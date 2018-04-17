<?php namespace App\Http\Controllers;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use App\Http\Utils\BooleanCellFormatter;
use App\Http\Utils\EpochCellFormatter;
use App\Http\Utils\PagingConstants;
use App\Services\Model\ISummitPushNotificationService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitNotificationRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use utils\Filter;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
/**
 * Class OAuth2SummitNotificationsApiController
 * @package App\Http\Controllers
 */
class OAuth2SummitNotificationsApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var ISummitPushNotificationService
     */
    private $push_notification_service;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitNotificationRepository $notification_repository,
        IMemberRepository $member_repository,
        ISummitPushNotificationService $push_notification_service,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);

        $this->repository                = $notification_repository;
        $this->push_notification_service = $push_notification_service;
        $this->member_repository         = $member_repository;
        $this->summit_repository         = $summit_repository;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAll($summit_id)
    {
        try
        {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $values = Input::all();

            $rules = [
                'page'     => 'integer|min:1',
                'per_page' => sprintf('required_with:page|integer|min:%s|max:%s', PagingConstants::MinPageSize, PagingConstants::MaxPageSize),
            ];

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }

            // default values
            $page     = 1;
            $per_page = PagingConstants::DefaultPageSize;

            if (Input::has('page')) {
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;
            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [
                    'channel'   => ['=='],
                    'sent_date' => ['>', '<', '<=', '>=', '=='],
                    'created'   => ['>', '<', '<=', '>=', '=='],
                    'is_sent'   => ['=='],
                    'approved'  => ['=='],
                    'event_id'  => ['=='],
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            $filter->validate([
                'channel'   => 'sometimes|in:EVERYONE,SPEAKERS,ATTENDEES,MEMBERS,SUMMIT,EVENT,GROUP',
                'sent_date' => 'sometimes|date_format:U',
                'created'   => 'sometimes|date_format:U',
                'is_sent'   => 'sometimes|boolean',
                'approved'  => 'sometimes|boolean',
                'event_id'  => 'sometimes|integer',
            ]);

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'sent_date',
                    'created',
                    'id',
                ]);
            }

            $result = $this->repository->getAllByPageBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray(Request::input('expand', ''),[],[],['summit_id' => $summit_id])
            );
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAllCSV($summit_id)
    {
        try
        {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();


            // default values
            $page     = 1;
            $per_page = PHP_INT_MAX;


            $filter = null;
            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [
                    'channel'   => ['=='],
                    'sent_date' => ['>', '<', '<=', '>=', '=='],
                    'created'   => ['>', '<', '<=', '>=', '=='],
                    'is_sent'   => ['=='],
                    'approved'  => ['=='],
                    'event_id'  => ['=='],
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            $filter->validate([
                'channel'   => 'sometimes|in:EVERYONE,SPEAKERS,ATTENDEES,MEMBERS,SUMMIT,EVENT,GROUP',
                'sent_date' => 'sometimes|date_format:U',
                'created'   => 'sometimes|date_format:U',
                'is_sent'   => 'sometimes|boolean',
                'approved'  => 'sometimes|boolean',
                'event_id'  => 'sometimes|integer',
            ]);

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'sent_date',
                    'created',
                    'id',
                ]);
            }

            $data     = $this->repository->getAllByPageBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);
            $filename = "push-notification-" . date('Ymd');
            $list     =  $data->toArray();
            return $this->export
            (
                'csv',
                $filename,
                $list['data'],
                [
                    'created'     => new EpochCellFormatter,
                    'last_edited' => new EpochCellFormatter,
                    'sent_date'   => new EpochCellFormatter,
                    'is_sent'     => new BooleanCellFormatter,
                    'approved'    => new BooleanCellFormatter,
                ]
            );
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $notification_id
     * @return mixed
     */
    public function getById($summit_id, $notification_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $notification = $summit->getNotificationById($notification_id);
            if(is_null($notification))
                return $this->error404();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($notification)->serialize( Request::input('expand', '')));
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function addPushNotification($summit_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitPushNotificationValidationRulesFactory::build($data->all());
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $current_member = null;
            if(!is_null($this->resource_server_context->getCurrentUserExternalId())){
                $current_member = $this->member_repository->getById($this->resource_server_context->getCurrentUserExternalId());
            }

            $notification = $this->push_notification_service->addPushNotification($summit, $current_member, $data->all());

            return $this->created(SerializerRegistry::getInstance()->getSerializer($notification)->serialize());
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}
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

use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitNotificationRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use models\summit\ISummitRepository;
use models\summit\SummitPushNotificationChannel;
use utils\FilterParser;
use utils\FilterParserException;
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
     * OAuth2SummitNotificationsApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitNotificationRepository $notification_repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitNotificationRepository $notification_repository,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->repository         = $notification_repository;
        $this->summit_repository = $summit_repository;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAll($summit_id)
    {
        try
        {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $values = Input::all();

            $rules = array
            (
                'page'     => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:5|max:100',
            );

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }

            // default values
            $page     = 1;
            $per_page = 5;

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
                    'event_id'  => ['=='],
                ]);
                $channels = $filter->getFlatFilter("channel");
                // validate that channel filter, if present if for a public one
                if(!is_null($channels) && is_array($channels)){
                    foreach ($channels as $element){
                       if(!SummitPushNotificationChannel::isPublicChannel($element->getValue()))
                            throw new ValidationException(sprintf("%s channel is not public!", $element->getValue()));
                    }
                }
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'sent_date',
                    'created',
                    'id',
                ));
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
        catch(FilterParserException $ex3){
            Log::warning($ex3);
            return $this->error412($ex3->getMessages());
        }
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}
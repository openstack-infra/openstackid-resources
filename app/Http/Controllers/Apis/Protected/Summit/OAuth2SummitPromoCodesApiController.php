<?php namespace App\Http\Controllers;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Models\Foundation\Summit\PromoCodes\PromoCodesConstants;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRegistrationPromoCodeRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitPromoCodeService;
use utils\Filter;
use utils\FilterElement;
use utils\FilterParser;
use utils\OrderParser;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use utils\PagingInfo;
/**
 * Class OAuth2SummitPromoCodesApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitPromoCodesApiController extends OAuth2ProtectedController
{

    /**
     * @var ISummitRegistrationPromoCodeRepository
     */
    private $promo_code_repository;

    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var ISummitPromoCodeService
     */
    private $promo_code_service;

    /**
     * OAuth2SummitPromoCodesApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitRegistrationPromoCodeRepository $promo_code_repository
     * @param IMemberRepository $member_repository
     * @param ISummitPromoCodeService $promo_code_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitRegistrationPromoCodeRepository $promo_code_repository,
        IMemberRepository $member_repository,
        ISummitPromoCodeService $promo_code_service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->promo_code_service    = $promo_code_service;
        $this->promo_code_repository = $promo_code_repository;
        $this->summit_repository     = $summit_repository;
        $this->member_repository     = $member_repository;
    }

    /**
     * @param $filter_element
     * @return bool
     */
    private function validateClassName($filter_element){
        if($filter_element instanceof FilterElement){
            return in_array($filter_element->getValue(), PromoCodesConstants::$valid_class_names);
        }
        $valid = true;
        foreach($filter_element[0] as $elem){
            $valid = $valid && in_array($elem->getValue(), PromoCodesConstants::$valid_class_names);
        }
        return $valid;
    }

    /**
     * @param $filter_element
     * @return bool
     */
    private function validateTypes($filter_element){
        if($filter_element instanceof FilterElement){
            return in_array($filter_element->getValue(), PromoCodesConstants::getValidTypes());
        }
        $valid = true;
        foreach($filter_element[0] as $elem){
            $valid = $valid && in_array($elem->getValue(), PromoCodesConstants::getValidTypes());
        }
        return $valid;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAllBySummit($summit_id){
        $values = Input::all();
        $rules  = [

            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        ];

        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

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

                    'code'          => ['=@', '=='],
                    'creator'       => ['=@', '=='],
                    'creator_email' => ['=@', '=='],
                    'owner'         => ['=@', '=='],
                    'owner_email'   => ['=@', '=='],
                    'speaker'       => ['=@', '=='],
                    'speaker_email' => ['=@', '=='],
                    'sponsor'       => ['=@', '=='],
                    'class_name'    => ['=='],
                    'type'          => ['=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'code',
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            if($filter->hasFilter("class_name") && !$this->validateClassName($filter->getFilter("class_name"))){
                throw new ValidationException(
                    sprintf
                    (
                        "class_name filter has an invalid value ( valid values are %s",
                        implode(", ", PromoCodesConstants::$valid_class_names)
                    )
                );
            }

            if($filter->hasFilter("type") && !$this->validateTypes($filter->getFilter("type"))){
                throw new ValidationException(
                    sprintf
                    (
                        "type filter has an invalid value ( valid values are %s",
                        implode(", ", PromoCodesConstants::getValidTypes())
                    )
                );
            }

            $data      = $this->promo_code_repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $data->toArray
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    [ 'serializer_type' => SerializerRegistry::SerializerType_Private ]
                )
            );
        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array( $ex1->getMessage()));
        }
        catch (EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        }
        catch(\HTTP401UnauthorizedException $ex3)
        {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return \Illuminate\Http\Response|mixed
     */
    public function getAllBySummitCSV($summit_id){
        $values = Input::all();
        $rules  = [
    ];

        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }

            // default values
            $page     = 1;
            $per_page = PHP_INT_MAX;
            $filter   = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [

                    'code'          => ['=@', '=='],
                    'creator'       => ['=@', '=='],
                    'creator_email' => ['=@', '=='],
                    'owner'         => ['=@', '=='],
                    'owner_email'   => ['=@', '=='],
                    'speaker'       => ['=@', '=='],
                    'speaker_email' => ['=@', '=='],
                    'sponsor'       => ['=@', '=='],
                    'class_name'    => ['=='],
                    'type'          => ['=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'code',
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            if($filter->hasFilter("class_name") && !$this->validateClassName($filter->getFilter("class_name"))){
                throw new ValidationException(
                    sprintf
                    (
                        "class_name filter has an invalid value ( valid values are %s",
                        implode(", ", PromoCodesConstants::$valid_class_names)
                    )
                );
            }

            if($filter->hasFilter("type") && !$this->validateTypes($filter->getFilter("type"))){
                throw new ValidationException(
                    sprintf
                    (
                        "type filter has an invalid value ( valid values are %s",
                        implode(", ", PromoCodesConstants::getValidTypes())
                    )
                );
            }

            $data     = $this->promo_code_repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);
            $filename = "promocodes-" . date('Ymd');
            $list     =  $data->toArray();
            return $this->export
            (
                'csv',
                $filename,
                $list['data'],
                [
                    'created'     => new EpochCellFormatter,
                    'last_edited' => new EpochCellFormatter,
                    'redeemed'    => new BooleanCellFormatter,
                    'email_sent'  => new BooleanCellFormatter,
                ]
            );

        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array( $ex1->getMessage()));
        }
        catch (EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        }
        catch(\HTTP401UnauthorizedException $ex3)
        {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getMetadata($summit_id){
        $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
        if (is_null($summit)) return $this->error404();

        return $this->ok
        (
            $this->promo_code_repository->getMetadata($summit)
        );
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function addPromoCodeBySummit($summit_id){
        try {
            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = PromoCodesValidationRulesFactory::build($data->all());
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

            $promo_code     = $this->promo_code_service->addPromoCode($summit, $data->all(), $current_member);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($promo_code)->serialize());
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $promo_code_id
     * @return mixed
     */
    public function updatePromoCodeBySummit($summit_id, $promo_code_id)
    {
        try {
            if (!Request::isJson()) return $this->error403();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = PromoCodesValidationRulesFactory::build($data->all());
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
            if (!is_null($this->resource_server_context->getCurrentUserExternalId())) {
                $current_member = $this->member_repository->getById($this->resource_server_context->getCurrentUserExternalId());
            }

            $promo_code = $this->promo_code_service->updatePromoCode($summit, $promo_code_id, $data->all(), $current_member);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($promo_code)->serialize());
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
     * @param $promo_code_id
     * @return mixed
     */
    public function deletePromoCodeBySummit($summit_id, $promo_code_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->promo_code_service->deletePromoCode($summit, $promo_code_id);

            return $this->deleted();
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
     * @param $promo_code_id
     * @return mixed
     */
    public function sendPromoCodeMail($summit_id, $promo_code_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $mail_request = $this->promo_code_service->sendPromoCodeMail($summit, $promo_code_id);
            return $this->created($mail_request->getId());
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
     * @param $promo_code_id
     * @return mixed
     */
    public function getPromoCodeBySummit($summit_id, $promo_code_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $promo_code = $summit->getPromoCodeById($promo_code_id);
            if(is_null($promo_code))
                return $this->error404();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($promo_code)->serialize( Request::input('expand', '')));
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

}
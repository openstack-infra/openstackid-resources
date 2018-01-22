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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRegistrationPromoCodeRepository;
use models\summit\ISummitRepository;
use models\summit\MemberSummitRegistrationPromoCode;
use models\summit\SpeakerSummitRegistrationPromoCode;
use models\summit\SponsorSummitRegistrationPromoCode;
use ModelSerializers\SerializerRegistry;
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

    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitRegistrationPromoCodeRepository $promo_code_repository,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->promo_code_repository = $promo_code_repository;
        $this->summit_repository     = $summit_repository;
    }

    private static $valid_class_names = [
        SpeakerSummitRegistrationPromoCode::ClassName,
        SponsorSummitRegistrationPromoCode::ClassName,
        MemberSummitRegistrationPromoCode::ClassName,
    ];

    /**
     * @param $filter_element
     * @return bool
     */
    private function validateClassName($filter_element){
        if($filter_element instanceof FilterElement){
            return in_array($filter_element->getValue(), self::$valid_class_names);
        }
        $valid = true;
        foreach($filter_element[0] as $elem){
            $valid = $valid && in_array($elem->getValue(), self::$valid_class_names);
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
                        implode(", ", self::$valid_class_names)
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
}
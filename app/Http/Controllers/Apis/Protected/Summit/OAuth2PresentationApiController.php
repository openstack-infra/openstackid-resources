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

use libs\utils\HTMLCleaner;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request as LaravelRequest;
use Exception;
use models\utils\IEntity;
use ModelSerializers\SerializerRegistry;
use services\model\IPresentationService;

/**
 * Class OAuth2PresentationApiController
 * @package App\Http\Controllers
 */
final class OAuth2PresentationApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var IPresentationService
     */
    private $presentation_service;

    /**
     * @var ISummitEventRepository
     */
    private $presentation_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * OAuth2PresentationApiController constructor.
     * @param IPresentationService $presentation_service
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $presentation_repository
     * @param IMemberRepository $member_repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        IPresentationService $presentation_service,
        ISummitRepository $summit_repository,
        ISummitEventRepository $presentation_repository,
        IMemberRepository $member_repository,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->presentation_repository = $presentation_repository;
        $this->presentation_service    = $presentation_service;
        $this->member_repository       = $member_repository;
        $this->summit_repository       = $summit_repository;
    }

    //presentations

    //videos

    public function getPresentationVideos($summit_id, $presentation_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation)) return $this->error404();

            $videos = $presentation->getVideos();

            $items = [];
            foreach($videos as $i)
            {
                if($i instanceof IEntity)
                {
                    $i = SerializerRegistry::getInstance()->getSerializer($i)->serialize();
                }
                $items[] = $i;
            }

            return $this->ok($items);

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @param $video_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getPresentationVideo($summit_id, $presentation_id, $video_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation)) return $this->error404();

            $video = $presentation-getVideoBy($video_id);

            if (is_null($video)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($video)->serialize());

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $presentation_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addVideo(LaravelRequest $request, $summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error400();

            $data = Input::json();

            $rules =
            [
                'you_tube_id'     => 'required|alpha_dash',
                'name'            => 'sometimes|required|text:512',
                'description'     => 'sometimes|required|string',
                'featured'        => 'sometimes|required|boolean',
                'display_on_site' => 'sometimes|required|boolean',
            ];

            $data = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
               $ex = new ValidationException;
               $ex->setMessages($validation->messages()->toArray());
               throw $ex;
            }

            $fields = [
                'name',
                'description',
            ];

            $video = $this->presentation_service->addVideoTo($presentation_id, HTMLCleaner::cleanData($data, $fields));

            return $this->created($video->getId());
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $presentation_id
     * @param $video_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function updateVideo(LaravelRequest $request, $summit_id, $presentation_id, $video_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error400();

            $data = Input::json();

            $rules = [
                'you_tube_id'     => 'required|alpha_dash',
                'name'            => 'sometimes|required|text:512',
                'description'     => 'sometimes|required|string',
                'display_on_site' => 'sometimes|required|boolean',
                'featured'        => 'sometimes|required|boolean',
                'order'           => 'sometimes|integer|min:1',
            ];

            $data = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'name',
                'description',
            ];

            $this->presentation_service->updateVideo($presentation_id, $video_id, HTMLCleaner::cleanData($data, $fields));

            return $this->updated();
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @param $video_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function deleteVideo($summit_id, $presentation_id, $video_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->presentation_service->deleteVideo($presentation_id, $video_id);

            return $this->deleted();
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function submitPresentation($summit_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error400();

            $member_id = $this->resource_server_context->getCurrentUserExternalId();
            if(is_null($member_id))
                return $this->error403();

            $member = $this->member_repository->getById($member_id);

            if(is_null($member))
                return $this->error403();

            $data = Input::json();

            $rules =
            [
                'title'                     => 'required|string|max:100',
                'description'               => 'required|string',
                'social_description'        => 'required|string|max:100',
                'level'                     => 'required|in:Beginner,Intermediate,Advanced,N/A',
                'attendees_expected_learnt' => 'required|string|max:1000',
                'type_id'                   => 'required|integer',
                'track_id'                  => 'required|integer',
                'attending_media'           => 'required|boolean',
                'links'                     => 'sometimes|url_array',
                'extra_questions'           => 'sometimes|entity_value_array',
                'tags'                      => 'sometimes|string_array',
            ];

            $data = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'title',
                'description',
                'social_summary',
                'attendees_expected_learnt',
            ];

            $presentation = $this->presentation_service->submitPresentation($summit, $member, HTMLCleaner::cleanData($data, $fields));

            return $this->created(SerializerRegistry::getInstance()->getSerializer($presentation)->serialize());
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404(['message' => $ex1->getMessage()]);
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @return mixed
     */
    public function updatePresentationSubmission($summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error400();

            $member_id = $this->resource_server_context->getCurrentUserExternalId();
            if(is_null($member_id))
                return $this->error403();

            $member = $this->member_repository->getById($member_id);

            if(is_null($member))
                return $this->error403();

            $data = Input::json();

            $rules =
                [
                    'title'                     => 'sometimes|string|max:100',
                    'description'               => 'sometimes|string',
                    'social_description'        => 'sometimes|string|max:100',
                    'level'                     => 'sometimes|in:Beginner,Intermediate,Advanced,N/A',
                    'attendees_expected_learnt' => 'sometimes|string|max:1000',
                    'type_id'                   => 'sometimes|integer',
                    'track_id'                  => 'sometimes|integer',
                    'attending_media'           => 'sometimes|boolean',
                    'links'                     => 'sometimes|url_array',
                    'extra_questions'           => 'sometimes|entity_value_array',
                    'tags'                      => 'sometimes|string_array',
                ];

            $data = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'title',
                'description',
                'social_summary',
                'attendees_expected_learnt',
            ];

            $presentation = $this->presentation_service->updatePresentationSubmission(
                $summit,
                $presentation_id,
                $member,
                HTMLCleaner::cleanData($data, $fields)
            );

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($presentation)->serialize());
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @return mixed
     */
    public function completePresentationSubmission($summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $member_id = $this->resource_server_context->getCurrentUserExternalId();
            if(is_null($member_id))
                return $this->error403();

            $member = $this->member_repository->getById($member_id);

            if(is_null($member))
                return $this->error403();

            $presentation = $this->presentation_service->completePresentationSubmission
            (
                $summit,
                $presentation_id,
                $member
            );

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($presentation)->serialize());
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @return mixed
     */
    public function deletePresentation($summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $member = $this->member_repository->getById($current_member_id);

            if(is_null($member))
                return $this->error403();

            $this->presentation_service->deletePresentation($summit, $member, $presentation_id);

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

    // Slides

    /**
     * @param $summit_id
     * @param $presentation_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getPresentationSlides($summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation)) return $this->error404();

            $slides = $presentation-getSlides();

            $items = [];
            foreach($slides as $i)
            {
                if($i instanceof IEntity)
                {
                    $i = SerializerRegistry::getInstance()->getSerializer($i)->serialize();
                }
                $items[] = $i;
            }

            return $this->ok($items);

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
     * @param $presentation_id
     * @param $slide_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getPresentationSlide($summit_id, $presentation_id, $slide_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation)) return $this->error404();

            $slide = $presentation-getSlideBy($slide_id);

            if (is_null($slide)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($slide)->serialize());

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $presentation_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addPresentationSlide(LaravelRequest $request, $summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $data  = $request->all();
            $rules = [
                'file'            => 'required_without:link',
                'link'            => 'required_without:file|url',
                'name'            => 'required|text:512',
                'description'     => 'sometimes|required|string',
                'display_on_site' => 'sometimes|required|boolean',
                'featured'        => 'sometimes|required|boolean',
            ];

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'name',
                'description',
            ];

            $slide = $this->presentation_service->addSlideTo($request, $presentation_id, HTMLCleaner::cleanData($data, $fields));

            return $this->created($slide->getId());
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $presentation_id
     * @param $slide_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function updatePresentationSlide(LaravelRequest $request, $summit_id, $presentation_id, $slide_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $data  = $request->all();

            $rules = [
                'link'            => 'sometimes|required|url',
                'name'            => 'sometimes|required|text:512',
                'description'     => 'sometimes|required|string',
                'display_on_site' => 'sometimes|required|boolean',
                'featured'        => 'sometimes|required|boolean',
                'order'           => 'sometimes|integer|min:1',
            ];

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'name',
                'description',
            ];

            $this->presentation_service->updateSlide($request, $presentation_id, $slide_id, HTMLCleaner::cleanData($data, $fields));

            return $this->updated();
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @param $slide_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function deletePresentationSlide($summit_id, $presentation_id, $slide_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->presentation_service->deleteSlide($presentation_id, $slide_id);

            return $this->deleted();
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    // Links

    /**
     * @param $summit_id
     * @param $presentation_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getPresentationLinks($summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation)) return $this->error404();

            $links = $presentation-getLinks();

            $items = [];
            foreach($links as $i)
            {
                if($i instanceof IEntity)
                {
                    $i = SerializerRegistry::getInstance()->getSerializer($i)->serialize();
                }
                $items[] = $i;
            }

            return $this->ok($items);

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
     * @param $presentation_id
     * @param $link_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getPresentationLink($summit_id, $presentation_id, $link_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation)) return $this->error404();

            $link = $presentation-getLinkBy($link_id);

            if (is_null($link)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($link)->serialize());

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $presentation_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addPresentationLink(LaravelRequest $request, $summit_id, $presentation_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $data  = $request->all();
            $rules = [
                'link'            => 'required|url',
                'name'            => 'required|text:512',
                'description'     => 'sometimes|required|string',
                'display_on_site' => 'sometimes|required|boolean',
                'featured'        => 'sometimes|required|boolean',
            ];

            $data = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'name',
                'description',
            ];

            $link = $this->presentation_service->addLinkTo($request, $presentation_id, HTMLCleaner::cleanData($data, $fields));

            return $this->created($link->getId());
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $presentation_id
     * @param $link_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function updatePresentationLink(LaravelRequest $request, $summit_id, $presentation_id, $link_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $data  = $request->all();

            $rules = [
                'link'            => 'sometimes|required|url',
                'name'            => 'sometimes|required|text:512',
                'description'     => 'sometimes|required|string',
                'display_on_site' => 'sometimes|required|boolean',
                'featured'        => 'sometimes|required|boolean',
                'order'           => 'sometimes|integer|min:1',
            ];

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException;
                $ex->setMessages($validation->messages()->toArray());
                throw $ex;
            }

            $fields = [
                'name',
                'description',
            ];

            $this->presentation_service->updateLink($request, $presentation_id, $link_id, HTMLCleaner::cleanData($data, $fields));

            return $this->updated();
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $presentation_id
     * @param $link_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function deletePresentationLink($summit_id, $presentation_id, $link_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->presentation_service->deleteLink($presentation_id, $link_id);

            return $this->deleted();
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
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}
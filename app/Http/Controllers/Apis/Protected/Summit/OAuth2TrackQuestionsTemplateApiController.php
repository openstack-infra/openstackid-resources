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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackMultiValueQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplateConstants;
use App\Models\Foundation\Summit\Repositories\ITrackQuestionTemplateRepository;
use App\Services\Model\ITrackQuestionTemplateService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use Exception;
use models\oauth2\IResourceServerContext;
use ModelSerializers\SerializerRegistry;
use Illuminate\Support\Facades\Validator;
use utils\Filter;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;

/**
 * Class OAuth2TrackQuestionsTemplateApiController
 * @package App\Http\Controllers
 */
final class OAuth2TrackQuestionsTemplateApiController extends OAuth2ProtectedController
{
    /**
     * @var ITrackQuestionTemplateService
     */
    private $track_question_template_service;

    /**
     * @var ITrackQuestionTemplateRepository
     */
    private $track_question_template_repository;

    /**
     * OAuth2TrackQuestionsTemplateApiController constructor.
     * @param ITrackQuestionTemplateService $track_question_template_service
     * @param ITrackQuestionTemplateRepository $track_question_template_repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ITrackQuestionTemplateService $track_question_template_service,
        ITrackQuestionTemplateRepository $track_question_template_repository,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->track_question_template_repository = $track_question_template_repository;
        $this->track_question_template_service = $track_question_template_service;
    }

    /**
     * @return mixed
     */
    public function getTrackQuestionTemplates(){
        $values = Input::all();
        $rules  = [

            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        ];

        try {

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
                    'name'  => ['=@', '=='],
                    'label' => ['=@', '=='],
                    'class_name' => ['=='],
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            $filter->validate([
                'class_name'      => sprintf('sometimes|in:%s',implode(',', TrackQuestionTemplateConstants::$valid_class_names)),
                'name'            => 'sometimes|string',
                'label'           => 'sometimes|string',
            ], [
                'class_name.in' =>  sprintf
                (
                    ":attribute has an invalid value ( valid values are %s )",
                    implode(", ", TrackQuestionTemplateConstants::$valid_class_names)
                ),
            ]);

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'name',
                    'label',
                ]);
            }

            $data = $this->track_question_template_repository->getAllByPage(new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $data->toArray
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    []
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
     * @return mixed
     */
    public function addTrackQuestionTemplate(){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $rules = TrackQuestionTemplateValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $question = $this->track_question_template_service->addTrackQuestionTemplate($payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($question)->serialize(
                Request::input('expand', '')
            ));
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }


    /**
     * @param $track_question_template_id
     * @return mixed
     */
    public function getTrackQuestionTemplate($track_question_template_id){
        try {

            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);
            if (is_null($track_question_template)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($track_question_template)->serialize());
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $track_question_template_id
     * @return mixed
     */
    public function updateTrackQuestionTemplate($track_question_template_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $rules = TrackQuestionTemplateValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $question = $this->track_question_template_service->updateTrackQuestionTemplate($track_question_template_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($question)->serialize(
                Request::input('expand', '')
            ));
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $track_question_template_id
     * @return mixed
     */
    public function deleteTrackQuestionTemplate($track_question_template_id){
        try {

            $this->track_question_template_service->deleteTrackQuestionTemplate($track_question_template_id);
            return $this->deleted();
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getTrackQuestionTemplateMetadata(){
        return $this->ok
        (
            $this->track_question_template_repository->getQuestionsMetadata()
        );
    }

    /**
     * values endpoints
     */

    /**
     * @param $track_question_template_id
     * @param $track_question_template_value_id
     * @return mixed
     */
    public function getTrackQuestionTemplateValue($track_question_template_id, $track_question_template_value_id){
        try {

            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);
            if (is_null($track_question_template)) return $this->error404();

            if (!$track_question_template instanceof TrackMultiValueQuestionTemplate) return $this->error404();

            $value = $track_question_template->getValueById($track_question_template_value_id);
            if (is_null($value)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($value)->serialize());
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
     * @param $track_question_template_id
     * @return mixed
     */
    public function addTrackQuestionTemplateValue($track_question_template_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $rules = TrackQuestionValueTemplateValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $value = $this->track_question_template_service->addTrackQuestionValueTemplate
            (
                $track_question_template_id,
                $payload
            );

            return $this->created(SerializerRegistry::getInstance()->getSerializer($value)->serialize(
                Request::input('expand', '')
            ));
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
     * @param $track_question_template_id
     * @param $track_question_template_value_id
     * @return mixed
     */
    public function updateTrackQuestionTemplateValue($track_question_template_id, $track_question_template_value_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $rules = TrackQuestionValueTemplateValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $value = $this->track_question_template_service->updateTrackQuestionValueTemplate
            (
                $track_question_template_id,
                $track_question_template_value_id,
                $payload
            );

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($value)->serialize(
                Request::input('expand', '')
            ));
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
     * @param $track_question_template_id
     * @param $track_question_template_value_id
     * @return mixed
     */
    public function deleteTrackQuestionTemplateValue($track_question_template_id, $track_question_template_value_id){
        try {
            $this->track_question_template_service->deleteTrackQuestionValueTemplate
            (
                $track_question_template_id,
                $track_question_template_value_id
            );
            return $this->deleted();
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
}
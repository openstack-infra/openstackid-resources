<?php namespace App\Services\Model;
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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionValueTemplate;
use App\Models\Foundation\Summit\Factories\TrackQuestionTemplateFactory;
use App\Models\Foundation\Summit\Factories\TrackQuestionValueTemplateFactory;
use App\Models\Foundation\Summit\Repositories\ISummitTrackRepository;
use App\Models\Foundation\Summit\Repositories\ITrackQuestionTemplateRepository;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
/**
 * Class TrackQuestionTemplateService
 * @package App\Services\Model
 */
final class TrackQuestionTemplateService
    extends AbstractService
    implements ITrackQuestionTemplateService
{
    /**
     * @var ITrackQuestionTemplateRepository
     */
    private $track_question_template_repository;

    /**
     * @var ISummitTrackRepository
     */
    private $track_repository;

    /**
     * TrackQuestionTemplateService constructor.
     * @param ITrackQuestionTemplateRepository $track_question_template_repository
     * @param ISummitTrackRepository $track_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ITrackQuestionTemplateRepository $track_question_template_repository,
        ISummitTrackRepository $track_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->track_repository = $track_repository;
        $this->track_question_template_repository = $track_question_template_repository;
    }

    /**
     * @param array $payload
     * @return TrackQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackQuestionTemplate(array $payload)
    {
       return $this->tx_service->transaction(function() use($payload){
           $former_track_question_template = $this->track_question_template_repository->getByName($payload["name"]);
           if(!is_null($former_track_question_template)){
               throw new ValidationException(
                   trans(
                       "validation_errors.TrackQuestionTemplateService.addTrackQuestionTemplate.TrackQuestionTemplateLabelAlreadyExist"
                   )
               );
           }

           $former_track_question_template = $this->track_question_template_repository->getByLabel($payload["label"]);
           if(!is_null($former_track_question_template)){
               throw new ValidationException(
                   trans(
                       "validation_errors.TrackQuestionTemplateService.addTrackQuestionTemplate.TrackQuestionTemplateNameAlreadyExist"
                   )
               );
           }

           $track_question_template = TrackQuestionTemplateFactory::build($payload);

           if(isset($payload['tracks'])){
               foreach($payload['tracks'] as $track_id){
                   $track = $this->track_repository->getById($track_id);
                   if(is_null($track))
                       throw new EntityNotFoundException(
                           trans(
                               "not_found_errors.TrackQuestionTemplateService.addTrackQuestionTemplate.TrackNotFound"
                               ,['track_id' => $track_id])
                       );
                   $track_question_template->addTrack($track);
               }
           }

           $this->track_question_template_repository->add($track_question_template);

           return $track_question_template;
       });
    }

    /**
     * @param int $track_question_template_id
     * @param array $payload
     * @return TrackQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackQuestionTemplate($track_question_template_id, array $payload)
    {
        return $this->tx_service->transaction(function() use($payload, $track_question_template_id){
            $former_track_question_template = $this->track_question_template_repository->getByName($payload["name"]);
            if(!is_null($former_track_question_template) && $former_track_question_template->getId() != $track_question_template_id){
                throw new ValidationException(
                    trans(
                        "validation_errors.TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateLabelAlreadyExist"
                    )
                );
            }

            $former_track_question_template = $this->track_question_template_repository->getByLabel($payload["label"]);
            if(!is_null($former_track_question_template) && $former_track_question_template->getId() != $track_question_template_id){
                throw new ValidationException(
                    trans(
                        "validation_errors.TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateNameAlreadyExist"
                    )
                );
            }

            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateNotFound',
                        [
                            'track_question_template_id' => $track_question_template_id
                        ]
                    )
                );
            $class_name = $payload['class_name'];

            $reflect = new \ReflectionClass($track_question_template);
            if ($reflect->getShortName() !== $class_name) {
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateNotFound',
                        [
                            'track_question_template_id' => $track_question_template_id
                        ]
                    )
                );
            }

            if(isset($payload['tracks'])){
                $track_question_template->clearTracks();
                foreach($payload['tracks'] as $track_id){
                    $track = $this->track_repository->getById($track_id);
                    if(is_null($track))
                        throw new EntityNotFoundException(
                            trans(
                                "not_found_errors.TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackNotFound"
                                ,['track_id' => $track_id])
                        );
                    $track_question_template->addTrack($track);
                }
            }

            return TrackQuestionTemplateFactory::populate($track_question_template, $payload);
        });
    }

    /**
     * @param int $track_question_template_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function deleteTrackQuestionTemplate($track_question_template_id){
        return $this->tx_service->transaction(function() use($track_question_template_id){
            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.TrackQuestionTemplateService.deleteTrackQuestionTemplate.TrackQuestionTemplateNotFound',
                        [
                            'track_question_template_id' => $track_question_template_id
                        ]
                    )
                );

            $this->track_question_template_repository->delete($track_question_template);
        });
    }

    /**
     * @param int $track_question_template_id
     * @param array $data
     * @return TrackQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackQuestionValueTemplate($track_question_template_id, $data){
        return $this->tx_service->transaction(function() use($track_question_template_id, $data){
            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException();

            if(!$track_question_template instanceof TrackMultiValueQuestionTemplate){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.TrackQuestionTemplateService.addTrackQuestionValueTemplate.TrackQuestionTemplateNotFound',
                        [
                            'track_question_template_id' => $track_question_template_id,
                        ]
                    )
                );
            }

            $former_value = $track_question_template->getValueByValue($data['value']);
            if(!is_null($former_value)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.TrackQuestionTemplateService.addTrackQuestionValueTemplate.ValueAlreadyExist',
                        [
                            'track_question_template_id' => $track_question_template_id,
                            'value'       => $data['value']
                        ]
                    )
                );
            }

            $former_value = $track_question_template->getValueByLabel($data['label']);
            if(!is_null($former_value)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.TrackQuestionTemplateService.addTrackQuestionValueTemplate.LabelAlreadyExist',
                        [
                            'track_question_template_id' => $track_question_template_id,
                            'label'       => $data['label']
                        ]
                    )
                );
            }

            $value = TrackQuestionValueTemplateFactory::build($data);

            $track_question_template->addValue($value);

            return $value;
        });
    }

    /**
     * @param int $track_question_template_id
     * @param int $track_question_value_template_id
     * @param array $data
     * @return TrackQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackQuestionValueTemplate($track_question_template_id, $track_question_value_template_id, $data){
        return $this->tx_service->transaction(function() use($track_question_template_id, $track_question_value_template_id, $data){
            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException();

            if(!$track_question_template instanceof TrackMultiValueQuestionTemplate){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.TrackQuestionTemplateService.updateTrackQuestionValueTemplate.TrackQuestionTemplateNotFound',
                        [
                            'track_question_template_id' => $track_question_template_id,
                        ]
                    )
                );
            }

            if(isset($data['value'])) {
                $former_value = $track_question_template->getValueByValue($data['value']);
                if (!is_null($former_value) && $former_value->getId() != $track_question_value_template_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.TrackQuestionTemplateService.updateTrackQuestionValueTemplate.ValueAlreadyExist',
                            [
                                'track_question_template_id' => $track_question_template_id,
                                'value' => $data['value']
                            ]
                        )
                    );
                }
            }

            if(isset($data['label'])) {
                $former_value = $track_question_template->getValueByLabel($data['label']);
                if (!is_null($former_value) && $former_value->getId() != $track_question_value_template_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.TrackQuestionTemplateService.updateTrackQuestionValueTemplate.LabelAlreadyExist',
                            [
                                'track_question_template_id' => $track_question_template_id,
                                'label' => $data['label']
                            ]
                        )
                    );
                }
            }

            $value = $track_question_template->getValueById($track_question_value_template_id);
            if(is_null($value))
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.TrackQuestionTemplateService.updateTrackQuestionValueTemplate.TrackQuestionTemplateValueNotFound',
                        [
                            'track_question_value_template_id' => $track_question_value_template_id,
                        ]
                    )
                );

            TrackQuestionValueTemplateFactory::populate($value, $data);

            if (isset($data['order']) && intval($data['order']) != $value->getOrder()) {
                // request to update order
                $track_question_template->recalculateValueOrder($value, intval($data['order']));
            }

            return $value;

        });
    }

    /**
     * @param int $track_question_template_id
     * @param int $track_question_value_template_id
     * @param array $data
     * @return TrackQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrackQuestionValueTemplate($track_question_template_id, $track_question_value_template_id){
        return $this->tx_service->transaction(function() use($track_question_template_id, $track_question_value_template_id){
            $track_question_template = $this->track_question_template_repository->getById($track_question_template_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException();

            if(!$track_question_template instanceof TrackMultiValueQuestionTemplate){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.TrackQuestionTemplateService.deleteTrackQuestionValueTemplate.TrackQuestionTemplateNotFound',
                        [
                            'track_question_template_id' => $track_question_template_id,
                        ]
                    )
                );
            }

            $value = $track_question_template->getValueById($track_question_value_template_id);
            if(is_null($value))
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.TrackQuestionTemplateService.deleteTrackQuestionValueTemplate.TrackQuestionTemplateValueNotFound',
                        [
                            'track_question_value_template_id' => $track_question_value_template_id,
                        ]
                    )
                );

            $track_question_template->removeValue($value);

        });
    }
}
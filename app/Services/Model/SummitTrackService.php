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
use App\Events\TrackDeleted;
use App\Events\TrackInserted;
use App\Events\TrackUpdated;
use App\Models\Foundation\Summit\Factories\PresentationCategoryFactory;
use App\Models\Foundation\Summit\Repositories\ISummitTrackRepository;
use App\Models\Foundation\Summit\Repositories\ITrackQuestionTemplateRepository;
use Illuminate\Support\Facades\Event;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ITagRepository;
use models\summit\PresentationCategory;
use models\summit\Summit;
/**
 * Class SummitTrackService
 * @package App\Services\Model
 */
final class SummitTrackService
    extends AbstractService
    implements ISummitTrackService
{
    /**
     * @var ISummitTrackRepository
     */
    private $track_repository;

    /**
     * @var ITagRepository
     */
    private $tag_repository;

    /**
     * @var ITrackQuestionTemplateRepository
     */
    private $track_question_template_repository;

    /**
     * SummitTrackService constructor.
     * @param ISummitTrackRepository $track_repository
     * @param ITagRepository $tag_repository
     * @param ITrackQuestionTemplateRepository $track_question_template_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitTrackRepository $track_repository,
        ITagRepository $tag_repository,
        ITrackQuestionTemplateRepository $track_question_template_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->tag_repository = $tag_repository;
        $this->track_repository = $track_repository;
        $this->track_question_template_repository = $track_question_template_repository;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return PresentationCategory
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrack(Summit $summit, array $data)
    {
        $track =  $this->tx_service->transaction(function () use ($summit, $data) {

            if (!empty($data['code'])) {
                $former_track = $summit->getPresentationCategoryByCode(trim($data['code']));
                if (!is_null($former_track))
                    throw new ValidationException(sprintf("track id %s already has code %s assigned on summit id %s", $former_track->getId(), $data['code'], $summit->getId()));
            }

            $former_track = $summit->getPresentationCategoryByTitle($data['name']);
            if (!is_null($former_track))
                throw new ValidationException(sprintf("track id %s already has title %s assigned on summit id %s", $former_track->getId(), $data['name'], $summit->getId()));

            $track = PresentationCategoryFactory::build($summit, $data);

            if(isset($data['allowed_tags'])){
                foreach($data['allowed_tags'] as $tag_value) {
                    $tackTagGroupAllowedTag = $summit->getAllowedTagOnTagTrackGroup($tag_value);
                    if(is_null($tackTagGroupAllowedTag)){
                        throw new ValidationException(
                            sprintf("allowed_tags : tag value %s is not allowed on current track tag groups for summit %s", $tag_value, $summit->getId())
                        );
                    }
                    $track->addAllowedTag($tackTagGroupAllowedTag->getTag());
                }
            }

            $summit->addPresentationCategory($track);

            return $track;
        });

        Event::fire(new TrackInserted($track->getSummitId(), $track->getId()));

        return $track;
    }

    /**
     * @param Summit $summit
     * @param int $track_id
     * @param array $data
     * @return PresentationCategory
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrack(Summit $summit, $track_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_id, $data) {

            $track = $summit->getPresentationCategory($track_id);

            if (is_null($track))
                throw new EntityNotFoundException
                (
                    sprintf("track id %s does not belong to summit id %s", $track_id, $summit->getId())
                );

            if (isset($data['code']) && !empty($data['code'])) {
                $former_track = $summit->getPresentationCategoryByCode($data['code']);
                if (!is_null($former_track) && $former_track->getId() != $track_id)
                    throw new ValidationException(sprintf("track id %s already has code %s assigned on summit id %s", $former_track->getId(), $data['code'], $summit->getId()));
            }

            if (isset($data['name'])) {
                $former_track = $summit->getPresentationCategoryByTitle($data['name']);
                if (!is_null($former_track) && $former_track->getId() != $track_id)
                    throw new ValidationException(sprintf("track id %s already has title %s assigned on summit id %s", $former_track->getId(), $data['name'], $summit->getId()));
            }

            $track = PresentationCategoryFactory::populate($track, $data);

            if(isset($data['allowed_tags'])){
                $track->clearAllowedTags();
                foreach($data['allowed_tags'] as $tag_value) {
                    $tackTagGroupAllowedTag = $summit->getAllowedTagOnTagTrackGroup($tag_value);
                    if(is_null($tackTagGroupAllowedTag)){
                        throw new ValidationException(
                            sprintf("allowed_tags : tag value %s is not allowed on current track tag groups for summit %s", $tag_value, $summit->getId())
                        );
                    }
                    $track->addAllowedTag($tackTagGroupAllowedTag->getTag());
                }
            }

            Event::fire(new TrackUpdated($track->getSummitId(), $track->getId()));

            return $track;

        });
    }

    /**
     * @param Summit $summit
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrack(Summit $summit, $track_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_id) {

            $track = $summit->getPresentationCategory($track_id);

            if (is_null($track))
                throw new EntityNotFoundException
                (
                    sprintf("track id %s does not belong to summit id %s", $track_id, $summit->getId())
                );

            $summit_events = $track->getRelatedPublishedSummitEventsIds();

            if(count($summit_events) > 0){
                throw new ValidationException
                (
                    sprintf("track id %s could not be deleted bc its assigned to published events on summit id %s", $track_id, $summit->getId())
                );
            }

            Event::fire(new TrackDeleted($track->getSummitId(), $track->getId()));

            $this->track_repository->delete($track);
        });
    }

    /**
     * @param Summit $from_summit
     * @param Summit $to_summit
     * @return PresentationCategory[]
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function copyTracks(Summit $from_summit, Summit $to_summit)
    {
        $added_tracks = $this->tx_service->transaction(function () use ($from_summit, $to_summit) {

            if($from_summit->getId() == $to_summit->getId()){
                throw new ValidationException
                (
                  trans
                  (
                      'validation_errors.SummitTrackService.copyTracks.SameSummit'
                  )
                );
            }

            $added_tracks = [];
            foreach($from_summit->getPresentationCategories() as $track_2_copy){
                $former_track = $to_summit->getPresentationCategoryByTitle($track_2_copy->getTitle());
                if(!is_null($former_track)) continue;

                $former_track = $to_summit->getPresentationCategoryByCode($track_2_copy->getCode());
                if(!is_null($former_track)) continue;

                $data      = [
                    'title'                     => $track_2_copy->getTitle(),
                    'code'                      => $track_2_copy->getCode(),
                    'description'               => $track_2_copy->getDescription(),
                    'session_count'             => $track_2_copy->getSessionCount(),
                    'alternate_count'           => $track_2_copy->getAlternateCount(),
                    'lightning_count'           => $track_2_copy->getLightningCount(),
                    'lightning_alternate_count' => $track_2_copy->getLightningAlternateCount(),
                    'voting_visible'            => $track_2_copy->isVotingVisible(),
                    'chair_visible'             => $track_2_copy->isChairVisible(),
                ];

                $new_track = PresentationCategoryFactory::build($to_summit, $data);

                $to_summit->addPresentationCategory($new_track);
                $added_tracks[] = $new_track;
            }

            return $added_tracks;
        });

        foreach ($added_tracks as $track){
            Event::fire(new TrackInserted($track->getSummitId(), $track->getId()));
        }

        return $added_tracks;
    }

    /**
     * @param int $track_id
     * @param int $question_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackExtraQuestion($track_id, $question_id)
    {
        return $this->tx_service->transaction(function() use($track_id, $question_id){
            $track = $this->track_repository->getById($track_id);
            if(is_null($track))
                throw new EntityNotFoundException(
                    trans
                    (
                        'not_found_errors.SummitTrackService.addTrackExtraQuestion.TrackNotFound',
                        ['track_id' => $track_id ]
                    )
                );

            $track_question_template = $this->track_question_template_repository->getById($question_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException(
                    trans
                    (
                        'not_found_errors.SummitTrackService.addTrackExtraQuestion.QuestionNotFound',
                        ['question_id' => $question_id ]
                    )
                );

            $track->addExtraQuestion($track_question_template);
        });
    }

    /**
     * @param int $track_id
     * @param int $question_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function removeTrackExtraQuestion($track_id, $question_id)
    {
        return $this->tx_service->transaction(function() use($track_id, $question_id){
            $track = $this->track_repository->getById($track_id);
            if(is_null($track))
                throw new EntityNotFoundException(
                    trans
                    (
                        'not_found_errors.SummitTrackService.removeTrackExtraQuestion.TrackNotFound',
                        ['track_id' => $track_id ]
                    )
                );

            $track_question_template = $this->track_question_template_repository->getById($question_id);

            if(is_null($track_question_template))
                throw new EntityNotFoundException(
                    trans
                    (
                        'not_found_errors.SummitTrackService.removeTrackExtraQuestion.QuestionNotFound',
                        ['question_id' => $question_id ]
                    )
                );

            $track->removeExtraQuestion($track_question_template);
        });
    }
}
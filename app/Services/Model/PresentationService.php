<?php namespace services\model;

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
use App\Events\PresentationMaterialDeleted;
use App\Events\PresentationMaterialUpdated;
use App\Models\Foundation\Summit\SelectionPlan;
use App\Services\Model\AbstractService;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackAnswer;
use Illuminate\Support\Facades\Event;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ITagRepository;
use models\main\Member;
use models\summit\factories\IPresentationVideoFactory;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\Presentation;
use models\summit\PresentationLink;
use models\summit\PresentationSpeaker;
use models\summit\PresentationType;
use models\summit\PresentationVideo;
use libs\utils\ITransactionService;
use models\summit\Summit;

/**
 * Class PresentationService
 * @package services\model
 */
final class PresentationService
    extends AbstractService
    implements IPresentationService
{
    /**
     * @var ISummitEventRepository
     */
    private $presentation_repository;

    /**
     * @var IPresentationVideoFactory
     */
    private $video_factory;

    /**
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var ITagRepository
     */
    private $tag_repository;


    /**
     * PresentationService constructor.
     * @param IPresentationVideoFactory $video_factory
     * @param ISummitEventRepository $presentation_repository
     * @param ISpeakerRepository $speaker_repository
     * @param ITagRepository $tag_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IPresentationVideoFactory $video_factory,
        ISummitEventRepository $presentation_repository,
        ISpeakerRepository $speaker_repository,
        ITagRepository $tag_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->presentation_repository = $presentation_repository;
        $this->speaker_repository = $speaker_repository;
        $this->tag_repository = $tag_repository;
        $this->video_factory = $video_factory;
    }

    /**
     * @param int $presentation_id
     * @param array $video_data
     * @return PresentationVideo
     */
    public function addVideoTo($presentation_id, array $video_data)
    {
        $video = $this->tx_service->transaction(function () use ($presentation_id, $video_data) {

            $presentation = $this->presentation_repository->getById($presentation_id);


            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            if ($presentation->hasVideos())
                throw new ValidationException(sprintf('presentation %s already has a video!', $presentation_id));

            if (!isset($video_data['name'])) $video_data['name'] = $presentation->getTitle();

            $video = $this->video_factory->build($video_data);

            $presentation->addVideo($video);

            return $video;
        });

        return $video;
    }

    /**
     * @param int $presentation_id
     * @param int $video_id
     * @param array $video_data
     * @return PresentationVideo
     */
    public function updateVideo($presentation_id, $video_id, array $video_data)
    {
        $video = $this->tx_service->transaction(function () use ($presentation_id, $video_id, $video_data) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            $video = $presentation->getVideoBy($video_id);

            if (is_null($video))
                throw new EntityNotFoundException('video not found!');

            if (!$video instanceof PresentationVideo)
                throw new EntityNotFoundException('video not found!');

            if (isset($video_data['name']))
                $video->setName(trim($video_data['name']));

            if (isset($video_data['you_tube_id']))
                $video->setYoutubeId(trim($video_data['you_tube_id']));

            if (isset($video_data['description']))
                $video->setDescription(trim($video_data['description']));

            if (isset($video_data['display_on_site']))
                $video->setDisplayOnSite((bool)$video_data['display_on_site']);

            return $video;

        });
        Event::fire(new PresentationMaterialUpdated($video));
        return $video;
    }

    /**
     * @param int $presentation_id
     * @param int $video_id
     * @return void
     */
    public function deleteVideo($presentation_id, $video_id)
    {
        $this->tx_service->transaction(function () use ($presentation_id, $video_id) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            $video = $presentation->getVideoBy($video_id);

            if (is_null($video))
                throw new EntityNotFoundException('video not found!');

            if (!$video instanceof PresentationVideo)
                throw new EntityNotFoundException('video not found!');

            $presentation->removeVideo($video);

            Event::fire(new PresentationMaterialDeleted($presentation, $video_id, 'PresentationVideo'));
        });

    }

    /**
     * @param Summit $summit
     * @return int
     */
    public function getSubmissionLimitFor(Summit $summit)
    {
        $res = -1;
        if ($summit->isSubmissionOpen()) {
            $res = intval($summit->getCurrentSelectionPlanByStatus(SelectionPlan::STATUS_SUBMISSION)->getMaxSubmissionAllowedPerUser());
        }

        // zero means infinity
        return $res === 0 ? PHP_INT_MAX : $res;
    }

    /**
     * @param Summit $summit
     * @param Member $member
     * @param array $data
     * @return Presentation
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function submitPresentation(Summit $summit, Member $member, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $member, $data) {

            $current_selection_plan = $summit->getCurrentSelectionPlanByStatus(SelectionPlan::STATUS_SUBMISSION);
            $current_speaker = $this->speaker_repository->getByMember($member);

            if (is_null($current_speaker))
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.submitPresentation.NotValidSpeaker'
                ));

            if (is_null($current_selection_plan))
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.submitPresentation.NotValidSelectionPlan'
                ));

            // check qty

            $limit = $this->getSubmissionLimitFor($summit);
            $count = count($current_speaker->getPresentationsBySelectionPlanAndRole($current_selection_plan, PresentationSpeaker::ROLE_CREATOR)) +
                count($current_speaker->getPresentationsBySelectionPlanAndRole($current_selection_plan, PresentationSpeaker::ROLE_MODERATOR)) +
                count($current_speaker->getPresentationsBySelectionPlanAndRole($current_selection_plan, PresentationSpeaker::ROLE_SPEAKER));

            if ($count >= $limit)
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.submitPresentation.limitReached',
                    ['limit' => $limit]));

            $presentation = new Presentation();
            $presentation->setCreator($member);
            $presentation->setSelectionPlan($current_selection_plan);

            $summit->addEvent($presentation);

            $presentation->setProgress(Presentation::PHASE_SUMMARY);

            $presentation = $this->saveOrUpdatePresentation
            (
                $summit,
                $current_selection_plan,
                $presentation,
                $current_speaker,
                $data
            );


            return $presentation;
        });

    }

    /**
     * @param Summit $summit
     * @param int $presentation_id
     * @param Member $member
     * @param array $data
     * @return Presentation
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updatePresentationSubmission(Summit $summit, $presentation_id, Member $member, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $presentation_id, $member, $data) {

            $current_selection_plan = $summit->getCurrentSelectionPlanByStatus(SelectionPlan::STATUS_SUBMISSION);
            $current_speaker = $this->speaker_repository->getByMember($member);

            if (is_null($current_speaker))
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.updatePresentationSubmission.NotValidSpeaker'
                ));

            if (is_null($current_selection_plan))
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.updatePresentationSubmission.NotValidSelectionPlan'
                ));

            $presentation = $summit->getEvent($presentation_id);


            if (is_null($presentation))
                throw new EntityNotFoundException(trans(
                    'not_found_errors.PresentationService.updatePresentationSubmission.PresentationNotFound',
                    ['presentation_id' => $presentation_id]
                ));

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException(trans(
                    'not_found_errors.PresentationService.updatePresentationSubmission.PresentationNotFound',
                    ['presentation_id' => $presentation_id]
                ));

            if (!$presentation->canEdit($current_speaker))
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.updatePresentationSubmission.CurrentSpeakerCanNotEditPresentation',
                    ['presentation_id' => $presentation_id]
                ));

            return $this->saveOrUpdatePresentation
            (
                $summit,
                $current_selection_plan,
                $presentation,
                $current_speaker,
                $data
            );
        });
    }

    /**
     * @param Summit $summit
     * @param SelectionPlan $selection_plan
     * @param Presentation $presentation
     * @param PresentationSpeaker $current_speaker
     * @param array $data
     * @return Presentation
     * @throws \Exception
     */
    private function saveOrUpdatePresentation(Summit $summit,
                                              SelectionPlan $selection_plan,
                                              Presentation $presentation,
                                              PresentationSpeaker $current_speaker,
                                              array $data
    )
    {
        return $this->tx_service->transaction(function () use ($summit, $selection_plan, $presentation, $current_speaker, $data) {
            $event_type = $summit->getEventType(intval($data['type_id']));
            if (is_null($event_type)) {
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.PresentationService.saveOrUpdatePresentation.eventTypeNotFound',
                        ['type_id' => $data['type_id']]
                    )
                );
            }

            if (!$event_type instanceof PresentationType) {
                throw new ValidationException(trans(
                        'validation_errors.PresentationService.saveOrUpdatePresentation.invalidPresentationType',
                        ['type_id' => $event_type->getIdentifier()])
                );
            }

            if (!$event_type->isShouldBeAvailableOnCfp()) {
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.saveOrUpdatePresentation.notAvailableCFP',
                    ['type_id' => $event_type->getIdentifier()]));
            }

            $track = $summit->getPresentationCategory(intval($data['track_id']));
            if (is_null($track)) {
                throw new EntityNotFoundException(
                    trans(
                        'not_found_errors.PresentationService.saveOrUpdatePresentation.trackNotFound',
                        ['track_id' => $data['track_id']]
                    )
                );
            }

            if (!$selection_plan->hasTrack($track)) {
                throw new ValidationException(trans(
                    'validation_errors.PresentationService.saveOrUpdatePresentation.trackDontBelongToSelectionPlan',
                    [
                        'selection_plan_id' => $selection_plan->getIdentifier(),
                        'track_id' => $track->getIdentifier(),
                    ]));
            }

            if (isset($data['title']))
                $presentation->setTitle(html_entity_decode(trim($data['title'])));

            if (isset($data['description']))
                $presentation->setAbstract(html_entity_decode(trim($data['description'])));

            if (isset($data['social_description']))
                $presentation->setSocialSummary(strip_tags(trim($data['social_description'])));

            if (isset($data['level']))
                $presentation->setLevel($data['level']);

            if (isset($data['attendees_expected_learnt']))
                $presentation->setAttendeesExpectedLearnt(html_entity_decode($data['attendees_expected_learnt']));

            $presentation->setAttendingMedia(isset($data['attending_media']) ?
                filter_var($data['attending_media'], FILTER_VALIDATE_BOOLEAN) : 0);

            $presentation->setType($event_type);
            $presentation->setCategory($track);
            // add me as speaker
            //$presentation->addSpeaker($current_speaker);

            if (isset($data['tags'])) {
                $presentation->clearTags();

                if (count($data['tags']) > 0) {
                    if ($presentation->getProgress() == Presentation::PHASE_SUMMARY)
                        $presentation->setProgress(Presentation::PHASE_TAGS);
                }

                foreach ($data['tags'] as $tag_value) {
                    $tag = $track->getAllowedTagByVal($tag_value);
                    if (is_null($tag)) {
                        throw new ValidationException(
                            trans(
                                'validation_errors.PresentationService.saveOrUpdatePresentation.TagNotAllowed',
                                [
                                    'tag' => $tag_value,
                                    'track_id' => $track->getId()
                                ]
                            )
                        );
                    }
                    $presentation->addTag($tag);
                }
            }

            if (isset($data['links'])) {
                $presentation->clearLinks();

                if (count($data['links']) > Presentation::MaxAllowedLinks) {
                    throw new ValidationException(trans(
                        'validation_errors.PresentationService.saveOrUpdatePresentation.MaxAllowedLinks',
                        [
                            'max_allowed_links' => Presentation::MaxAllowedLinks
                        ]));
                }

                foreach ($data['links'] as $link) {
                    $presentationLink = new PresentationLink();
                    $presentationLink->setName(trim($link));
                    $presentationLink->setLink(trim($link));
                    $presentation->addLink($presentationLink);
                }
            }

            // extra questions values
            if (isset($data['extra_questions'])) {
                foreach ($data['extra_questions'] as $extra_question) {
                    if (!isset($extra_question['id'])) continue;
                    if (!isset($extra_question['value'])) continue;

                    $extra_question_id = $extra_question['id'];
                    $extra_question_value = $extra_question['value'];

                    $track_question = $track->getExtraQuestionById($extra_question_id);
                    if (is_null($track_question)) {
                        throw new EntityNotFoundException(
                            trans(
                                'not_found_errors.PresentationService.saveOrUpdatePresentation.trackQuestionNotFound',
                                ['question_id' => $extra_question_id]
                            )
                        );
                    }

                    $answer = $presentation->getTrackExtraQuestionAnswer($track_question);

                    if (is_null($answer)) {
                        $answer = new TrackAnswer();
                        $presentation->addAnswer($answer);
                        $track_question->addAnswer($answer);
                    }

                    if (is_array($extra_question_value)) {
                        $extra_question_value = str_replace('{comma}', ',', $extra_question_value);
                        $extra_question_value = implode(',', $extra_question_value);
                    }

                    $answer->setValue($extra_question_value);
                }
            }
            return $presentation;
        });
    }

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $presentation_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deletePresentation(Summit $summit, Member $member, $presentation_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $member, $presentation_id) {

            $current_speaker = $this->speaker_repository->getByMember($member);
            if (is_null($current_speaker))
                throw new EntityNotFoundException(sprintf("member %s does not has a speaker profile", $member->getId()));

            $presentation = $summit->getEvent($presentation_id);
            if (is_null($presentation))
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if (!$presentation->canEdit($current_speaker))
                throw new ValidationException(sprintf("member %s can not edit presentation %s",
                    $member->getId(),
                    $presentation_id
                ));

            $summit->removeEvent($presentation);

        });
    }

    /**
     * @param Summit $summit
     * @param int $presentation_id
     * @param Member $member
     * @return Presentation
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function completePresentationSubmission(Summit $summit, $presentation_id, Member $member)
    {
        return $this->tx_service->transaction(function () use ($summit, $member, $presentation_id) {

            $current_speaker = $this->speaker_repository->getByMember($member);
            if (is_null($current_speaker))
                throw new EntityNotFoundException(sprintf("member %s does not has a speaker profile", $member->getId()));

            $presentation = $summit->getEvent($presentation_id);
            if (is_null($presentation))
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if (!$presentation->canEdit($current_speaker))
                throw new ValidationException(sprintf("member %s can not edit presentation %s",
                    $member->getId(),
                    $presentation_id
                ));

            if ($presentation->getProgress() != Presentation::PHASE_SPEAKERS) {
                throw new ValidationException
                (
                    sprintf("presentation %s is not allowed to mark as completed", $presentation_id)
                );
            }

            if (!$presentation->fulfilSpeakersConditions()) {
                throw new ValidationException
                (
                    sprintf("presentation %s is not allowed to mark as completed because does not fulfil speakers conditions", $presentation_id)
                );
            }

            $title = $presentation->getTitle();
            $abtract = $presentation->getAbstract();
            $level = $presentation->getLevel();

            if (empty($title)) {
                throw new ValidationException('Title is Mandatory!');
            }

            if (empty($abtract)) {
                throw new ValidationException('Abstract is mandatory!');
            }

            if (empty($level)) {
                throw new ValidationException('Level is mandatory!');
            }

            $presentation->setProgress(Presentation::PHASE_COMPLETE);
            $presentation->setStatus(Presentation::STATUS_RECEIVED);
            return $presentation;
        });
    }
}
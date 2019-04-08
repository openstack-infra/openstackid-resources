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
use App\Http\Utils\IFileUploader;
use App\Models\Foundation\Summit\Factories\PresentationLinkFactory;
use App\Models\Foundation\Summit\Factories\PresentationSlideFactory;
use App\Models\Foundation\Summit\Factories\PresentationVideoFactory;
use App\Models\Foundation\Summit\SelectionPlan;
use App\Services\Model\AbstractService;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackAnswer;
use Illuminate\Support\Facades\Event;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ITagRepository;
use models\main\Member;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\Presentation;
use models\summit\PresentationLink;
use models\summit\PresentationSlide;
use models\summit\PresentationSpeaker;
use models\summit\PresentationType;
use models\summit\PresentationVideo;
use libs\utils\ITransactionService;
use models\summit\Summit;
use Illuminate\Http\Request as LaravelRequest;
use App\Services\Model\IFolderService;
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
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var ITagRepository
     */
    private $tag_repository;

    /**
     * @var IFolderService
     */
    private $folder_service;

    /**
     * @var IFileUploader
     */
    private $file_uploader;

    /**
     * PresentationService constructor.
     * @param ISummitEventRepository $presentation_repository
     * @param ISpeakerRepository $speaker_repository
     * @param ITagRepository $tag_repository
     * @param IFolderService $folder_service
     * @param IFileUploader $file_uploader
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitEventRepository $presentation_repository,
        ISpeakerRepository $speaker_repository,
        ITagRepository $tag_repository,
        IFolderService $folder_service,
        IFileUploader $file_uploader,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->presentation_repository = $presentation_repository;
        $this->speaker_repository = $speaker_repository;
        $this->tag_repository = $tag_repository;
        $this->folder_service = $folder_service;
        $this->file_uploader = $file_uploader;
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

            $video = PresentationVideoFactory::build($video_data);

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

            PresentationVideoFactory::populate($video, $video_data);

            if (isset($data['order']) && intval($video_data['order']) != $video->getOrder()) {
                // request to update order
                $presentation->recalculateMaterialOrder($video, intval($video_data['order']));
            }

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

    /**
     * @param LaravelRequest $request
     * @param int $presentation_id
     * @param array $slide_data
     * @param array $allowed_extensions
     * @param int $max_file_size
     * @return mixed|PresentationSlide
     * @throws \Exception
     */
    public function addSlideTo
    (
        LaravelRequest $request,
        $presentation_id,
        array $slide_data,
        array $allowed_extensions = ['ppt', 'pptx', 'xps',  'key', 'pdf'],
        $max_file_size = 10485760
    )
    {
        $slide = $this->tx_service->transaction(function () use (
            $request,
            $presentation_id,
            $slide_data,
            $max_file_size,
            $allowed_extensions
        ) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');
            $slide = PresentationSlideFactory::build($slide_data);

            // check if there is any file sent
            if($request->hasFile('file')){
                $file = $request->file('file');
                if (!in_array($file->extension(), $allowed_extensions)) {
                    throw new ValidationException(
                        sprintf("file does not has a valid extension '(%s)'.", implode("','", $allowed_extensions)));
                }

                if ($file->getSize() > $max_file_size) {
                    throw new ValidationException(sprintf("file exceeds max_file_size (%s MB).", ($max_file_size / 1024) / 1024));
                }

                $slideFile = $this->file_uploader->build(
                    $file,
                    sprintf('summits/%s/presentations/%s/slides/', $presentation->getSummitId(), $presentation_id),
                    false);
                $slide->setSlide($slideFile);
            }

            $presentation->addSlide($slide);

            return $slide;
        });

        return $slide;
    }

    /**
     * @param LaravelRequest $request
     * @param int $presentation_id
     * @param int $slide_id
     * @param array $slide_data
     * @param array $allowed_extensions
     * @param int $max_file_size
     * @return mixed|PresentationSlide
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateSlide
    (
        LaravelRequest $request,
        $presentation_id,
        $slide_id,
        array $slide_data,
        array $allowed_extensions = ['ppt', 'pptx', 'xps',  'key', 'pdf'],
        $max_file_size = 10485760
    ){
        $slide = $this->tx_service->transaction(function () use
        (
            $request,
            $presentation_id,
            $slide_data,
            $max_file_size,
            $allowed_extensions,
            $slide_id
        ) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            $slide = $presentation->getSlideBy($slide_id);

            if (is_null($slide))
                throw new EntityNotFoundException('slide not found!');

            if (!$slide instanceof PresentationSlide)
                throw new EntityNotFoundException('slide not found!');

            PresentationSlideFactory::populate($slide, $slide_data);

            // check if there is any file sent
            if($request->hasFile('file')){
                $file = $request->file('file');
                if (!in_array($file->extension(), $allowed_extensions)) {
                    throw new ValidationException(
                        sprintf("file does not has a valid extension '(%s)'.", implode("','", $allowed_extensions)));
                }

                if ($file->getSize() > $max_file_size) {
                    throw new ValidationException(sprintf("file exceeds max_file_size (%s MB).", ($max_file_size / 1024) / 1024));
                }

                $slideFile = $this->file_uploader->build($file, sprintf('summits/%s/presentations/%s/slides/', $presentation->getSummitId(), $presentation_id), false);
                $slide->setSlide($slideFile);
            }

            if (isset($data['order']) && intval($slide_data['order']) != $slide->getOrder()) {
                // request to update order
                $presentation->recalculateMaterialOrder($slide, intval($slide_data['order']));
            }

            return $slide;

        });

        Event::fire(new PresentationMaterialUpdated($slide));
        return $slide;
    }

    /**
     * @param int $presentation_id
     * @param int $slide_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function deleteSlide($presentation_id, $slide_id)
    {
        $this->tx_service->transaction(function () use ($presentation_id, $slide_id) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            $slide = $presentation->getSlideBy($slide_id);

            if (is_null($slide))
                throw new EntityNotFoundException('slide not found!');

            if (!$slide instanceof PresentationSlide)
                throw new EntityNotFoundException('slide not found!');

            $presentation->removeSlide($slide);

            Event::fire(new PresentationMaterialDeleted($presentation, $slide_id, 'PresentationSlide'));
        });
    }

    /**
     * @param $presentation_id
     * @param array $link_data
     * @return PresentationLink
     */
    public function addLinkTo($presentation_id, array $link_data)
    {
        $link = $this->tx_service->transaction(function () use (
            $presentation_id,
            $link_data
        ) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');
            $link = PresentationLinkFactory::build($link_data);

            $presentation->addLink($link);

            return $link;
        });

        return $link;
    }

    /**
     * @param $presentation_id
     * @param $link_id
     * @param array $link_data
     * @return PresentationLink
     */
    public function updateLink($presentation_id, $link_id, array $link_data)
    {
        $link = $this->tx_service->transaction(function () use (
            $presentation_id,
            $link_id,
            $link_data
        ) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            $link = $presentation->getLinkBy($link_id);

            if (is_null($link))
                throw new EntityNotFoundException('link not found!');

            if (!$link instanceof PresentationLink)
                throw new EntityNotFoundException('link not found!');

            $link = PresentationLinkFactory::populate($link, $link_data);


            return $link;
        });

        Event::fire(new PresentationMaterialUpdated($link));

        return $link;
    }

    /**
     * @param int $presentation_id
     * @param int $link_id
     * @return void
     */
    public function deleteLink($presentation_id, $link_id)
    {
        $this->tx_service->transaction(function () use ($presentation_id, $link_id) {

            $presentation = $this->presentation_repository->getById($presentation_id);

            if (is_null($presentation))
                throw new EntityNotFoundException('presentation not found!');

            if (!$presentation instanceof Presentation)
                throw new EntityNotFoundException('presentation not found!');

            $link = $presentation->getLinkBy($link_id);

            if (is_null($link))
                throw new EntityNotFoundException('link not found!');

            if (!$link instanceof PresentationSlide)
                throw new EntityNotFoundException('link not found!');

            $presentation->removeLink($link);

            Event::fire(new PresentationMaterialDeleted($presentation, $link_id, 'PresentationLink'));
        });
    }
}
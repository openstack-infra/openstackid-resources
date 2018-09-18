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
use App\Models\Foundation\Summit\Factories\TrackTagGroupFactory;
use App\Models\Foundation\Summit\Repositories\IDefaultTrackTagGroupRepository;
use App\Models\Foundation\Summit\TrackTagGroup;
use App\Models\Foundation\Summit\TrackTagGroupAllowedTag;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ITagRepository;
use models\main\Tag;
use models\summit\Summit;
/**
 * Class SummitTrackTagGroupService
 * @package App\Services\Model
 */
final class SummitTrackTagGroupService extends AbstractService
implements ISummitTrackTagGroupService
{

    /**
     * @var ITagRepository
     */
    private $tag_repository;

    /**
     * @var IDefaultTrackTagGroupRepository
     */
    private $default_track_tag_group_repository;

    /**
     * SummitTrackTagGroupService constructor.
     * @param ITagRepository $tag_repository
     * @param IDefaultTrackTagGroupRepository $default_track_tag_group_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ITagRepository $tag_repository,
        IDefaultTrackTagGroupRepository $default_track_tag_group_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->tag_repository = $tag_repository;
        $this->default_track_tag_group_repository = $default_track_tag_group_repository;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return TrackTagGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @throws \Exception
     */
    public function addTrackTagGroup(Summit $summit, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $data) {

            if(isset($data['label'])){
                $former_group = $summit->getTrackTagGroupByLabel(trim($data['label']));
                if(!is_null($former_group)){
                    throw new ValidationException(trans
                    (
                        'validation_errors.SummitTrackTagGroupService.addTrackTagGroup.TrackTagGroupLabelAlreadyExists',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    ));
                }
            }

            if(isset($data['name'])){
                $former_group = $summit->getTrackTagGroupByName(trim($data['name']));
                if(!is_null($former_group)){
                    throw new ValidationException(trans
                    (
                        'validation_errors.SummitTrackTagGroupService.addTrackTagGroup.TrackTagGroupNameAlreadyExists',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    ));
                }
            }

            $track_tag_group = TrackTagGroupFactory::build($summit, $data);

            if (isset($data['allowed_tags'])) {
                $track_tag_group->clearAllowedTags();
                foreach ($data['allowed_tags'] as $str_tag) {
                    $tag = $this->tag_repository->getByTag($str_tag);
                    if($tag == null) $tag = new Tag($str_tag);
                    $track_tag_group->addTag($tag);
                }
            }

            $summit->addTrackTagGroup($track_tag_group);

            return $track_tag_group;
        });
    }

    /**
     * @param Summit $summit
     * @param int $track_tag_group_id
     * @param array $data
     * @return TrackTagGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackTagGroup(Summit $summit, $track_tag_group_id, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $track_tag_group_id, $data) {

            if(isset($data['label'])){
                $former_group = $summit->getTrackTagGroupByLabel(trim($data['label']));
                if(!is_null($former_group) && $former_group->getId() != $track_tag_group_id ){
                    throw new ValidationException(trans
                    (
                        'validation_errors.SummitTrackTagGroupService.updateTrackTagGroup.TrackTagGroupLabelAlreadyExists',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    ));
                }
            }

            if(isset($data['name'])){
                $former_group = $summit->getTrackTagGroupByName(trim($data['name']));
                if(!is_null($former_group) && $former_group->getId() != $track_tag_group_id ){
                    throw new ValidationException(trans
                    (
                        'validation_errors.SummitTrackTagGroupService.updateTrackTagGroup.TrackTagGroupNameAlreadyExists',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    ));
                }
            }

            $track_tag_group = $summit->getTrackTagGroup($track_tag_group_id);

            if(is_null($track_tag_group)){
                throw new EntityNotFoundException
                (
                    trans("not_found_errors.SummitTrackTagGroupService.updateTrackTagGroup.TrackTagGroupNotFound", [
                        'summit_id' => $summit->getId(),
                        'track_tag_group_id' => $track_tag_group_id,
                    ])
                );
            }

            if (isset($data['allowed_tags'])) {
                $track_tag_group->clearAllowedTags();
                foreach ($data['allowed_tags'] as $str_tag) {
                    $tag = $this->tag_repository->getByTag($str_tag);
                    if($tag == null) $tag = new Tag($str_tag);
                    $track_tag_group->addTag($tag);
                }
            }

            if (isset($data['order']) && intval($data['order']) != $track_tag_group->getOrder()) {
                // request to update order
                $summit->recalculateTrackTagGroupOrder($track_tag_group, intval($data['order']));
            }

            return $track_tag_group;
        });
    }

    /**
     * @param Summit $summit
     * @param int $track_tag_group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrackTagGroup(Summit $summit, $track_tag_group_id)
    {
        $this->tx_service->transaction(function() use($summit, $track_tag_group_id) {
            $track_tag_group = $summit->getTrackTagGroup($track_tag_group_id);

            if(is_null($track_tag_group)){
                throw new EntityNotFoundException
                (
                    trans("not_found_errors.SummitTrackTagGroupService.deleteTrackTagGroup.TrackTagGroupNotFound", [
                        'summit_id' => $summit->getId(),
                        'track_tag_group_id' => $track_tag_group_id,
                    ])
                );
            }

            $summit->removeTrackTagGroup($track_tag_group);
        });
    }

    /**
     * @param Summit $summit
     * @return TrackTagGroup[]
     * @throws \Exception
     */
    public function seedDefaultTrackTagGroups(Summit $summit)
    {
        return $this->tx_service->transaction(function() use($summit) {
            $added_track_tag_groups = [];
            $default_groups = $this->default_track_tag_group_repository->getAll();
            foreach($default_groups as $default_track_tag_group){
                // if already exists ...
                if($summit->getTrackTagGroupByLabel($default_track_tag_group->getLabel()))
                    continue;

                $new_group = new TrackTagGroup();
                $new_group->setName($default_track_tag_group->getName());
                $new_group->setLabel($default_track_tag_group->getLabel());
                $new_group->setOrder($default_track_tag_group->getOrder());
                $new_group->setIsMandatory($default_track_tag_group->isMandatory());
                $summit->addTrackTagGroup($new_group);
                $added_track_tag_groups[] = $new_group;
                foreach ($default_track_tag_group->getAllowedTags() as $default_allowed_tag){
                    $new_group->addTag($default_allowed_tag->getTag());
                }
            }
            return $added_track_tag_groups;
        });
    }

    /**
     * @param Summit $summit
     * @param int $tag_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function seedTagOnAllTrack(Summit $summit, $tag_id)
    {
        return $this->tx_service->transaction(function() use($summit, $tag_id) {

            $tag = $this->tag_repository->getById($tag_id);
            if(is_null($tag))
                throw new EntityNotFoundException(
                    trans(
                        "not_found_errors.SummitTrackTagGroupService.seedTagOnAllTrack.TagNotFound",
                        [
                           'tag_id' => $tag_id
                        ]
                    )
                );

            $tag_track_group = $summit->getTrackTagGroupForTag($tag);

            if(is_null($tag_track_group))
                throw new ValidationException(
                    trans(
                        'validation_errors.SummitTrackTagGroupService.seedTagOnAllTrack.TagDoesNotBelongToTrackTagGroup',
                        [
                            'tag_id' => $tag_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );

            foreach ($summit->getPresentationCategories() as $track){
                $track->addAllowedTag($tag);
            }
        });
    }

    /**
     * @param Summit $summit
     * @param int $track_tag_group_id
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function seedTagTrackGroupTagsOnTrack(Summit $summit, $track_tag_group_id, $track_id)
    {
        return $this->tx_service->transaction(function() use($summit, $track_tag_group_id, $track_id) {

            $track_tag_group = $summit->getTrackTagGroup($track_tag_group_id);

            if(is_null($track_tag_group)){
                throw new EntityNotFoundException
                (
                    trans("not_found_errors.SummitTrackTagGroupService.seedTagTrackGroupTagsOnTrack.TrackTagGroupNotFound", [
                        'summit_id' => $summit->getId(),
                        'track_tag_group_id' => $track_tag_group_id,
                    ])
                );
            }

            $track = $summit->getPresentationCategory($track_id);

            if(is_null($track)){
                throw new EntityNotFoundException
                (
                    trans("not_found_errors.SummitTrackTagGroupService.seedTagTrackGroupTagsOnTrack.TrackNotFound", [
                        'summit_id' => $summit->getId(),
                        'track_id' => $track_id,
                    ])
                );
            }

            foreach ($track_tag_group->getAllowedTags() as $allowedTag){
                $track->addAllowedTag($allowedTag->getTag());
            }

        });
    }
}
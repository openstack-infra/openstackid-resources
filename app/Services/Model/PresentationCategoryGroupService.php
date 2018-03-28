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
use App\Events\TrackGroupDeleted;
use App\Events\TrackGroupInserted;
use App\Events\TrackGroupUpdated;
use App\Models\Foundation\Summit\Factories\PresentationCategoryGroupFactory;
use Illuminate\Support\Facades\Event;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IGroupRepository;
use models\summit\PresentationCategoryGroup;
use models\summit\PrivatePresentationCategoryGroup;
use models\summit\Summit;
/**
 * Class PresentationCategoryGroupService
 * @package App\Services\Model
 */
final class PresentationCategoryGroupService
    extends AbstractService
    implements IPresentationCategoryGroupService
{
    /**
     * @var IGroupRepository
     */
    private $group_repository;

    /**
     * PresentationCategoryGroupService constructor.
     * @param IGroupRepository $group_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IGroupRepository $group_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->group_repository = $group_repository;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return PresentationCategoryGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackGroup(Summit $summit, array $data)
    {
        $track_group = $this->tx_service->transaction(function () use ($summit, $data) {

            $former_track_group = $summit->getCategoryGroupByName(trim($data['name']));
            if (!is_null($former_track_group)) {
                throw new ValidationException
                (
                    trans('validation_errors.PresentationCategoryGroupService.addTrackGroup.NameAlreadyExists'),
                    [
                        'name' => trim($data['name']),
                        'summit_id' => $summit->getId(),
                    ]
                );
            }

            $track_group = PresentationCategoryGroupFactory::build($summit, $data);

            $summit->addCategoryGroup($track_group);

            return $track_group;
        });

        Event::fire
        (
            new TrackGroupInserted
            (

                $track_group->getId(),
                $track_group->getSummitId(),
                $track_group->getClassName()
            )
        );

        return $track_group;
    }

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param array $data
     * @return PresentationCategoryGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackGroup(Summit $summit, $track_group_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_group_id, $data) {

            if (isset($data['name'])) {
                $former_track_group = $summit->getCategoryGroupByName(trim($data['name']));
                if (!is_null($former_track_group) && $former_track_group->getId() != $track_group_id) {
                    throw new ValidationException
                    (
                        trans('validation_errors.PresentationCategoryGroupService.updateTrackGroup.NameAlreadyExists'),
                        [
                            'name'      => trim($data['name']),
                            'summit_id' => $summit->getId(),
                        ]
                    );
                }
            }

            $track_group = $summit->getCategoryGroupById($track_group_id);

            if (is_null($track_group)) {
                throw new EntityNotFoundException
                (
                    trans('not_found_errors.PresentationCategoryGroupService.updateTrackGroup.TrackGroupNotFound'),
                    [
                        'track_group_id' => $track_group_id,
                        'summit_id'      => $summit->getId(),
                    ]
                );
            }

            Event::fire
            (
                new TrackGroupUpdated
                (

                    $track_group->getId(),
                    $track_group->getSummitId(),
                    $track_group->getClassName()
                )
            );

            return PresentationCategoryGroupFactory::populate($summit, $track_group, $data);

        });
    }

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrackGroup(Summit $summit, $track_group_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_group_id) {

            $track_group = $summit->getCategoryGroupById($track_group_id);

            if (is_null($track_group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.deleteTrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            Event::fire
            (
                new TrackGroupDeleted
                (
                    $track_group->getId(),
                    $track_group->getSummitId(),
                    $track_group->getClassName()
                )
            );

            $summit->removeCategoryGroup($track_group);
        });
    }

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function associateTrack2TrackGroup(Summit $summit, $track_group_id, $track_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_group_id, $track_id) {
            $track_group = $summit->getCategoryGroupById($track_group_id);

            if (is_null($track_group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.associateTrack2TrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $track = $summit->getPresentationCategory($track_id);

            if (is_null($track)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.associateTrack2TrackGroup.TrackNotFound',
                        [
                            'track_id' => $track_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $track_group->addCategory($track);

            Event::fire
            (
                new TrackGroupUpdated
                (
                    $track_group->getId(),
                    $track_group->getSummitId(),
                    $track_group->getClassName()
                )
            );
        });
    }

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function disassociateTrack2TrackGroup(Summit $summit, $track_group_id, $track_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_group_id, $track_id) {

            $track_group = $summit->getCategoryGroupById($track_group_id);

            if (is_null($track_group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.disassociateTrack2TrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $track = $summit->getPresentationCategory($track_id);

            if (is_null($track)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.disassociateTrack2TrackGroup.TrackNotFound',
                        [
                            'track_id' => $track_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $track_group->removeCategory($track);

            Event::fire
            (
                new TrackGroupUpdated
                (
                    $track_group->getId(),
                    $track_group->getSummitId(),
                    $track_group->getClassName()
                )
            );

        });
    }

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function associateAllowedGroup2TrackGroup(Summit $summit, $track_group_id, $group_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_group_id, $group_id) {

            $track_group = $summit->getCategoryGroupById($track_group_id);

            if (is_null($track_group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.associateAllowedGroup2TrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            if (!$track_group instanceof PrivatePresentationCategoryGroup) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.associateAllowedGroup2TrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $group = $this->group_repository->getById($group_id);

            if (is_null($group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.associateAllowedGroup2TrackGroup.GroupNotFound',
                        [
                            'group_id' => $group_id,
                        ]
                    )
                );
            }

            $track_group->addToGroup($group);

            Event::fire
            (
                new TrackGroupUpdated
                (
                    $track_group->getId(),
                    $track_group->getSummitId(),
                    $track_group->getClassName()
                )
            );
        });
    }

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function disassociateAllowedGroup2TrackGroup(Summit $summit, $track_group_id, $group_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $track_group_id, $group_id) {

            $track_group = $summit->getCategoryGroupById($track_group_id);

            if (is_null($track_group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.disassociateAllowedGroup2TrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            if (!$track_group instanceof PrivatePresentationCategoryGroup) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.disassociateAllowedGroup2TrackGroup.TrackGroupNotFound',
                        [
                            'track_group_id' => $track_group_id,
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $group = $this->group_repository->getById($group_id);

            if (is_null($group)) {
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.PresentationCategoryGroupService.disassociateAllowedGroup2TrackGroup.GroupNotFound',
                        [
                            'group_id' => $group_id,
                        ]
                    )
                );
            }

            $track_group->removeFromGroup($group);

            Event::fire
            (
                new TrackGroupUpdated
                (
                    $track_group->getId(),
                    $track_group->getSummitId(),
                    $track_group->getClassName()
                )
            );
        });
    }
}
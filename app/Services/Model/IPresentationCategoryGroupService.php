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
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\PresentationCategoryGroup;
use models\summit\Summit;
/**
 * Interface IPresentationCategoryGroupService
 * @package App\Services\Model
 */
interface IPresentationCategoryGroupService
{

    /**
     * @param Summit $summit
     * @param array $data
     * @return PresentationCategoryGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackGroup(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param array $data
     * @return PresentationCategoryGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackGroup(Summit $summit, $track_group_id, array $data);

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrackGroup(Summit $summit, $track_group_id);

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function associateTrack2TrackGroup(Summit $summit, $track_group_id, $track_id);

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $track_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function disassociateTrack2TrackGroup(Summit $summit, $track_group_id, $track_id);

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function associateAllowedGroup2TrackGroup(Summit $summit, $track_group_id, $group_id);

    /**
     * @param Summit $summit
     * @param int $track_group_id
     * @param int $group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function disassociateAllowedGroup2TrackGroup(Summit $summit, $track_group_id, $group_id);
}
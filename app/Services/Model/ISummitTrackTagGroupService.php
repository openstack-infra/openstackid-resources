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
use App\Models\Foundation\Summit\TrackTagGroup;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;

/**
 * Interface ISummitTrackTagGroupService
 * @package App\Services\Model
 */
interface ISummitTrackTagGroupService
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return TrackTagGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackTagGroup(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param int $track_tag_group_id
     * @param array $data
     * @return TrackTagGroup
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackTagGroup(Summit $summit, $track_tag_group_id, array $data);

    /**
     * @param Summit $summit
     * @param int $track_tag_group_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrackTagGroup(Summit $summit, $track_tag_group_id);


    /**
     * @param Summit $summit
     * @return void
     */
    public function seedDefaultTrackTagGroups(Summit $summit);
}
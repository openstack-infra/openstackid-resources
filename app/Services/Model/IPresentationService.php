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
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\Member;
use models\summit\Presentation;
use models\summit\PresentationVideo;
use models\summit\Summit;
/**
 * Interface IPresentationService
 * @package services\model
 */
interface IPresentationService
{
    /**
     * @param int $presentation_id
     * @param array $video_data
     * @return PresentationVideo
     */
    public function addVideoTo($presentation_id, array $video_data);

    /**
     * @param int $presentation_id
     * @param int $video_id
     * @param array $video_data
     * @return PresentationVideo
     */
    public function updateVideo($presentation_id, $video_id, array $video_data);


    /**
     * @param int $presentation_id
     * @param int $video_id
     * @return void
     */
    public function deleteVideo($presentation_id, $video_id);

    /**
     * @param Summit $summit
     * @param Member $member
     * @param array $data
     * @return Presentation
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function submitPresentation(Summit $summit, Member $member, array $data);

    /**
     * @param Summit $summit
     * @param int $presentation_id
     * @param Member $member
     * @param array $data
     * @return Presentation
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updatePresentationSubmission(Summit $summit, $presentation_id, Member $member, array $data);


    /**
     * @param Summit $summit
     * @param int $presentation_id
     * @param Member $member
     * @return Presentation
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function completePresentationSubmission(Summit $summit, $presentation_id, Member $member);

    /**
     * @param Member $member
     * @param int $presentation_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deletePresentation(Member $member, $presentation_id);
}
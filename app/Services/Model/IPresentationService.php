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
use models\summit\PresentationLink;
use models\summit\PresentationSlide;
use models\summit\PresentationVideo;
use models\summit\Summit;
use Illuminate\Http\Request as LaravelRequest;
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
     * @param Summit $summit
     * @param Member $member
     * @param int $presentation_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deletePresentation(Summit $summit, Member $member, $presentation_id);

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
    );

    /**
     * @param LaravelRequest $request
     * @param int $presentation_id
     * @param int $slide_id
     * @param array $slide_data
     * @param array $allowed_extensions
     * @param int $max_file_size
     * @return mixed|PresentationSlide
     * @throws \Exception
     */
    public function updateSlide
    (
        LaravelRequest $request,
        $presentation_id,
        $slide_id,
        array $slide_data,
        array $allowed_extensions = ['ppt', 'pptx', 'xps',  'key', 'pdf'],
        $max_file_size = 10485760
    );

    /**
     * @param int $presentation_id
     * @param int $slide_id
     * @return void
     */
    public function deleteSlide($presentation_id, $slide_id);

    /**
     * @param $presentation_id
     * @param array $link_data
     * @return PresentationLink
     */
    public function addLinkTo($presentation_id, array $link_data);

    /**
     * @param $presentation_id
     * @param $link_id
     * @param array $link_data
     * @return PresentationLink
     */
    public function updateLink($presentation_id, $link_id, array $link_data);

    /**
     * @param int $presentation_id
     * @param int $link_id
     * @return void
     */
    public function deleteLink($presentation_id, $link_id);
}
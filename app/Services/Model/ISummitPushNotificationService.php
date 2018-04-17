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
use models\main\Member;
use models\summit\Summit;
use models\summit\SummitPushNotification;
/**
 * Interface ISummitPushNotificationService
 * @package App\Services\Model
 */
interface ISummitPushNotificationService
{
    /**
     * @param Summit $summit
     * @param Member|null $current_member
     * @param array $data
     * @return SummitPushNotification
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function addPushNotification(Summit $summit, Member $current_member, array $data);

    /**
     * @param Summit $summit
     * @param Member|null $current_member
     * @param int $notification_id
     * @return SummitPushNotification
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function approveNotification(Summit $summit, Member $current_member, $notification_id);

    /**
     * @param Summit $summit
     * @param Member|null $current_member
     * @param int $notification_id
     * @return SummitPushNotification
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function unApproveNotification(Summit $summit, Member $current_member, $notification_id);

    /**
     * @param Summit $summit
     * @param int $notification_id
     * @return void
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function deleteNotification(Summit $summit, $notification_id);
}
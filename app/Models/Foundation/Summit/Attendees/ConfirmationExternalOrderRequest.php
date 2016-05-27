<?php namespace models\summit;
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

/**
 * Class ConfirmationExternalOrderRequest
 * @package models\summit
 */
final class ConfirmationExternalOrderRequest
{
    /**
     * @var Summit
     */
    private $summit;

    /**
     * @return Summit
     */
    public function getSummit()
    {
        return $this->summit;
    }

    /**
     * @return int
     */
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @return string
     */
    public function getExternalOrderId()
    {
        return $this->external_order_id;
    }

    /**
     * @return string
     */
    public function getExternalAttendeeId()
    {
        return $this->external_attendee_id;
    }

    /**
     * @var int
     */
    private $member_id;

    /**
     * @var string
     */
    private $external_order_id;

    /**
     * @var string
     */
    private $external_attendee_id;

    /**
     * ConfirmationExternalOrderRequest constructor.
     * @param Summit $summit
     * @param int $member_id
     * @param string $external_order_id
     * @param string $external_attendee_id
     */
    public function __construct(
        Summit $summit,
        $member_id,
        $external_order_id,
        $external_attendee_id
    )
    {
        $this->summit               = $summit;
        $this->member_id            = $member_id;
        $this->external_order_id    = $external_order_id;
        $this->external_attendee_id = $external_attendee_id;
    }
}
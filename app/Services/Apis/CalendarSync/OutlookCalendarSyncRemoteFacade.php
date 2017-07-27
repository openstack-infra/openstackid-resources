<?php namespace services\apis\CalendarSync;
/**
 * Copyright 2017 OpenStack Foundation
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

use Composer\Script\Event;
use models\summit\CalendarSync\CalendarSyncInfo;
use models\summit\CalendarSync\CalendarSyncInfoOAuth2;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\SummitEvent;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitVenueRoom;
use OutlookRestClient\Facade\OutlookRestClient;
use OutlookRestClient\Facade\Requests\AddressVO;
use OutlookRestClient\Facade\Requests\CalendarVO;
use OutlookRestClient\Facade\Requests\EventVO;
use OutlookRestClient\Facade\Requests\LocationVO;
use OutlookRestClient\Facade\Responses\CalendarResponse;
use OutlookRestClient\Facade\Responses\ErrorResponse;
use OutlookRestClient\IOutlookRestClient;
/**
 * Class OutlookCalendarSyncRemoteFacade
 * @package services\apis\CalendarSync
 */
final class OutlookCalendarSyncRemoteFacade
    extends AbstractCalendarSyncRemoteFacade
{

    /**
     * @var IOutlookRestClient
     */
    private $client;


    /**
     * OutlookCalendarSyncRemoteFacade constructor.
     * @param CalendarSyncInfoOAuth2 $sync_calendar_info
     */
    public function __construct(CalendarSyncInfoOAuth2 $sync_calendar_info)
    {
        parent::__construct($sync_calendar_info);

        $this->client = new OutlookRestClient();
        $this->client->setAccessToken($sync_calendar_info->getAccessToken());
        $this->client->setTokenCallback(function($access_token){

            $this->sync_calendar_info->setAccessToken($access_token);

            if(isset($access_token['refresh_token']))
                $this->sync_calendar_info->setRefreshToken($access_token['refresh_token']);
        });

    }


    /**
     * @param SummitEvent $summit_event
     * @return EventVO
     */
    private function buildEventVO(SummitEvent $summit_event){

        $location_name        = null;
        $location_title       = null;
        $location_lat         = null;
        $location_lng         = null;
        $location_street      = null;
        $location_city        = null;
        $location_state       = null;
        $location_country     = null;
        $location_postal_code = null;

        if($summit_event->hasLocation()){
            $venue       = $summit_event->getLocation();
            $room        = null;
            if($venue instanceof SummitVenueRoom){
                $room  = $venue;
                $venue = $venue->getVenue();
            }
            $location_full_name = $venue->getName();
            if(!is_null($room)){
                if($room->hasFloor()){
                    $location_full_name .= ', '.$room->getFloor()->getName();
                }
                $location_full_name .= ', '.$room->getName();
            }

            $location_name      = $location_full_name;
            $location_title     = $location_full_name;
            if($venue instanceof SummitGeoLocatedLocation) {
                $location_lat         = $venue->getLat();
                $location_lng         = $venue->getLng();
                $location_street      = $venue->getAddress1();
                $location_city        = $venue->getCity();
                $location_state       = $venue->getState();
                $location_country     = $venue->getCountry();
                $location_postal_code = $venue->getZipCode();
            }
        }

        return new EventVO(
            $summit_event->getTitle(),
            $summit_event->getAbstract(),
            $summit_event->getLocalStartDate(),
            $summit_event->getLocalEndDate(),
            $summit_event->getSummit()->getTimeZone(),
            new LocationVO
            (
                $location_name,
                new AddressVO
                (
                    $location_street,
                    $location_city,
                    $location_state,
                    $location_country,
                    $location_postal_code
                ),
                $location_lat,
                $location_lng
            )
        );
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @return ScheduleCalendarSyncInfo
     */
    public function addEvent(MemberEventScheduleSummitActionSyncWorkRequest $request)
    {
        $summit_event  = $request->getSummitEvent();
        $vo            = $this->buildEventVO($summit_event);
        $calendar_id   = $this->sync_calendar_info->getExternalId();
        $created_event = $this->client->createEvent($calendar_id, $vo);
        // new schedule sync info
        $sync_info = new ScheduleCalendarSyncInfo();
        // primitives
        $sync_info->setEtag($created_event->getEtag());
        $sync_info->setExternalId($created_event->getId());
        $sync_info->setExternalUrl($created_event->getDataId());
        // relationships
        $sync_info->setEvent($summit_event);
        $sync_info->setCalendarSyncInfo($this->sync_calendar_info);
        $sync_info->setLocation($summit_event->getLocation());
        return $sync_info;
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $schedule_sync_info
     * @return bool
     */
    public function deleteEvent
    (
        MemberEventScheduleSummitActionSyncWorkRequest $request,
        ScheduleCalendarSyncInfo $schedule_sync_info
    )
    {
        $res = $this->client->deleteEvent($schedule_sync_info->getExternalId());
        return !($res instanceof ErrorResponse);
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $schedule_sync_info
     * @return bool
     */
    public function updateEvent
    (
        MemberEventScheduleSummitActionSyncWorkRequest $request,
        ScheduleCalendarSyncInfo $schedule_sync_info
    )
    {
        $summit_event  = $request->getSummitEvent();
        $vo            = $this->buildEventVO($summit_event);
        $event_id      = $schedule_sync_info->getExternalId();
        $updated_event = $this->client->updateEvent($event_id, $vo);

        // primitives
        $schedule_sync_info->setEtag($updated_event->getEtag());
        // relationships
        $schedule_sync_info->setEvent($summit_event);
        $schedule_sync_info->setLocation($summit_event->getLocation());

        return true;
    }

    /**
     * @param MemberCalendarScheduleSummitActionSyncWorkRequest $request
     * @param CalendarSyncInfo $calendar_sync_info
     * @return bool
     */
    public function createCalendar
    (
        MemberCalendarScheduleSummitActionSyncWorkRequest $request,
        CalendarSyncInfo $calendar_sync_info
    )
    {
        $res = $this->client->createCalendar(new CalendarVO
        (
            $request->getCalendarName()
        ));

        return ($res instanceof CalendarResponse);
    }

    /**
     * @param MemberCalendarScheduleSummitActionSyncWorkRequest $request
     * @param CalendarSyncInfo $calendar_sync_info
     * @return bool
     */
    public function deleteCalendar
    (
        MemberCalendarScheduleSummitActionSyncWorkRequest $request,
        CalendarSyncInfo $calendar_sync_info
    )
    {
        $res = $this->client->deleteCalendar($calendar_sync_info->getExternalId());
        return !($res instanceof ErrorResponse);
    }
}
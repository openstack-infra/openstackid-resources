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

use models\summit\CalendarSync\CalendarSyncInfo;
use models\summit\CalendarSync\CalendarSyncInfoOAuth2;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\SummitEvent;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitVenueRoom;

use Illuminate\Support\Facades\Config;
/**
 * Class GoogleCalendarSyncRemoteFacade
 * @package services\apis\CalendarSync
 */
final class GoogleCalendarSyncRemoteFacade
    extends AbstractCalendarSyncRemoteFacade
{

    /**
     * @var \Google_Client
     */
    private $client;

    /**
     * GoogleCalendarSyncRemoteFacade constructor.
     * @param CalendarSyncInfoOAuth2 $sync_calendar_info
     */
    public function __construct(CalendarSyncInfoOAuth2 $sync_calendar_info)
    {
        parent::__construct($sync_calendar_info);

        $this->client = new \Google_Client();
        $this->client->setClientId(Config::get("google_api.google_client_id"));
        $this->client->setClientSecret(Config::get("google_api.google_client_secret"));
        //$this->client->setRedirectUri(Config::get("google_api.google_redirect_url"));
        $this->client->setScopes(explode(',', Config::get("google_api.google_scopes")));
        $this->client->setApprovalPrompt('force');
        $this->client->setAccessType('offline');
        $this->client->setAccessToken($sync_calendar_info->getAccessToken());
        $this->checkAccessToken();
    }

    private function checkAccessToken(){
        if($this->client->isAccessTokenExpired())
        {
            $creds         = $this->client->fetchAccessTokenWithRefreshToken($this->sync_calendar_info->getRefreshToken());
            $access_token  = isset($creds['access_token']) ? $creds['access_token'] : null;
            $refresh_token = isset($creds['refresh_token']) ? $creds['refresh_token'] : null;

            if(!empty($access_token)) {
                $this->sync_calendar_info->setAccessToken($creds);
            }

            if(!empty($refresh_token))
                $this->sync_calendar_info->setRefreshToken($refresh_token);
        }
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @return ScheduleCalendarSyncInfo
     */
    public function addEvent(MemberEventScheduleSummitActionSyncWorkRequest $request)
    {
        $this->checkAccessToken();
        $service       = new \Google_Service_Calendar($this->client);
        $summit_event  = $request->getSummitEvent();
        $vo            = $this->buildEventVO($summit_event);
        $calendar_id   = $this->sync_calendar_info->getExternalId();
        $created_event = $service->events->insert($calendar_id, $vo);

        // new schedule sync info
        $sync_info = new ScheduleCalendarSyncInfo();
        // primitives
        $sync_info->setEtag($created_event->getEtag());
        $sync_info->setExternalId($created_event->getId());
        $sync_info->setExternalUrl($created_event->getHtmlLink());
        // relationships
        $sync_info->setEvent($summit_event);
        $sync_info->setCalendarSyncInfo($this->sync_calendar_info);
        $sync_info->setLocation($summit_event->getLocation());
        return $sync_info;
    }

    /**
     * @param SummitEvent $summit_event
     * @return \Google_Service_Calendar_Event
     */
    private function buildEventVO(SummitEvent $summit_event){
        $vo = new \Google_Service_Calendar_Event();
        $vo->setSummary($summit_event->getTitle());
        $vo->setDescription($summit_event->getAbstract());

        $location_name  = null;
        $location_title = null;
        $location_lat   = null;
        $location_lng   = null;

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
                $location_lat = $venue->getLat();
                $location_lng = $venue->getLng();
            }
        }

        $vo->setLocation($location_full_name);

        // dates

        $time_zone = $summit_event->getSummit()->getTimeZone()->getName();
        $start = new \Google_Service_Calendar_EventDateTime();
        $start->setDateTime($summit_event->getLocalStartDate()->format(\DateTime::RFC3339));
        $start->setTimeZone($time_zone);
        $vo->setStart($start);

        $end = new \Google_Service_Calendar_EventDateTime();
        $end->setDateTime($summit_event->getLocalEndDate()->format(\DateTime::RFC3339));
        $end->setTimeZone($time_zone);
        $vo->setEnd($end);

        return $vo;
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
        $this->checkAccessToken();
        $service       = new \Google_Service_Calendar($this->client);
        $calendar_id   = $this->sync_calendar_info->getExternalId();
        $service->events->delete($calendar_id, $schedule_sync_info->getExternalId());
        return true;
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
        $this->checkAccessToken();
        $service       = new \Google_Service_Calendar($this->client);
        $summit_event  = $request->getSummitEvent();
        $vo            = $this->buildEventVO($summit_event);
        $calendar_id   = $this->sync_calendar_info->getExternalId();

        $updated_event = $service->events->update($calendar_id, $schedule_sync_info->getExternalId(), $vo);
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
        $this->checkAccessToken();
        $service           = new \Google_Service_Calendar($this->client);
        $google_calendar   = new \Google_Service_Calendar_Calendar($this->client);
        $google_calendar->setSummary($request->getCalendarName());
        $google_calendar->setDescription($request->getCalendarDescription());
        $google_calendar->setTimeZone($calendar_sync_info->getSummit()->getTimeZone()->getName());
        $created_calendar  = $service->calendars->insert($google_calendar);
        $calendar_id       = $created_calendar->getId();
        $calendarListEntry = new \Google_Service_Calendar_CalendarListEntry();
        $calendarListEntry->setId($calendar_id);
        $createdCalendarListEntry = $service->calendarList->insert($calendarListEntry);
        $this->sync_calendar_info->setExternalId($calendar_id);
        $this->sync_calendar_info->setEtag($created_calendar->getEtag());
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
        $this->checkAccessToken();
        $service = new \Google_Service_Calendar($this->client);
        $service->calendars->delete($calendar_sync_info->getExternalId());
    }
}
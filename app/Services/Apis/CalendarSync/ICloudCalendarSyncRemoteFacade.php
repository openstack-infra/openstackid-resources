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

use CalDAVClient\Facade\CalDavClient;
use CalDAVClient\Facade\Exceptions\ForbiddenException;
use CalDAVClient\Facade\Requests\EventRequestVO;
use CalDAVClient\Facade\Requests\MakeCalendarRequestVO;
use CalDAVClient\ICalDavClient;
use models\summit\CalendarSync\CalendarSyncInfoCalDav;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitEventCalendarSyncWorkRequest;


/**
 * Class ICloudCalendarSyncRemoteFacade
 * @package services\apis\CalendarSync
 */
final class ICloudCalendarSyncRemoteFacade
    extends AbstractCalendarSyncRemoteFacade
{
    /**
     * @var ICalDavClient
     */
    private $client;

    public function __construct(CalendarSyncInfoCalDav $sync_calendar_info)
    {
        parent::__construct($sync_calendar_info);

        $this->client = new CalDavClient(
            $this->sync_calendar_info->getServer(),
            $this->sync_calendar_info->getUserName(),
            $this->sync_calendar_info->getUserPassword()
        );

        if(empty($this->sync_calendar_info->getExternalId())){
            // calendar not created yet
           $this->createDefaultCalendar();
        }
    }


    private function createDefaultCalendar(){
        try {
            // get calendar home url
            $user_ppal_url = $this->sync_calendar_info->getUserPrincipalUrl();
            $res = $this->client->getCalendarHome
            (
                $user_ppal_url
            );
            $calendar_home = $res->getCalendarHomeSetUrl();
            $real_server   = $res->getRealCalDAVHost();

            // make calendar
            $summit        = $this->sync_calendar_info->getSummit();
            $display_name  = $summit->getName() . ' Calendar';
            $resource_name = $summit->getSlug();
            $calendar_url = $this->client->createCalendar($calendar_home,
                new MakeCalendarRequestVO(
                    $resource_name,
                    $display_name,
                    'Calendar to hold Summit Events',
                    $summit->getTimeZone()
                )
            );
            $calendar_url = rtrim($calendar_url, '/') . '/';
        }
        catch(ForbiddenException $ex){
            // calendar already exists
            $calendar_url = $calendar_home.'/'.$resource_name.'/';
        }

        $this->sync_calendar_info->setUserPrincipalUrl
        (
                str_replace
                (
                    "p01-caldav.icloud.com",
                    $real_server,
                    $user_ppal_url
                )
        );

        $this->sync_calendar_info->setCalendarDisplayName($display_name);
        $this->sync_calendar_info->setExternalId($calendar_url);

        $res = $this->client->getCalendar($calendar_url);
        $this->sync_calendar_info->setCalendarSyncToken($res->getSyncToken());

    }

    /**
     * @param MemberScheduleSummitEventCalendarSyncWorkRequest $request
     * @return ScheduleCalendarSyncInfo
     */
    public function addEvent(MemberScheduleSummitEventCalendarSyncWorkRequest $request)
    {
        $summit_event = $request->getEvent();
        $res          = $this->client->createEvent(

            $this->sync_calendar_info->getCalendarUrl(),
            new EventRequestVO(
                $this->sync_calendar_info->getCalendarDisplayName(),
                $summit_event->getTitle(),
                $summit_event->getAbstract(),
                $summit_event->getSocialSummary(),
                $summit_event->getLocalStartDate(),
                $summit_event->getLocalEndDate(),
                $summit_event->getSummit()->getTimeZone()
            )
        );

        $etag  = $res->getETag();
        $vcard = "";

        if(empty($etag)) {
            try {

                $resource_absolute_url = $res->getResourceUrl();
                $relative_resource_url = parse_url($resource_absolute_url, PHP_URL_PATH);

                $events_response = $this->client->getEventsBy
                (
                    $this->sync_calendar_info->getCalendarUrl(), [
                    $relative_resource_url
                ]);

                if ($events_response->isSuccessFull() && count($events_response->getResponses()) > 0) {
                    $etag = $events_response->getResponses()[0]->getETag();
                    $vcard = $events_response->getResponses()[0]->getVCard();
                }
            }
            catch (\Exception $ex){

            }
        }

        $sync_info = new ScheduleCalendarSyncInfo();
        // primitives
        $sync_info->setEtag($etag);
        $sync_info->setExternalId($res->getUid());
        $sync_info->setExternalUrl($res->getResourceUrl());
        $sync_info->setVCard($vcard);
        // relationships
        $sync_info->setEvent($summit_event);
        $sync_info->setSyncInfo($this->sync_calendar_info);
        $sync_info->setLocation($summit_event->getLocation());

        return $sync_info;
    }

    /**
     * @param MemberScheduleSummitEventCalendarSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $sync_info
     * @return void
     */
    public function deleteEvent
    (
        MemberScheduleSummitEventCalendarSyncWorkRequest $request,
        ScheduleCalendarSyncInfo $sync_info
    )
    {
        if(empty($sync_info->getEtag())) return false;
        $res = $this->client->deleteEvent
        (
            $this->sync_calendar_info->getCalendarUrl(),
            $sync_info->getExternalId(),
            $sync_info->getEtag()
        );

        return $res->isSuccessFull();
    }
    /**
     * @param MemberScheduleSummitEventCalendarSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $sync_info
     * @return void
     */
    public function updateEvent(MemberScheduleSummitEventCalendarSyncWorkRequest $request, ScheduleCalendarSyncInfo $sync_info)
    {
        // TODO: Implement updateEvent() method.
    }
}
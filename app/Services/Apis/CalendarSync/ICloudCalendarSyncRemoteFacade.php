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
use App\Services\Apis\CalendarSync\Exceptions\RevokedAccessException;
use CalDAVClient\Facade\CalDavClient;
use CalDAVClient\Facade\Exceptions\ConflictException;
use CalDAVClient\Facade\Exceptions\ForbiddenException;
use CalDAVClient\Facade\Exceptions\UserUnAuthorizedException;
use CalDAVClient\Facade\Requests\EventRequestVO;
use CalDAVClient\Facade\Requests\MakeCalendarRequestVO;
use CalDAVClient\ICalDavClient;
use models\summit\CalendarSync\CalendarSyncInfo;
use models\summit\CalendarSync\CalendarSyncInfoCalDav;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\SummitEvent;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitVenueRoom;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;
/**
 * Class ICloudCalendarSyncRemoteFacade
 * @package services\apis\CalendarSync
 */
final class ICloudCalendarSyncRemoteFacade
    extends AbstractCalendarSyncRemoteFacade
{
    const NonExistParentConflict = 'non-existent parent';

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
    }

    /**
     * @param $prod_id
     * @param SummitEvent $summit_event
     * @param bool $update
     * @return EventRequestVO
     */
    private function buildEventVO($prod_id, SummitEvent $summit_event, $update = false){
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
                    $location_full_name .= ' - '.$room->getFloor()->getName();
                }
                $location_full_name .= ' - '.$room->getName();
            }

            $location_name      = $location_full_name;
            $location_title     = $location_full_name;
            if($venue instanceof SummitGeoLocatedLocation) {
                $location_lat = $venue->getLat();
                $location_lng = $venue->getLng();
            }
        }

        $title = $summit_event->getTitle();
        if($update) $title = "{$title} [UPDATED]";
        return new EventRequestVO(
            $prod_id,
            $title,
            $summit_event->getAbstract(),
            $summit_event->getSocialSummary(),
            $summit_event->getLocalStartDate(),
            $summit_event->getLocalEndDate(),
            $summit_event->getSummit()->getTimeZone(),
            $location_name,
            $location_title,
            $location_lat,
            $location_lng
        );
    }

    /**
     * @param string $calendar_url
     * @param string $event_url
     * @return array
     */
    private function getEventInfo($calendar_url, $event_url){
        $etag  = null;
        $vcard = null;

        try {

            $relative_resource_url = parse_url($event_url, PHP_URL_PATH);

            $events_response = $this->client->getEventsBy
            (
                $calendar_url, [
                $relative_resource_url
            ]);

            if ($events_response->isSuccessFull() && count($events_response->getResponses()) > 0) {
                $etag  = $events_response->getResponses()[0]->getETag();
                $vcard = $events_response->getResponses()[0]->getVCard();
            }
        }
        catch (\Exception $ex){

        }
        return [$etag, $vcard];
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @return ScheduleCalendarSyncInfo|null
     * @throws RevokedAccessException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addEvent(MemberEventScheduleSummitActionSyncWorkRequest $request)
    {
        try {
            $summit_event = $request->getSummitEvent();
            $calendar_url = $this->sync_calendar_info->getCalendarUrl();
            $vo = $this->buildEventVO($this->sync_calendar_info->getCalendarDisplayName(), $summit_event);
            $res = $this->client->createEvent(
                $calendar_url,
                $vo
            );

            $etag = $res->getETag();
            $vcard = "";

            if (empty($etag)) {
                list($etag, $vcard) = $this->getEventInfo($calendar_url, $res->getResourceUrl());
            }

            $sync_info = new ScheduleCalendarSyncInfo();
            // primitives
            $sync_info->setEtag($etag);
            $sync_info->setExternalId($res->getUid());
            $sync_info->setExternalUrl($res->getResourceUrl());
            $sync_info->setVCard($vcard);
            // relationships
            $sync_info->setSummitEventId($summit_event->getId());
            $sync_info->setCalendarSyncInfo($this->sync_calendar_info);
            $sync_info->setLocationId($summit_event->getLocationId());

            return $sync_info;
        }
        catch(UserUnAuthorizedException $ex1){
            Log::warning($ex1);
            throw new RevokedAccessException($ex1->getMessage());
        }
        catch (ConflictException $ex2){
            Log::warning($ex2);
            if(strpos($ex2->getMessage(), self::NonExistParentConflict) != false)
                throw new RevokedAccessException($ex2->getMessage());
        }
        catch (Exception $ex){
            Log::error($ex);
            return null;
        }
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $schedule_sync_info
     * @return bool
     * @throws RevokedAccessException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateEvent
    (
        MemberEventScheduleSummitActionSyncWorkRequest $request,
        ScheduleCalendarSyncInfo $schedule_sync_info
    )
    {
        try {
            $summit_event = $request->getSummitEvent();
            $calendar_url = $this->sync_calendar_info->getCalendarUrl();
            $vo = $this->buildEventVO($this->sync_calendar_info->getCalendarDisplayName(), $summit_event, true);
            $vo->setUID($schedule_sync_info->getExternalId());
            $res = $this->client->updateEvent(
                $calendar_url,
                $vo
            );

            $etag = $res->getETag();
            $vcard = "";

            if (empty($etag)) {
                list($etag, $vcard) = $this->getEventInfo($calendar_url, $res->getResourceUrl());
            }

            // primitives
            $schedule_sync_info->setEtag($etag);
            $schedule_sync_info->setVCard($vcard);
            // relationships
            $schedule_sync_info->setLocationId($summit_event->getLocationId());
            return true;
        }
        catch(UserUnAuthorizedException $ex1){
            Log::warning($ex1);
            throw new RevokedAccessException($ex1->getMessage());
        }
        catch (ConflictException $ex2){
            Log::warning($ex2);
            if(strpos($ex2->getMessage(), self::NonExistParentConflict) != false)
                throw new RevokedAccessException($ex2->getMessage());
        }
        catch (Exception $ex){
            Log::error($ex);
            return false;
        }
    }

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $schedule_sync_info
     * @return bool
     * @throws RevokedAccessException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteEvent(MemberEventScheduleSummitActionSyncWorkRequest $request, ScheduleCalendarSyncInfo $schedule_sync_info)
    {
        try{
            if(empty($schedule_sync_info->getEtag())) return false;

            $res = $this->client->deleteEvent
            (
                $this->sync_calendar_info->getCalendarUrl(),
                $schedule_sync_info->getExternalId()
            );

            return $res->isSuccessFull();
        }
        catch(UserUnAuthorizedException $ex1){
            Log::warning($ex1);
            throw new RevokedAccessException($ex1->getMessage());
        }
        catch (ConflictException $ex2){
            Log::warning($ex2);
            if(strpos($ex2->getMessage(), self::NonExistParentConflict) != false)
                throw new RevokedAccessException($ex2->getMessage());
        }
        catch (Exception $ex){
            Log::error($ex);
            return false;
        }
    }

    /**
     * @param MemberCalendarScheduleSummitActionSyncWorkRequest $request
     * @param CalendarSyncInfo $calendar_sync_info
     * @return bool
     * @throws RevokedAccessException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCalendar(MemberCalendarScheduleSummitActionSyncWorkRequest $request, CalendarSyncInfo $calendar_sync_info)
    {
        try{
            try {
                // get calendar home url
                $user_ppal_url = $this->sync_calendar_info->getUserPrincipalUrl();
                $res           = $this->client->getCalendarHome
                (
                    $user_ppal_url
                );
                $calendar_home = $res->getCalendarHomeSetUrl();
                $real_server   = $res->getRealCalDAVHost();

                // make calendar
                $summit        = $this->sync_calendar_info->getSummit();
                $calendar_url  = $this->client->createCalendar($calendar_home,
                    new MakeCalendarRequestVO(
                        $request->getCalendarId(),
                        $request->getCalendarName(),
                        $request->getCalendarDescription(),
                        $summit->getTimeZone()
                    )
                );
                $calendar_url = rtrim($calendar_url, '/') . '/';
            }
            catch(ForbiddenException $ex){
                // calendar already exists
                $calendar_url = $calendar_home.'/'.$request->getCalendarId().'/';
            }

            $this->sync_calendar_info->setUserPrincipalUrl
            (
                str_replace
                (
                    Config::get("apple_api.base_caldav_server"),
                    $real_server,
                    $user_ppal_url
                )
            );

            $this->sync_calendar_info->setCalendarDisplayName($request->getCalendarName());
            $this->sync_calendar_info->setExternalId($calendar_url);

            $res = $this->client->getCalendar($calendar_url);
            $this->sync_calendar_info->setCalendarSyncToken($res->getSyncToken());
            return true;
        }
        catch(UserUnAuthorizedException $ex1){
            Log::warning($ex1);
            throw new RevokedAccessException($ex1->getMessage());
        }
        catch (Exception $ex){
            Log::error($ex);
            return false;
        }
    }

    /**
     * @param MemberCalendarScheduleSummitActionSyncWorkRequest $request
     * @param CalendarSyncInfo $calendar_sync_info
     * @return bool
     * @throws RevokedAccessException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteCalendar(MemberCalendarScheduleSummitActionSyncWorkRequest $request, CalendarSyncInfo $calendar_sync_info)
    {
        try {
            $this->client->deleteCalendar($this->sync_calendar_info->getExternalId());
            return true;
        }
        catch(UserUnAuthorizedException $ex1){
            Log::warning($ex1);
            throw new RevokedAccessException($ex1->getMessage());
        }
        catch (Exception $ex){
            Log::error($ex);
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getSleepInterval()
    {
        return 500;
    }
}
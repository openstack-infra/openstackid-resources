<?php
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

use Illuminate\Support\Facades\App;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\SummitEvent;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\CalendarSyncInfo;
use models\main\Member;

/**
 * Class MemberActionsCalendarSyncPreProcessorTest
 */
final class MemberActionsCalendarSyncPreProcessorTest extends TestCase
{

    public function __construct()
    {
    }

    protected function prepareForTests()
    {
        parent::prepareForTests();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function createApplication()
    {
        $app = parent::createApplication();

        $repo_mock = Mockery::mock(IAbstractCalendarSyncWorkRequestRepository::class)->shouldIgnoreMissing();
        $app->instance(IAbstractCalendarSyncWorkRequestRepository::class, $repo_mock);

        $repo_mock = Mockery::mock(ICalendarSyncInfoRepository::class)->shouldIgnoreMissing();
        $app->instance(ICalendarSyncInfoRepository::class, $repo_mock);
        return $app;
    }

    public function testSynchronizedRemoveUpdateAdd(){

        $preprocessor = App::make('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $member_mock =  Mockery::mock(Member::class);
        $member_mock->shouldReceive("isEventSynchronized")->andReturn(true);

        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class);
        $calendar_sync_info->shouldReceive("getId")->andReturn(1);

        $request_delete_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_delete_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_delete_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);
        $request_delete_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_delete_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);

        $request_update_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_update_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_update_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);
        $request_update_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_update_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);

        $request_add_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_add_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_add_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request_add_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);
        $request_add_mock->shouldReceive("getOwner")->andReturn($member_mock);


        // preconditions event id 1 is already synchronized with user external calendar
        // we pass over the time this work load
        // delete , update, add
        // bc event is already synchronized, the purged actions should only emmit an update
        $purged_requests = $preprocessor->preProcessActions([
            $request_delete_mock,
            $request_update_mock,
            $request_add_mock
        ]);

        $this->assertTrue(count($purged_requests) == 1);

        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeUpdate);
    }

    public function testSynchronizedRemoveAdd(){

        $preprocessor = App::make('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $member_mock =  Mockery::mock(Member::class);
        $member_mock->shouldReceive("isEventSynchronized")->andReturn(true);

        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class);
        $calendar_sync_info->shouldReceive("getId")->andReturn(1);

        $request_delete_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_delete_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_delete_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);
        $request_delete_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_delete_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);

        $request_add_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_add_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_add_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request_add_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);
        $request_add_mock->shouldReceive("getOwner")->andReturn($member_mock);


        // preconditions event id 1 is already synchronized with user external calendar
        // we pass over the time this work load
        // delete , add
        // bc event is already synchronized, the purged actions should only emmit zero elements ( no action)
        $purged_requests = $preprocessor->preProcessActions([
            $request_delete_mock,
            $request_add_mock
        ]);

        $this->assertTrue(count($purged_requests) == 0);
    }

    public function testSynchronizedRemoveUpdate(){

        $preprocessor = App::make('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $member_mock =  Mockery::mock(Member::class);
        $member_mock->shouldReceive("isEventSynchronized")->andReturn(true);

        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class);
        $calendar_sync_info->shouldReceive("getId")->andReturn(1);

        $request_delete_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_delete_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_delete_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);
        $request_delete_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_delete_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);

        $request_update_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_update_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_update_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);
        $request_update_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_update_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);


        // preconditions event id 1 is already synchronized with user external calendar
        // we pass over the time this work load
        // delete, update
        // bc event is already synchronized, the purged actions should only emmit an delete
        $purged_requests = $preprocessor->preProcessActions([
            $request_delete_mock,
            $request_update_mock,
        ]);

        $this->assertTrue(count($purged_requests) == 1);

        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeRemove);
    }

    public function testSynchronizedUpdateRemove(){

        $preprocessor = App::make('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $member_mock =  Mockery::mock(Member::class);
        $member_mock->shouldReceive("isEventSynchronized")->andReturn(true);

        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class);
        $calendar_sync_info->shouldReceive("getId")->andReturn(1);

        $request_delete_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_delete_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_delete_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);
        $request_delete_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_delete_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);

        $request_update_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_update_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_update_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);
        $request_update_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_update_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);


        // preconditions event id 1 is already synchronized with user external calendar
        // we pass over the time this work load
        // update, delete
        // bc event is already synchronized, the purged actions should only emmit an delete
        $purged_requests = $preprocessor->preProcessActions([
            $request_update_mock,
            $request_delete_mock,
        ]);

        $this->assertTrue(count($purged_requests) == 1);

        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeRemove);
    }

    public function testUnSynchronizedAddRemoveAdd(){

        $preprocessor = App::make('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $member_mock =  Mockery::mock(Member::class);
        $member_mock->shouldReceive("isEventSynchronized")->andReturn(false);

        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class);
        $calendar_sync_info->shouldReceive("getId")->andReturn(1);


        $request_add_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_add_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_add_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request_add_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);
        $request_add_mock->shouldReceive("getOwner")->andReturn($member_mock);


        $request_delete_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_delete_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_delete_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);
        $request_delete_mock->shouldReceive("getOwner")->andReturn($member_mock);
        $request_delete_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);

        $request_add_mock2 = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_add_mock2->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_add_mock2->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request_add_mock2->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);
        $request_add_mock2->shouldReceive("getOwner")->andReturn($member_mock);


        // preconditions event id 1 is not synchronized
        // we pass over the time this work load
        // add , delete, add
        // bc event is already synchronized, the purged actions should only emmit 1 element : add
        $purged_requests = $preprocessor->preProcessActions([
            $request_add_mock,
            $request_delete_mock,
            $request_add_mock2
        ]);

        $this->assertTrue(count($purged_requests) == 1);
        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeAdd);
    }

    public function testUnSynchronizedAddAdd(){

        $preprocessor = App::make('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $member_mock =  Mockery::mock(Member::class);
        $member_mock->shouldReceive("isEventSynchronized")->andReturn(false);

        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class);
        $calendar_sync_info->shouldReceive("getId")->andReturn(1);


        $request_add_mock = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_add_mock->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_add_mock->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request_add_mock->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);
        $request_add_mock->shouldReceive("getOwner")->andReturn($member_mock);

        $request_add_mock2 = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class);
        $request_add_mock2->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $request_add_mock2->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request_add_mock2->shouldReceive("getCalendarSyncInfo")->andReturn($calendar_sync_info);
        $request_add_mock2->shouldReceive("getOwner")->andReturn($member_mock);


        // preconditions event id 1 is not synchronized
        // we pass over the time this work load
        // add , delete, add
        // bc event is already synchronized, the purged actions should only emmit 1 element : add
        $purged_requests = $preprocessor->preProcessActions([
            $request_add_mock,
            $request_add_mock2
        ]);

        $this->assertTrue(count($purged_requests) == 1);
        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeAdd);
    }
}
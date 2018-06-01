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
use App\Services\Model\MemberActionsCalendarSyncProcessingService;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use models\main\IEmailCreationRequestRepository;
use App\Services\Model\ICalendarSyncWorkRequestPreProcessor;
use App\Services\Apis\CalendarSync\ICalendarSyncRemoteFacadeFactory;
use utils\PagingResponse;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\SummitEvent;
use models\main\Member;
use models\summit\CalendarSync\CalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use services\apis\CalendarSync\ICalendarSyncRemoteFacade;
use CalDAVClient\Facade\Exceptions\ConflictException;
/**
 * Class SummitICloudCalendarSyncTest
 */
final class SummitICloudCalendarSyncTest extends TestCase
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


        $repo_mock = Mockery::mock(IAbstractCalendarSyncWorkRequestRepository::class)
                     ->shouldIgnoreMissing();

        $repo_mock->shouldReceive('getUnprocessedMemberScheduleWorkRequestActionByPage')->andReturn(new PagingResponse(1, 10, 1, 1, []));

        $app->instance(IAbstractCalendarSyncWorkRequestRepository::class, $repo_mock);

        $repo_mock = Mockery::mock(ICalendarSyncInfoRepository::class)->shouldIgnoreMissing();
        $app->instance(ICalendarSyncInfoRepository::class, $repo_mock);

        $values     = [];
        $request     = Mockery::mock(MemberEventScheduleSummitActionSyncWorkRequest::class)->shouldIgnoreMissing();
        $request->shouldReceive('getSummitEventId')->andReturn(1);
        $summit_event = Mockery::mock(SummitEvent::class)->shouldIgnoreMissing();
        $member       = Mockery::mock(Member::class)->shouldIgnoreMissing();
        $calendar_sync_info = Mockery::mock(CalendarSyncInfo::class)->shouldIgnoreMissing();
        $summit_event->shouldReceive('getId')->andReturn(1);

        $request->shouldReceive('getSummitEvent')->andReturn($summit_event);

        $request->shouldReceive('getCalendarSyncInfo')->andReturn($calendar_sync_info);
        $request->shouldReceive('getOwner')->andReturn($member);
        $request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeAdd);
        $request->shouldReceive('getSubType')->andReturn(MemberEventScheduleSummitActionSyncWorkRequest::SubType);

        $values[]   = $request;

        $repo_email = Mockery::mock(IEmailCreationRequestRepository::class)
                      ->shouldIgnoreMissing();
        $app->instance(IEmailCreationRequestRepository::class, $repo_email);

        $processor = Mockery::mock(ICalendarSyncWorkRequestPreProcessor::class)->shouldIgnoreMissing();
        $processor->shouldReceive('preProcessActions')->andReturn($values);

        $app->instance(ICalendarSyncWorkRequestPreProcessor::class, $processor);

        $facade  = Mockery::mock(ICalendarSyncRemoteFacade::class)->shouldIgnoreMissing();
        $message = <<<HTML
<html><head><title>Conflict</title></head><body><h1>Conflict</h1><p>cannot
PUT to non-existent parent</p></body></html>
HTML;
        $error   = new ConflictException($message, 409);
        $facade->shouldReceive('addEvent')->andThrow($error);

        $factory = Mockery::mock(ICalendarSyncRemoteFacadeFactory::class)->shouldIgnoreMissing();
        $factory->shouldReceive('build')->andReturn($facade);
        $app->instance(ICalendarSyncRemoteFacadeFactory::class, $factory);
        return $app;
    }

    public function test409ResponseFromCalDav(){

        $service = App::make(MemberActionsCalendarSyncProcessingService::class);
        
        $service->processActions(CalendarSyncInfo::ProvideriCloud, 1000);
    }
}
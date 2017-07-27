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
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use models\summit\SummitEvent;
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
/**
 * Class AdminActionsCalendarSyncPreProcessorTest
 */
final class AdminActionsCalendarSyncPreProcessorTest extends TestCase
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

    public function testUpdateFourthSameEvent(){
        $preprocessor = App::make('App\Services\Model\AdminActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $mock_update_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_update_request1 = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request1->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request1->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_update_request2  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request2->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request2->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_update_request3  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request3->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request3->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $purged_requests = $preprocessor->preProcessActions([
            $mock_update_request,
            $mock_update_request1,
            $mock_update_request2,
            $mock_update_request3
        ]);

        $this->assertTrue(count($purged_requests) == 1);
    }

    public function testUpdateFourthTimesDeleteSameEvent(){
        $preprocessor = App::make('App\Services\Model\AdminActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $mock_update_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_update_request1 = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request1->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request1->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_update_request2  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request2->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request2->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_update_request3  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request3->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request3->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $mock_delete_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_delete_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_delete_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);

        $purged_requests = $preprocessor->preProcessActions([
            $mock_update_request,
            $mock_update_request1,
            $mock_delete_request,
            $mock_update_request2,
            $mock_update_request3
        ]);

        $this->assertTrue(count($purged_requests) == 1);
        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeUpdate);
    }

    public function testDeleteUpdateSameEvent(){
        $preprocessor = App::make('App\Services\Model\AdminActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $mock_delete_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_delete_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_delete_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);

        $mock_update_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $purged_requests = $preprocessor->preProcessActions([
            $mock_delete_request,
            $mock_update_request
        ]);

        $this->assertTrue(count($purged_requests) == 1);
        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeUpdate);
    }


    public function testUpdateDeleteSameEvent(){

        $preprocessor = App::make('App\Services\Model\AdminActionsCalendarSyncPreProcessor');

        $summit_event =  Mockery::mock(SummitEvent::class);
        $summit_event->shouldReceive('getId')->andReturn(1);

        $mock_delete_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_delete_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_delete_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeRemove);

        $mock_update_request  = Mockery::mock(AdminSummitEventActionSyncWorkRequest::class);
        $mock_update_request->shouldReceive('getSummitEvent')->andReturn($summit_event);
        $mock_update_request->shouldReceive('getType')->andReturn(AbstractCalendarSyncWorkRequest::TypeUpdate);

        $purged_requests = $preprocessor->preProcessActions([
            $mock_update_request,
            $mock_delete_request,
        ]);

        $this->assertTrue(count($purged_requests) == 1);
        $this->assertTrue($purged_requests[0]->getType() == AbstractCalendarSyncWorkRequest::TypeRemove);
    }

}
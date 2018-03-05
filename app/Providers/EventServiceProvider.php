<?php namespace App\Providers;
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
use App\EntityPersisters\AdminSummitEventActionSyncWorkRequestPersister;
use App\EntityPersisters\AdminSummitLocationActionSyncWorkRequestPersister;
use App\EntityPersisters\AssetSyncRequestPersister;
use App\EntityPersisters\EntityEventPersister;
use App\Factories\AssetsSyncRequest\FileCreatedAssetSyncRequestFactory;
use App\Factories\CalendarAdminActionSyncWorkRequest\AdminSummitLocationActionSyncWorkRequestFactory;
use App\Factories\CalendarAdminActionSyncWorkRequest\SummitEventDeletedCalendarSyncWorkRequestFactory;
use App\Factories\CalendarAdminActionSyncWorkRequest\SummitEventUpdatedCalendarSyncWorkRequestFactory;
use App\Factories\EntityEvents\FloorActionEntityEventFactory;
use App\Factories\EntityEvents\LocationActionEntityEventFactory;
use App\Factories\EntityEvents\MyFavoritesAddEntityEventFactory;
use App\Factories\EntityEvents\MyFavoritesRemoveEntityEventFactory;
use App\Factories\EntityEvents\MyScheduleAddEntityEventFactory;
use App\Factories\EntityEvents\MyScheduleRemoveEntityEventFactory;
use App\Factories\EntityEvents\PresentationMaterialCreatedEntityEventFactory;
use App\Factories\EntityEvents\PresentationMaterialDeletedEntityEventFactory;
use App\Factories\EntityEvents\PresentationMaterialUpdatedEntityEventFactory;
use App\Factories\EntityEvents\PresentationSpeakerCreatedEntityEventFactory;
use App\Factories\EntityEvents\PresentationSpeakerDeletedEntityEventFactory;
use App\Factories\EntityEvents\PresentationSpeakerUpdatedEntityEventFactory;
use App\Factories\EntityEvents\SummitEventCreatedEntityEventFactory;
use App\Factories\EntityEvents\SummitEventDeletedEntityEventFactory;
use App\Factories\EntityEvents\SummitEventTypeActionEntityEventFactory;
use App\Factories\EntityEvents\SummitEventUpdatedEntityEventFactory;
use App\Factories\EntityEvents\TrackActionEntityEventFactory;
use App\Services\Utils\SCPFileUploader;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
/**
 * Class EventServiceProvider
 * @package App\Providers
 */
final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Database\Events\QueryExecuted' => [
            'App\Listeners\QueryExecutedListener',
        ],
    ];

    /**
     * Register any other events for your application.
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(\App\Events\MyScheduleAdd::class, function($event)
        {
            EntityEventPersister::persist(MyScheduleAddEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\MyFavoritesAdd::class, function($event)
        {
            EntityEventPersister::persist(MyFavoritesAddEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\MyScheduleRemove::class, function($event)
        {
            EntityEventPersister::persist(MyScheduleRemoveEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\MyFavoritesRemove::class, function($event)
        {
            EntityEventPersister::persist(MyFavoritesRemoveEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\SummitEventCreated::class, function($event)
        {
            EntityEventPersister::persist(SummitEventCreatedEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\SummitEventUpdated::class, function($event)
        {
            EntityEventPersister::persist(SummitEventUpdatedEntityEventFactory::build($event));
            AdminSummitEventActionSyncWorkRequestPersister::persist(SummitEventUpdatedCalendarSyncWorkRequestFactory::build($event));
        });

        Event::listen(\App\Events\SummitEventDeleted::class, function($event)
        {
            EntityEventPersister::persist(SummitEventDeletedEntityEventFactory::build($event));

            $request = SummitEventDeletedCalendarSyncWorkRequestFactory::build($event);
            if(!is_null($request))
                AdminSummitEventActionSyncWorkRequestPersister::persist($request);
        });

        Event::listen(\App\Events\PresentationMaterialCreated::class, function($event)
        {
            EntityEventPersister::persist(PresentationMaterialCreatedEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\PresentationMaterialUpdated::class, function($event)
        {
            EntityEventPersister::persist(PresentationMaterialUpdatedEntityEventFactory::build(($event)));
        });

        Event::listen(\App\Events\PresentationMaterialDeleted::class, function($event)
        {
            EntityEventPersister::persist(PresentationMaterialDeletedEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\FileCreated::class, function($event)
        {
            SCPFileUploader::upload($event);
            AssetSyncRequestPersister::persist(FileCreatedAssetSyncRequestFactory::build($event));
        });

        Event::listen(\App\Events\PresentationSpeakerCreated::class, function($event)
        {
            EntityEventPersister::persist_list(PresentationSpeakerCreatedEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\PresentationSpeakerUpdated::class, function($event)
        {
            EntityEventPersister::persist_list(PresentationSpeakerUpdatedEntityEventFactory::build($event));
        });

        Event::listen(\App\Events\PresentationSpeakerDeleted::class, function($event)
        {
            EntityEventPersister::persist_list(PresentationSpeakerDeletedEntityEventFactory::build($event));
        });

        // event types

        Event::listen(\App\Events\SummitEventTypeInserted::class, function($event)
        {
            EntityEventPersister::persist(SummitEventTypeActionEntityEventFactory::build($event, 'INSERT'));
        });

        Event::listen(\App\Events\SummitEventTypeUpdated::class, function($event)
        {
            EntityEventPersister::persist(SummitEventTypeActionEntityEventFactory::build($event, 'UPDATE'));
        });

        Event::listen(\App\Events\SummitEventTypeDeleted::class, function($event)
        {
            EntityEventPersister::persist(SummitEventTypeActionEntityEventFactory::build($event, 'DELETE'));
        });

        // tracks

        Event::listen(\App\Events\TrackInserted::class, function($event)
        {
            EntityEventPersister::persist(TrackActionEntityEventFactory::build($event, 'INSERT'));
        });

        Event::listen(\App\Events\TrackUpdated::class, function($event)
        {
            EntityEventPersister::persist(TrackActionEntityEventFactory::build($event, 'UPDATE'));
        });

        Event::listen(\App\Events\TrackDeleted::class, function($event)
        {
            EntityEventPersister::persist(TrackActionEntityEventFactory::build($event, 'DELETE'));
        });

        // locations events

        Event::listen(\App\Events\LocationInserted::class, function($event)
        {
            EntityEventPersister::persist(LocationActionEntityEventFactory::build($event, 'INSERT'));
        });

        Event::listen(\App\Events\LocationUpdated::class, function($event)
        {
            EntityEventPersister::persist(LocationActionEntityEventFactory::build($event, 'UPDATE'));
            $published_events = $event->getRelatedEventIds();
            if(count($published_events) > 0){
                AdminSummitLocationActionSyncWorkRequestPersister::persist
                (
                    AdminSummitLocationActionSyncWorkRequestFactory::build($event, 'UPDATE')
                );
            }
        });

        Event::listen(\App\Events\LocationDeleted::class, function($event)
        {
            EntityEventPersister::persist(LocationActionEntityEventFactory::build($event, 'DELETE'));
            $published_events = $event->getRelatedEventIds();
            if(count($published_events) > 0){
                AdminSummitLocationActionSyncWorkRequestPersister::persist
                (
                    AdminSummitLocationActionSyncWorkRequestFactory::build($event, 'REMOVE')
                );
            }
        });

        Event::listen(\App\Events\FloorInserted::class, function($event)
        {
            EntityEventPersister::persist(FloorActionEntityEventFactory::build($event, 'INSERT'));
        });

        Event::listen(\App\Events\FloorUpdated::class, function($event)
        {
            EntityEventPersister::persist(FloorActionEntityEventFactory::build($event, 'UPDATE'));

        });

        Event::listen(\App\Events\FloorDeleted::class, function($event)
        {
            EntityEventPersister::persist(FloorActionEntityEventFactory::build($event, 'DELETE'));
        });

    }
}

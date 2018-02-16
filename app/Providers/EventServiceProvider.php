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
use App\EntityPersisters\AssetSyncRequestPersister;
use App\EntityPersisters\EntityEventPersister;
use App\Events\PresentationSpeakerCreated;
use App\Events\PresentationSpeakerDeleted;
use App\Events\PresentationSpeakerUpdated;
use App\Factories\AssetsSyncRequest\FileCreatedAssetSyncRequestFactory;
use App\Factories\CalendarAdminActionSyncWorkRequest\SummitEventDeletedCalendarSyncWorkRequestFactory;
use App\Factories\CalendarAdminActionSyncWorkRequest\SummitEventUpdatedCalendarSyncWorkRequestFactory;
use App\Factories\EntityEvents\MyFavoritesAddEntityEventFactory;
use App\Factories\EntityEvents\MyScheduleAddEntityEventFactory;
use App\Factories\EntityEvents\SummitEventCreatedEntityEventFactory;
use App\Factories\EntityEvents\SummitEventDeletedEntityEventFactory;
use App\Factories\EntityEvents\SummitEventUpdatedEntityEventFactory;
use App\Services\Utils\SCPFileUploader;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use models\summit\SummitEntityEvent;
use models\utils\PreRemoveEventArgs;
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
            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName('MySchedule');
            $entity_event->setEntityId($event->getEventId());
            $entity_event->setType('DELETE');
            $entity_event->setOwner($event->getMember());
            $entity_event->setSummit($event->getSummit());
            $entity_event->setMetadata('');

            EntityEventPersister::persist($entity_event);

        });

        Event::listen(\App\Events\MyFavoritesRemove::class, function($event)
        {

            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName('MyFavorite');
            $entity_event->setEntityId($event->getEventId());
            $entity_event->setType('DELETE');
            $entity_event->setOwner($event->getMember());
            $entity_event->setSummit($event->getSummit());
            $entity_event->setMetadata('');

            EntityEventPersister::persist($entity_event);

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

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->setEntityClassName($event->getMaterial()->getClassName());
            $entity_event->setEntityId($event->getMaterial()->getId());
            $entity_event->setType('INSERT');

            if($owner_id > 0){
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($event->getMaterial()->getPresentation()->getSummit());
            $entity_event->setMetadata(json_encode([ 'presentation_id' => intval($event->getMaterial()->getPresentation()->getId())]));

            EntityEventPersister::persist($entity_event);

        });

        Event::listen(\App\Events\PresentationMaterialUpdated::class, function($event)
        {

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->setEntityClassName($event->getMaterial()->getClassName());
            $entity_event->setEntityId($event->getMaterial()->getId());
            $entity_event->setType('UPDATE');

            if($owner_id > 0){
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($event->getMaterial()->getPresentation()->getSummit());
            $entity_event->setMetadata(json_encode([ 'presentation_id' => intval($event->getMaterial()->getPresentation()->getId())]));

            EntityEventPersister::persist($entity_event);

        });

        Event::listen(\App\Events\PresentationMaterialDeleted::class, function($event)
        {

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName($event->getClassName());
            $entity_event->setEntityId($event->getMaterialId());
            $entity_event->setType('DELETE');

            if($owner_id > 0){
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($event->getPresentation()->getSummit());

            EntityEventPersister::persist($entity_event);
        });

        Event::listen(\App\Events\FileCreated::class, function($event)
        {
            SCPFileUploader::upload($event);
            AssetSyncRequestPersister::persist(FileCreatedAssetSyncRequestFactory::build($event));
        });

        Event::listen(\App\Events\PresentationSpeakerCreated::class, function($event)
        {
            if(!$event instanceof PresentationSpeakerCreated) return;

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            foreach($event->getPresentationSpeaker()->getRelatedSummits() as $summit) {

                $entity_event = new SummitEntityEvent;
                $entity_event->setEntityClassName("PresentationSpeaker");
                $entity_event->setEntityId($event->getPresentationSpeaker()->getId());
                $entity_event->setType('INSERT');

                if ($owner_id > 0) {
                    $member = $member_repository->getById($owner_id);
                    $entity_event->setOwner($member);
                }

                $entity_event->setSummit($summit);
                $entity_event->setMetadata('');

                EntityEventPersister::persist($entity_event);
            }

        });

        Event::listen(\App\Events\PresentationSpeakerUpdated::class, function($event)
        {
            if(!$event instanceof PresentationSpeakerUpdated) return;

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            foreach($event->getPresentationSpeaker()->getRelatedSummits() as $summit) {

                $entity_event = new SummitEntityEvent;
                $entity_event->setEntityClassName("PresentationSpeaker");
                $entity_event->setEntityId($event->getPresentationSpeaker()->getId());
                $entity_event->setType('UPDATE');

                if ($owner_id > 0) {
                    $member = $member_repository->getById($owner_id);
                    $entity_event->setOwner($member);
                }

                $entity_event->setSummit($summit);
                $entity_event->setMetadata('');

                EntityEventPersister::persist($entity_event);
            }

        });

        Event::listen(\App\Events\PresentationSpeakerDeleted::class, function($event)
        {
            if(!$event instanceof PresentationSpeakerDeleted) return;
            $args = $event->getArgs();
            if(!$args instanceof PreRemoveEventArgs) return;

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;
            $params = $args->getParams();

            foreach($params['summits'] as $summit) {

                $entity_event = new SummitEntityEvent;
                $entity_event->setEntityClassName($params['class_name']);
                $entity_event->setEntityId($params['id']);
                $entity_event->setType('DELETE');

                if ($owner_id > 0) {
                    $member = $member_repository->getById($owner_id);
                    $entity_event->setOwner($member);
                }

                $entity_event->setSummit($summit);
                $entity_event->setMetadata('');

                EntityEventPersister::persist($entity_event);
            }

        });

    }
}

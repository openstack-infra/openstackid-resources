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
use App\Events\MyFavoritesAdd;
use App\Events\SummitEventCreated;
use App\Events\SummitEventDeleted;
use App\Events\SummitEventUpdated;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use LaravelDoctrine\ORM\Facades\Registry;
use models\main\AssetsSyncRequest;
use models\summit\SummitEntityEvent;
use App\Events\MyScheduleAdd;
use App\Events\MyScheduleRemove;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use models\utils\PreRemoveEventArgs;
use IDCT\Networking\Ssh\SftpClient;
use IDCT\Networking\Ssh\Credentials;

/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
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
            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName('MySchedule');
            $entity_event->setEntityId($event->getEventId());
            $entity_event->setType('INSERT');
            $entity_event->setOwner($event->getMember());
            $entity_event->setSummit($event->getSummit());
            $entity_event->setMetadata('');

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();
        });

        Event::listen(\App\Events\MyFavoritesAdd::class, function($event)
        {
            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName('MyFavorite');
            $entity_event->setEntityId($event->getEventId());
            $entity_event->setType('INSERT');
            $entity_event->setOwner($event->getMember());
            $entity_event->setSummit($event->getSummit());
            $entity_event->setMetadata('');

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();
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

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

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

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

        });

        Event::listen(\App\Events\SummitEventCreated::class, function($event)
        {
            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->setEntityClassName($event->getSummitEvent()->getClassName());
            $entity_event->setEntityId($event->getSummitEvent()->getId());
            $entity_event->setType('INSERT');

            if($owner_id > 0){
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($event->getSummitEvent()->getSummit());
            $entity_event->setMetadata( json_encode([ 'pub_new' => intval($event->getSummitEvent()->isPublished())]));

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

        });

        Event::listen(\App\Events\SummitEventUpdated::class, function($event)
        {
            if(!$event instanceof SummitEventUpdated) return;
            $args = $event->getArgs();
            if(!$args instanceof PreUpdateEventArgs) return;

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);

            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->setEntityClassName($event->getSummitEvent()->getClassName());
            $entity_event->setEntityId($event->getSummitEvent()->getId());
            $entity_event->setType('UPDATE');

            if($owner_id > 0){
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($event->getSummitEvent()->getSummit());

            if($args->hasChangedField('published')){
                $entity_event->setMetadata(json_encode([ 'pub_old'=> intval($args->getOldValue('published')),  'pub_new' => intval($args->getNewValue('published'))]));
            }
            else
                $entity_event->setMetadata(json_encode([ 'pub_new' => intval($event->getSummitEvent()->getPublished())]));

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();
        });

        Event::listen(\App\Events\SummitEventDeleted::class, function($event)
        {
            if(!$event instanceof SummitEventDeleted) return;
            $args = $event->getArgs();
            if(!$args instanceof PreRemoveEventArgs) return;

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\models\main\IMemberRepository::class);
            $owner_id                        = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;
            $params = $args->getParams();

            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName($params['class_name']);
            $entity_event->setEntityId($params['id']);
            $entity_event->setType('DELETE');

            if($owner_id > 0){
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($params['summit']);
            $entity_event->setMetadata('');

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();
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

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

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

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

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

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

        });

        Event::listen(\App\Events\FileCreated::class, function($event)
        {

            $storage_path      = storage_path();
            $local_path        = $event->getLocalPath();
            $file_name         = $event->getFileName();
            $folder_name       = $event->getFolderName();
            $remote_base_path  = Config::get('scp.scp_remote_base_path', null);
            $client            = new SftpClient();
            $host              = Config::get('scp.scp_host', null);

            $credentials       = Credentials::withPublicKey
            (
                Config::get('scp.ssh_user', null),
                Config::get('scp.ssh_public_key', null),
                Config::get('scp.ssh_private_key', null)
            );

            $client->setCredentials($credentials);
            $client->connect($host);
            $remote_destination = sprintf("%s/%s",$remote_base_path, $file_name);
            $client->scpUpload(sprintf("%s/app/%s", $storage_path, $local_path), $remote_destination);
            $client->close();

            $asset_sync_request = new AssetsSyncRequest();
            $asset_sync_request->setFrom($remote_destination);
            $asset_sync_request->setTo(sprintf("%s/%s", $folder_name, $file_name));
            $asset_sync_request->setProcessed(false);
            $em = Registry::getManager('ss');
            $em->persist($asset_sync_request);
            $em->flush();

        });

    }
}

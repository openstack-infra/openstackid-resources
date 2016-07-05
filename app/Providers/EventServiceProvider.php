<?php namespace App\Providers;

use App\Events\SummitEventCreated;
use App\Events\SummitEventDeleted;
use App\Events\SummitEventUpdated;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use LaravelDoctrine\ORM\Facades\Registry;
use models\summit\SummitEntityEvent;
use App\Events\MyScheduleAdd;
use App\Events\MyScheduleRemove;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use models\utils\PreRemoveEventArgs;

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
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
    ];

      /**
     * Register any other events for your application.
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
        /*


        SummitEvent::deleted(function($summit_event){

            if(!$summit_event instanceof SummitEvent) return;
            $resource_server_context       = App::make('models\\oauth2\\IResourceServerContext');
            $owner_id                      = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->EntityClassName = $summit_event->ClassName;
            $entity_event->EntityID        = $summit_event->ID;
            $entity_event->Type            = 'DELETE';
            $entity_event->OwnerID         = $owner_id;
            $entity_event->SummitID        = $summit_event->getSummit()->ID;
            $entity_event->Metadata        = '';
            $entity_event->save();
        });

        SummitEvent::updated(function($summit_event){

            if(!$summit_event instanceof SummitEvent) return;

            $resource_server_context       = App::make('models\\oauth2\\IResourceServerContext');
            $owner_id                      = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $original                      = $summit_event->getOriginal();
            $entity_event                  = new SummitEntityEvent;
            $entity_event->EntityClassName = $summit_event->ClassName;
            $entity_event->EntityID        = $summit_event->ID;
            $entity_event->Type            = 'UPDATE';
            $entity_event->OwnerID         = $owner_id;
            $entity_event->SummitID        = $summit_event->getSummit()->ID;
            if(intval($original['Published']) !== intval($summit_event->Published)){
                $entity_event->Metadata = json_encode( array ( 'pub_old'=> intval($original['Published']),  'pub_new' => intval($summit_event->Published)));
            }
            else
                $entity_event->Metadata = json_encode( array ( 'pub_new' => intval($summit_event->Published)));
            $entity_event->save();
        });

     */

        Event::listen(\App\Events\MyScheduleAdd::class, function($event)
        {
            if(!$event instanceof MyScheduleAdd) return;

            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName('MySchedule');
            $entity_event->setEntityId($event->getEventId());
            $entity_event->setType('INSERT');
            $entity_event->setOwner($event->getAttendee()->getMember());
            $entity_event->setSummit($event->getAttendee()->getSummit());
            $entity_event->setMetadata('');

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();
        });

        Event::listen(\App\Events\MyScheduleRemove::class, function($event)
        {
            if(!$event instanceof MyScheduleRemove) return;

            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName('MySchedule');
            $entity_event->setEntityId($event->getEventId());
            $entity_event->setType('DELETE');
            $entity_event->setOwner($event->getAttendee()->getMember());
            $entity_event->setSummit($event->getAttendee()->getSummit());
            $entity_event->setMetadata('');

            $em = Registry::getManager('ss');
            $em->persist($entity_event);
            $em->flush();

        });

        Event::listen(\App\Events\SummitEventCreated::class, function($event)
        {
            if(!$event instanceof SummitEventCreated) return;

            $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
            $member_repository               = App::make(\Models\foundation\main\repositories\IMemberRepository::class);
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
            $member_repository               = App::make(\Models\foundation\main\repositories\IMemberRepository::class);

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
            $member_repository               = App::make(\Models\foundation\main\repositories\IMemberRepository::class);
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
    }
}

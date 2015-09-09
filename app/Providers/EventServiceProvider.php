<?php namespace App\Providers;

use App\Events\MyScheduleAdd;
use App\Events\MyScheduleRemove;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use models\summit\SummitEntityEvent;
use models\summit\SummitEvent;


class EventServiceProvider extends ServiceProvider
{
     /**
     * The event handler mappings for the application.
     * @var array
     */
    protected $listen = [
    ];

    /**
     * Register any other events for your application.
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        Event::listen('App\Events\MyScheduleAdd', function($event)
        {
            if(!$event instanceof MyScheduleAdd) return;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->EntityClassName = 'MySchedule';
            $entity_event->EntityID        = $event->getEventId();
            $entity_event->Type            = 'INSERT';
            $entity_event->OwnerID         = $event->getAttendee()->member()->ID;
            $entity_event->SummitID        = $event->getAttendee()->getSummit()->ID;
            $entity_event->Metadata        = '';
            $entity_event->save();
        });

        Event::listen('App\Events\MyScheduleRemove', function($event)
        {
            if(!$event instanceof MyScheduleRemove) return;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->EntityClassName = 'MySchedule';
            $entity_event->EntityID        = $event->getEventId();
            $entity_event->Type            = 'DELETE';
            $entity_event->OwnerID         = $event->getAttendee()->member()->ID;
            $entity_event->SummitID        = $event->getAttendee()->getSummit()->ID;
            $entity_event->Metadata        = '';
            $entity_event->save();
        });

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

        SummitEvent::created(function($summit_event){

            if(!$summit_event instanceof SummitEvent) return;

            $resource_server_context       = App::make('models\\oauth2\\IResourceServerContext');
            $owner_id                      = $resource_server_context->getCurrentUserExternalId();
            if(is_null($owner_id)) $owner_id = 0;

            $entity_event                  = new SummitEntityEvent;
            $entity_event->EntityClassName = $summit_event->ClassName;
            $entity_event->EntityID        = $summit_event->ID;
            $entity_event->Type            = 'INSERT';
            $entity_event->OwnerID         = $owner_id;
            $entity_event->SummitID        = $summit_event->getSummit()->ID;
            $entity_event->Metadata = json_encode( array ( 'pub_new' => intval($summit_event->Published)));
            $entity_event->save();
        });
    }

}
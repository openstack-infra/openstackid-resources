<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//OAuth2 Protected API
Route::group(array(
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'api/v1',
    'before' => [],
    'after' => [],
    'middleware' => ['ssl', 'oauth2.protected', 'rate.limit','etags']
), function () {

    Route::group(array('prefix' => 'marketplace'), function () {

        Route::group(array('prefix' => 'public-clouds'), function () {
            Route::get('', 'OAuth2PublicCloudApiController@getClouds');
            Route::get('/{id}', 'OAuth2PublicCloudApiController@getCloud');
            Route::get('/{id}/data-centers', 'OAuth2PublicCloudApiController@getCloudDataCenters');
        });

        Route::group(array('prefix' => 'private-clouds'), function () {
            Route::get('', 'OAuth2PrivateCloudApiController@getClouds');
            Route::get('/{id}', 'OAuth2PrivateCloudApiController@getCloud');
            Route::get('/{id}/data-centers', 'OAuth2PrivateCloudApiController@getCloudDataCenters');
        });

        Route::group(array('prefix' => 'consultants'), function () {
            Route::get('', 'OAuth2ConsultantsApiController@getConsultants');
            Route::get('/{id}', 'OAuth2ConsultantsApiController@getConsultant');
            Route::get('/{id}/offices', 'OAuth2ConsultantsApiController@getOffices');
        });

    });

    // summits
    Route::group(array('prefix' => 'summits'), function () {

        Route::get('', 'OAuth2SummitApiController@getSummits');
        Route::group(array('prefix' => 'events'), function () {
            Route::get('', 'OAuth2SummitApiController@getAllEvents');
            Route::get('published', 'OAuth2SummitApiController@getAllEvents');
        });

        Route::group(array('prefix' => '{id}'), function () {

            Route::get('', [ 'middleware' => 'cache', 'uses' => 'OAuth2SummitApiController@getSummit'])->where('id', 'current|[0-9]+');

            Route::get('entity-events', 'OAuth2SummitApiController@getSummitEntityEvents');

            // attendees
            Route::group(array('prefix' => 'attendees'), function () {

                //Route::get('', 'OAuth2SummitAttendeesApiController@getAttendees');

                Route::group(array('prefix' => '{attendee_id}'), function () {

                    Route::get('', 'OAuth2SummitAttendeesApiController@getAttendee')->where('attendee_id', 'me|[0-9]+');

                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitAttendeesApiController@getAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitAttendeesApiController@addEventToAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::delete('', 'OAuth2SummitAttendeesApiController@removeEventFromAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::put('/check-in', 'OAuth2SummitAttendeesApiController@checkingAttendeeOnEvent')->where('attendee_id', 'me|[0-9]+');
                        });
                    });
                });
            });

            // speakers
            Route::group(array('prefix' => 'speakers'), function () {

                Route::get('', 'OAuth2SummitSpeakersApiController@getSpeakers');

                Route::group(array('prefix' => '{speaker_id}'), function () {
                    Route::get('', 'OAuth2SummitSpeakersApiController@getSpeaker')->where('speaker_id', 'me|[0-9]+');
                });
            });

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::get('', 'OAuth2SummitEventsApiController@getEvents');
                Route::get('/published', 'OAuth2SummitEventsApiController@getScheduledEvents');
                Route::post('', 'OAuth2SummitEventsApiController@addEvent');
                Route::group(array('prefix' => '{event_id}'), function () {

                    Route::get('', 'OAuth2SummitEventsApiController@getEvent');
                    Route::get('/published', 'OAuth2SummitEventsApiController@getScheduledEvent');
                    Route::put('', 'OAuth2SummitEventsApiController@updateEvent');
                    Route::delete('', 'OAuth2SummitEventsApiController@deleteEvent');
                    Route::put('/publish', 'OAuth2SummitEventsApiController@publishEvent');
                    Route::delete('/publish', 'OAuth2SummitEventsApiController@unPublishEvent');
                    Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedback');
                    Route::get('/feedback/{attendee_id?}', 'OAuth2SummitEventsApiController@getEventFeedback')->where('attendee_id', 'me|[0-9]+');
                });
            });

            // presentations
            Route::group(array('prefix' => 'presentations'), function () {
                Route::group(array('prefix' => '{presentation_id}'), function () {

                    Route::group(array('prefix' => 'videos'), function () {
                        Route::get('', 'OAuth2PresentationApiController@getPresentationVideos');
                        Route::get('{video_id}', 'OAuth2PresentationApiController@getPresentationVideo');
                        Route::post('', 'OAuth2PresentationApiController@addVideo');
                    });
                });
            });

            // locations
            Route::group(array('prefix' => 'locations'), function () {

                Route::get('', 'OAuth2SummitLocationsApiController@getLocations');

                Route::group(array('prefix' => '{location_id}'), function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getLocation');
                    Route::get('/events/published','OAuth2SummitLocationsApiController@getLocationEvents');
                    Route::get('/events','OAuth2SummitLocationsApiController@getLocationPublishedEvents');
                });
            });

            // event types
            Route::group(array('prefix' => 'event-types'), function () {
                Route::get('', 'OAuth2SummitApiController@getEventTypes');
            });

            // summit types
            Route::group(array('prefix' => 'summit-types'), function () {
                Route::get('', 'OAuth2SummitApiController@getSummitTypes');
            });

            // summit types
            Route::group(array('prefix' => 'external-orders'), function () {
                Route::get('{external_order_id}', 'OAuth2SummitApiController@getExternalOrder');
                Route::post('{external_order_id}/external-attendees/{external_attendee_id}/confirm', 'OAuth2SummitApiController@confirmExternalOrderAttendee');
            });

            // member
            Route::group(array('prefix' => 'members'), function () {
                Route::group(array('prefix' => 'me'), function () {
                    Route::get('', 'OAuth2SummitMembersApiController@getMyMember');
                });

            });

        });
    });
});

//OAuth2 Protected API V2
Route::group(array(
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'api/v2',
    'before' => [],
    'after' => [],
    'middleware' => ['ssl', 'oauth2.protected', 'rate.limit','etags']
), function () {

    // summits
    Route::group(array('prefix' => 'summits'), function () {

        Route::group(array('prefix' => '{id}'), function () {

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::group(array('prefix' => '{event_id}'), function () {
                   Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedbackByMember');
                });
            });

        });
    });
});
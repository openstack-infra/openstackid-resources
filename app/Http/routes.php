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

            Route::get('entity-events', 'OAuth2SummitApiController@getAllScheduledEvents');
            // attendees
            Route::group(array('prefix' => 'attendees'), function () {

                //Route::get('', 'OAuth2SummitApiController@getAttendees');

                Route::group(array('prefix' => '{attendee_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getAttendee')->where('attendee_id', 'me|[0-9]+');

                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitApiController@getAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitApiController@addEventToAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::delete('', 'OAuth2SummitApiController@removeEventFromAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::put('/check-in', 'OAuth2SummitApiController@checkingAttendeeOnEvent')->where('attendee_id', 'me|[0-9]+');
                        });
                    });
                });
            });

            // speakers
            Route::group(array('prefix' => 'speakers'), function () {

                Route::get('', 'OAuth2SummitApiController@getSpeakers');

                Route::group(array('prefix' => '{speaker_id}'), function () {
                    Route::get('', 'OAuth2SummitApiController@getSpeaker')->where('speaker_id', 'me|[0-9]+');
                });
            });

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::get('', 'OAuth2SummitApiController@getEvents');
                Route::get('/published', 'OAuth2SummitApiController@getScheduledEvents');
                Route::post('', 'OAuth2SummitApiController@addEvent');
                Route::group(array('prefix' => '{event_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getEvent');
                    Route::get('/published', 'OAuth2SummitApiController@getScheduledEvent');
                    Route::put('', 'OAuth2SummitApiController@updateEvent');
                    Route::delete('', 'OAuth2SummitApiController@deleteEvent');
                    Route::put('/publish', 'OAuth2SummitApiController@publishEvent');
                    Route::delete('/publish', 'OAuth2SummitApiController@unPublishEvent');
                    Route::post('/feedback', 'OAuth2SummitApiController@addEventFeedback');
                    Route::get('/feedback/{attendee_id?}', 'OAuth2SummitApiController@getEventFeedback')->where('attendee_id', 'me|[0-9]+');
                });
            });

            // locations
            Route::group(array('prefix' => 'locations'), function () {

                Route::get('', 'OAuth2SummitApiController@getLocations');

                Route::group(array('prefix' => '{location_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getLocation');
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

        });
    });
});
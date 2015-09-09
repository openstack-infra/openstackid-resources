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
    'before' => ['ssl', 'oauth2.enabled'],
    'after' => '',
    'middleware' => ['oauth2.protected', 'rate.limit','etags', 'cache']
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
        Route::group(array('prefix' => '{id}'), function () {
            Route::get('', 'OAuth2SummitApiController@getSummit');

            Route::get('entity-events', 'OAuth2SummitApiController@getSummitEntityEvents');
            // attendees
            Route::group(array('prefix' => 'attendees'), function () {

                Route::get('', 'OAuth2SummitApiController@getAttendees');

                Route::group(array('prefix' => '{attendee_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getAttendee');

                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitApiController@getAttendeeSchedule');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitApiController@addEventToAttendeeSchedule');
                            Route::delete('', 'OAuth2SummitApiController@removeEventToAttendeeSchedule');
                            Route::put('/check-in', 'OAuth2SummitApiController@checkingAttendeeOnEvent');
                        });
                    });
                });
            });

            // speakers
            Route::group(array('prefix' => 'speakers'), function () {

                Route::get('', 'OAuth2SummitApiController@getSpeakers');

                Route::group(array('prefix' => '{speaker_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getSpeaker');
                    Route::post('/presentations/{presentation_id}/feedback', 'OAuth2SummitApiController@addSpeakerFeedback');
                });
            });

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::get('', 'OAuth2SummitApiController@getEvents');

                Route::group(array('prefix' => '{event_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getEvent');
                    Route::post('/feedback', 'OAuth2SummitApiController@addEventFeedback');
                    Route::get('/feedback', 'OAuth2SummitApiController@getEventFeedback');
                });
            });

            // locations
            Route::group(array('prefix' => 'locations'), function () {

                Route::get('', 'OAuth2SummitApiController@getLocations');

                Route::group(array('prefix' => '{location_id}'), function () {

                    Route::get('', 'OAuth2SummitApiController@getLocation');
                });
            });
        });
    });
});
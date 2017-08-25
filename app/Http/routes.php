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

use Illuminate\Support\Facades\Config;

// public api ( without AUTHZ [OAUTH2.0])
Route::group([
    'namespace' => 'App\Http\Controllers',
    'prefix'     => 'api/public/v1',
    'before'     => [],
    'after'      => [],
    'middleware' => [
        'ssl',
        'rate.limit:100,1', // 100 request per minute
        'etags'
    ]
], function(){
    // members
    Route::group(['prefix'=>'members'], function() {
        Route::get('', 'OAuth2MembersApiController@getMembers');
    });

    // summits
    Route::group(['prefix'=>'summits'], function() {
        Route::get('','OAuth2SummitApiController@getSummits');
        // locations
        Route::group(array('prefix' => 'locations'), function () {
            Route::group(array('prefix' => '{location_id}'), function () {
                Route::get('/events/published','OAuth2SummitLocationsApiController@getLocationEvents');
            });
        });
    });

    // marketplace
    Route::group(array('prefix' => 'marketplace'), function () {

        Route::group(array('prefix' => 'appliances'), function () {
            Route::get('', 'AppliancesApiController@getAll');
        });

        Route::group(array('prefix' => 'distros'), function () {
            Route::get('', 'DistributionsApiController@getAll');
        });

        Route::group(array('prefix' => 'consultants'), function () {
            Route::get('', 'ConsultantsApiController@getAll');
        });

        Route::group(array('prefix' => 'hosted-private-clouds'), function () {
            Route::get('', 'PrivateCloudsApiController@getAll');
        });

        Route::group(array('prefix' => 'remotely-managed-private-clouds'), function () {
            Route::get('', 'RemoteCloudsApiController@getAll');
        });

        Route::group(array('prefix' => 'public-clouds'), function () {
            Route::get('', 'PublicCloudsApiController@getAll');
        });
    });
});

//OAuth2 Protected API
Route::group([
    'namespace' => 'App\Http\Controllers',
    'prefix'     => 'api/v1',
    'before'     => [],
    'after'      => [],
    'middleware' => ['ssl', 'oauth2.protected', 'rate.limit','etags']
], function () {

    // members
    Route::group(['prefix'=>'members'], function(){
        Route::get('', 'OAuth2MembersApiController@getMembers');

        Route::group(['prefix'=>'me'], function(){

            // invitations
            Route::group(['prefix'=>'team-invitations'], function(){
                Route::get('', 'OAuth2TeamInvitationsApiController@getMyInvitations');
                Route::get('pending', 'OAuth2TeamInvitationsApiController@getMyPendingInvitations');
                Route::get('accepted', 'OAuth2TeamInvitationsApiController@getMyAcceptedInvitations');
                Route::group(['prefix'=>'{invitation_id}'], function() {
                    Route::put('', 'OAuth2TeamInvitationsApiController@acceptInvitation');
                    Route::delete('', 'OAuth2TeamInvitationsApiController@declineInvitation');
                });
            });
        });
    });

    // teams
    Route::group(['prefix'=>'teams'], function(){
        Route::get('', 'OAuth2TeamsApiController@getMyTeams');
        Route::post('', 'OAuth2TeamsApiController@addTeam');

        Route::group(['prefix' => '{team_id}'], function () {
            Route::get('', 'OAuth2TeamsApiController@getMyTeam');
            Route::put('', 'OAuth2TeamsApiController@updateTeam');
            Route::delete('', 'OAuth2TeamsApiController@deleteTeam');

            Route::group(array('prefix' => 'messages'), function () {
                Route::get('', 'OAuth2TeamsApiController@getMyTeamMessages');
                Route::post('', 'OAuth2TeamsApiController@postTeamMessage');
            });

            Route::group(array('prefix' => 'members'), function () {
                Route::group(['prefix' => '{member_id}'], function () {
                    Route::post('', 'OAuth2TeamsApiController@addMember2MyTeam');
                    Route::delete('', 'OAuth2TeamsApiController@removedMemberFromMyTeam');
                });
            });
        });
    });

    // summits
    Route::group(array('prefix' => 'summits'), function () {

        Route::get('',  [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summits_response_lifetime', 600), 'uses' => 'OAuth2SummitApiController@getSummits']);

        Route::group(array('prefix' => '{id}'), function () {

            Route::get('', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summit_response_lifetime', 300), 'uses' => 'OAuth2SummitApiController@getSummit'])->where('id', 'current|[0-9]+');

            Route::get('entity-events', 'OAuth2SummitApiController@getSummitEntityEvents');

            // attendees
            Route::group(array('prefix' => 'attendees'), function () {

                //Route::get('', 'OAuth2SummitAttendeesApiController@getAttendees');

                Route::group(array('prefix' => '{attendee_id}'), function () {

                    Route::get('', 'OAuth2SummitAttendeesApiController@getAttendee')->where('attendee_id', 'me');

                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitAttendeesApiController@getAttendeeSchedule')->where('attendee_id', 'me');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitAttendeesApiController@addEventToAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::delete('', 'OAuth2SummitAttendeesApiController@removeEventFromAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::delete('/rsvp', 'OAuth2SummitAttendeesApiController@deleteEventRSVP')->where('attendee_id', 'me|[0-9]+');
                            Route::put('/check-in', 'OAuth2SummitAttendeesApiController@checkingAttendeeOnEvent')->where('attendee_id', 'me|[0-9]+');
                        });
                    });
                });
            });

            // notifications
            Route::group(array('prefix' => 'notifications'), function () {
                Route::get('', 'OAuth2SummitNotificationsApiController@getAll');
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
                Route::post('', [ 'middleware' => 'auth.user:administrators', 'uses' => 'OAuth2SummitEventsApiController@addEvent']);
                Route::group(array('prefix' => '{event_id}'), function () {

                    Route::get('', 'OAuth2SummitEventsApiController@getEvent');
                    Route::get('/published', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_published_event_response_lifetime', 300), 'uses' => 'OAuth2SummitEventsApiController@getScheduledEvent']);
                    Route::put('', [ 'middleware' => 'auth.user:administrators', 'uses' => 'OAuth2SummitEventsApiController@updateEvent' ]);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators', 'uses' => 'OAuth2SummitEventsApiController@deleteEvent' ]);
                    Route::put('/publish', [ 'middleware' => 'auth.user:administrators', 'uses' => 'OAuth2SummitEventsApiController@publishEvent']);
                    Route::delete('/publish', [ 'middleware' => 'auth.user:administrators', 'uses' => 'OAuth2SummitEventsApiController@unPublishEvent']);
                    Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedback');
                    Route::get('/feedback/{attendee_id?}',  ['middleware' => 'cache:'.Config::get('cache_api_response.get_event_feedback_response_lifetime', 300), 'uses' => 'OAuth2SummitEventsApiController@getEventFeedback'] )->where('attendee_id', 'me|[0-9]+');
                });
            });

            // presentations
            Route::group(array('prefix' => 'presentations'), function () {
                Route::group(array('prefix' => '{presentation_id}'), function () {

                    Route::group(array('prefix' => 'videos'), function () {
                        Route::get('', 'OAuth2PresentationApiController@getPresentationVideos');
                        Route::get('{video_id}', 'OAuth2PresentationApiController@getPresentationVideo');
                        Route::post('', [ 'middleware' => 'auth.user:administrators|video-admins', 'uses' => 'OAuth2PresentationApiController@addVideo' ]);
                        Route::group(array('prefix' => '{video_id}'), function () {
                            Route::put('', [ 'middleware' => 'auth.user:administrators|video-admins', 'uses' => 'OAuth2PresentationApiController@updateVideo' ]);
                            Route::delete('', [ 'middleware' => 'auth.user:administrators|video-admins', 'uses' => 'OAuth2PresentationApiController@deleteVideo' ]);
                        });
                    });
                });
            });

            // locations
            Route::group(array('prefix' => 'locations'), function () {

                Route::get('', 'OAuth2SummitLocationsApiController@getLocations');
                Route::get('/venues', 'OAuth2SummitLocationsApiController@getVenues');
                Route::get('/external-locations', 'OAuth2SummitLocationsApiController@getExternalLocations');
                Route::get('/hotels', 'OAuth2SummitLocationsApiController@getHotels');
                Route::get('/airports', 'OAuth2SummitLocationsApiController@getAirports');
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

            // external orders
            Route::group(array('prefix' => 'external-orders'), function () {
                Route::get('{external_order_id}', 'OAuth2SummitApiController@getExternalOrder');
                Route::post('{external_order_id}/external-attendees/{external_attendee_id}/confirm', 'OAuth2SummitApiController@confirmExternalOrderAttendee');
            });

            // member
            Route::group(array('prefix' => 'members'), function () {
                Route::group(array('prefix' => '{member_id}'), function () {
                    Route::get('', 'OAuth2SummitMembersApiController@getMyMember')->where('member_id', 'me');
                    // favorites
                    Route::group(array('prefix' => 'favorites'), function ()
                    {
                        Route::get('', 'OAuth2SummitMembersApiController@getMemberFavoritesSummitEvents')->where('member_id', 'me');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitMembersApiController@addEventToMemberFavorites')->where('member_id', 'me');
                            Route::delete('', 'OAuth2SummitMembersApiController@removeEventFromMemberFavorites')->where('member_id', 'me');
                        });
                    });

                    // schedule
                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitMembersApiController@getMemberScheduleSummitEvents')->where('member_id', 'me');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::delete('/rsvp', 'OAuth2SummitMembersApiController@deleteEventRSVP')->where('member_id', 'me');
                            Route::post('', 'OAuth2SummitMembersApiController@addEventToMemberSchedule')->where('member_id', 'me');
                            Route::delete('', 'OAuth2SummitMembersApiController@removeEventFromMemberSchedule')->where('member_id', 'me');
                        });
                    });
                });

            });

            // tracks
            Route::group(array('prefix' => 'tracks'), function () {
                Route::get('', 'OAuth2SummitApiController@getTracks');
                Route::get('{track_id}', 'OAuth2SummitApiController@getTrack');
            });
            // track groups
            Route::group(array('prefix' => 'track-groups'), function () {
                Route::get('', 'OAuth2SummitApiController@getTracksGroups');
                Route::get('{track_group_id}', 'OAuth2SummitApiController@getTrackGroup');
            });

        });
    });
});

//OAuth2 Protected API V2
Route::group([
    'namespace'  => 'App\Http\Controllers',
    'prefix'     => 'api/v2',
    'before'     => [],
    'after'      => [],
    'middleware' => ['ssl', 'oauth2.protected', 'rate.limit','etags']
], function () {

    // summits
    Route::group(array('prefix' => 'summits'), function () {

        Route::group(array('prefix' => '{id}'), function () {

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::group(array('prefix' => '{event_id}'), function () {
                   Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedbackByMember');
                   Route::put('/feedback', 'OAuth2SummitEventsApiController@updateEventFeedbackByMember');
                });
            });

        });
    });
});
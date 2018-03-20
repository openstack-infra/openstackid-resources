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
        'rate.limit:1000,1', // 1000 request per minute
        'etags'
    ]
], function(){
    // members
    Route::group(['prefix'=>'members'], function() {
        Route::get('', 'OAuth2MembersApiController@getAll');
    });

    // summits
    Route::group(['prefix'=>'summits'], function() {
        Route::get('', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summit_response_lifetime', 600), 'uses' => 'OAuth2SummitApiController@getSummits']);
        Route::group(['prefix' => '{id}'], function () {
            // locations
            Route::group(['prefix' => 'locations'], function () {
                Route::group(['prefix' => '{location_id}'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getLocation');
                    Route::get('/events/published','OAuth2SummitLocationsApiController@getLocationPublishedEvents');
                });
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
        Route::get('', 'OAuth2MembersApiController@getAll');

        Route::group(['prefix'=>'me'], function(){
            // get my member info
            Route::get('', 'OAuth2MembersApiController@getMyMember');
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

        Route::group(['prefix'=>'{member_id}'], function(){

            Route::group(['prefix' => 'affiliations'], function(){
                Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2MembersApiController@getMemberAffiliations']);
                Route::group(['prefix' => '{affiliation_id}'], function(){
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2MembersApiController@updateAffiliation']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2MembersApiController@deleteAffiliation']);
                });
            });

            Route::group(array('prefix' => 'rsvp'), function () {
                Route::group(['prefix' => '{rsvp_id}'], function () {
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2MembersApiController@deleteRSVP']);
                });
            });
        });
    });

    // tags
    Route::group(['prefix'=>'tags'], function(){
        Route::get('', 'OAuth2TagsApiController@getAll');
    });

    // companies
    Route::group(['prefix'=>'companies'], function(){
        Route::get('', 'OAuth2CompaniesApiController@getAll');
    });

    // groups
    Route::group(['prefix'=>'groups'], function(){
        Route::get('', 'OAuth2GroupsApiController@getAll');
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
        Route::get('all', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitApiController@getAllSummits']);
        Route::group(['prefix' => '{id}'], function () {

            // rsvp templates
            Route::group(['prefix' => 'rsvp-templates'], function () {
                Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getAllBySummit']);
                Route::group(['prefix' => '{template_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getRSVPTemplate']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@deleteRSVPTemplate']);
                    Route::group(['prefix' => 'questions'], function () {
                        Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@addRSVPTemplateQuestion']);
                        Route::group(['prefix' => '{question_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@updateRSVPTemplateQuestion']);
                            Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getRSVPTemplateQuestion']);
                        });
                    });
                });
            });

            Route::get('', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summit_response_lifetime', 1200), 'uses' => 'OAuth2SummitApiController@getSummit'])->where('id', 'current|[0-9]+');

            Route::get('entity-events', 'OAuth2SummitApiController@getSummitEntityEvents');

            // attendees
            Route::group(array('prefix' => 'attendees'), function () {

                Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@getAttendeesBySummit']);
                Route::get('me', 'OAuth2SummitAttendeesApiController@getOwnAttendee');
                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@addAttendee']);
                Route::group(array('prefix' => '{attendee_id}'), function () {

                    Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@getAttendee']);
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@updateAttendee']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@deleteAttendee']);
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
                    Route::group(array('prefix' => 'tickets'), function ()
                    {
                        Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@addAttendeeTicket']);
                        Route::delete('{ticket_id}', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitAttendeesApiController@deleteAttendeeTicket']);
                    });
                });
            });

            // notifications
            Route::group(array('prefix' => 'notifications'), function () {
                Route::get('', 'OAuth2SummitNotificationsApiController@getAll');
            });

            // speakers
            Route::group(['prefix' => 'speakers'], function () {

                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersApiController@addSpeakerBySummit']);
                Route::get('', 'OAuth2SummitSpeakersApiController@getSpeakers');

                Route::group(['prefix' => '{speaker_id}'], function () {
                    Route::get('', 'OAuth2SummitSpeakersApiController@getSummitSpeaker')->where('speaker_id', 'me|[0-9]+');
                    Route::put('',[ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersApiController@updateSpeakerBySummit'])->where('speaker_id', 'me|[0-9]+');
                });
            });

            // speakers assistance
            Route::group(['prefix' => 'speakers-assistances'], function () {
                Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@getBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@getBySummitCSV']);
                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@addSpeakerSummitAssistance']);
                Route::group(['prefix' => '{assistance_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@getSpeakerSummitAssistanceBySummit']);
                    Route::delete('',[ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@deleteSpeakerSummitAssistance']);
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@updateSpeakerSummitAssistance']);
                    Route::post('mail', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@sendSpeakerSummitAssistanceAnnouncementMail']);
                });
            });

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::get('',[ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@getEvents']);
                Route::get('csv',[ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@getEventsCSV']);
                // bulk actions
                Route::delete('/publish', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@unPublishEvents']);
                Route::put('/publish', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@updateAndPublishEvents']);
                Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@updateEvents']);

                Route::group(array('prefix' => 'unpublished'), function () {
                    Route::get('', 'OAuth2SummitEventsApiController@getUnpublishedEvents');
                    //Route::get('{event_id}', 'OAuth2SummitEventsApiController@getUnpublisedEvent');
                });
                Route::group(array('prefix' => 'published'), function () {
                    Route::get('', 'OAuth2SummitEventsApiController@getScheduledEvents');
                    Route::get('/empty-spots', 'OAuth2SummitEventsApiController@getScheduleEmptySpots');
                });

                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@addEvent']);
                Route::group(array('prefix' => '{event_id}'), function () {

                    Route::get('', 'OAuth2SummitEventsApiController@getEvent');
                    Route::get('/published', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_published_event_response_lifetime', 300), 'uses' => 'OAuth2SummitEventsApiController@getScheduledEvent']);
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@updateEvent' ]);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@deleteEvent' ]);
                    Route::put('/publish', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@publishEvent']);
                    Route::delete('/publish', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@unPublishEvent']);
                    Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedback');
                    Route::post('/attachment', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitEventsApiController@addEventAttachment']);
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
            Route::group(['prefix' => 'locations'], function () {

                Route::get('', 'OAuth2SummitLocationsApiController@getLocations');
                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addLocation']);

                Route::get('metadata', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@getMetadata']);
                Route::group(['prefix' => 'venues'], function () {

                    Route::get('', 'OAuth2SummitLocationsApiController@getVenues');
                    Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addVenue']);
                    Route::group(['prefix' => '{venue_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateVenue']);

                        Route::group(['prefix' => 'rooms'], function () {
                            Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addVenueRoom']);
                            Route::group(['prefix' => '{room_id}'], function () {
                                Route::get('', 'OAuth2SummitLocationsApiController@getVenueRoom');
                                Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueRoom']);
                                Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@deleteVenueRoom']);
                            });
                        });

                        Route::group(['prefix' => 'floors'], function () {
                            Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addVenueFloor']);
                            Route::group(['prefix' => '{floor_id}'], function () {
                                Route::get('', 'OAuth2SummitLocationsApiController@getVenueFloor');
                                Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueFloor']);
                                Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@deleteVenueFloor']);
                                Route::group(['prefix' => 'rooms'], function () {
                                    Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addVenueFloorRoom']);
                                    Route::group(['prefix' => '{room_id}'], function () {
                                        Route::get('', 'OAuth2SummitLocationsApiController@getVenueFloorRoom');
                                        Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueFloorRoom']);
                                    });
                                });
                            });
                        });
                    });
                });

                Route::group(['prefix' => 'airports'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getAirports');
                    Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addAirport']);
                    Route::group(['prefix' => '{airport_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateAirport']);
                    });
                });

                Route::group(['prefix' => 'hotels'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getHotels');
                    Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addExternalLocation']);
                    Route::group(['prefix' => '{hotel_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateExternalLocation']);
                    });
                });

                Route::group(['prefix' => 'external-locations'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getExternalLocations');
                    Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addHotel']);
                    Route::group(['prefix' => '{external_location_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateHotel']);
                    });
                });

                Route::group(['prefix' => '{location_id}'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getLocation');

                    // locations maps
                    Route::group(['prefix' => 'maps'], function () {
                        Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addLocationMap']);
                        Route::group(['prefix' => '{map_id}'], function () {
                            Route::get('', 'OAuth2SummitLocationsApiController@getLocationMap');
                            Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateLocationMap']);
                            Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocationMap']);
                        });
                    });

                    // locations images
                    Route::group(['prefix' => 'images'], function () {
                        Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addLocationImage']);
                        Route::group(['prefix' => '{image_id}'], function () {
                            Route::get('', 'OAuth2SummitLocationsApiController@getLocationImage');
                            Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateLocationImage']);
                            Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocationImage']);
                        });
                    });

                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateLocation']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocation']);
                    Route::get('/events/published','OAuth2SummitLocationsApiController@getLocationPublishedEvents')->where('location_id', 'tbd|[0-9]+');
                    Route::get('/events','OAuth2SummitLocationsApiController@getLocationEvents')->where('location_id', 'tbd|[0-9]+');
                    // location banners
                    Route::group(['prefix' => 'banners'], function () {
                        Route::get('', 'OAuth2SummitLocationsApiController@getLocationBanners');
                        Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@addLocationBanner']);
                        Route::group(['prefix' => '{banner_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@updateLocationBanner']);
                            Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocationBanner']);
                        });
                    });
                });
            });

            // event types
            Route::group(['prefix' => 'event-types'], function () {
                Route::get('', 'OAuth2SummitsEventTypesApiController@getAllBySummit');
                Route::get('csv', 'OAuth2SummitsEventTypesApiController@getAllBySummitCSV');
                Route::post('seed-defaults', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitsEventTypesApiController@seedDefaultEventTypesBySummit']);
                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitsEventTypesApiController@addEventTypeBySummit']);
                Route::group(['prefix' => '{event_type_id}'], function () {
                    Route::get('', 'OAuth2SummitsEventTypesApiController@getEventTypeBySummit');
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitsEventTypesApiController@updateEventTypeBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitsEventTypesApiController@deleteEventTypeBySummit']);
                });
            });

            // external orders
            Route::group(['prefix' => 'external-orders'], function () {
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
            Route::group(['prefix' => 'tracks'], function () {
                Route::get('', 'OAuth2SummitTracksApiController@getAllBySummit');
                Route::get('csv', 'OAuth2SummitTracksApiController@getAllBySummitCSV');
                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitTracksApiController@addTrackBySummit']);
                Route::group(['prefix' => '{track_id}'], function () {
                    Route::get('', 'OAuth2SummitTracksApiController@getTrackBySummit');
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitTracksApiController@updateTrackBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitTracksApiController@deleteTrackBySummit']);
                });
            });

            // track groups
            Route::group(['prefix' => 'track-groups'], function () {
                Route::get('', 'OAuth2SummitTracksApiController@getTracksGroups');
                Route::get('{track_group_id}', 'OAuth2SummitApiController@getTrackGroup');
            });

            // promo codes
            Route::group(['prefix' => 'promo-codes'], function () {
                Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@getAllBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@getAllBySummitCSV']);
                Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@addPromoCodeBySummit']);
                Route::get('metadata', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@getMetadata']);
                Route::group(['prefix' => '{promo_code_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@getPromoCodeBySummit']);
                    Route::put('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@updatePromoCodeBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@deletePromoCodeBySummit']);
                    Route::post('mail', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitPromoCodesApiController@sendPromoCodeMail']);
                });
            });

        });
    });

    // speakers
    Route::group(['prefix' => 'speakers'], function () {
        Route::get('', 'OAuth2SummitSpeakersApiController@getAll');
        Route::post('', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersApiController@addSpeaker']);
        Route::put('merge/{speaker_from_id}/{speaker_to_id}', 'OAuth2SummitSpeakersApiController@merge');
        Route::group(['prefix' => '{speaker_id}'], function () {
            Route::put('',[ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersApiController@updateSpeaker'])->where('speaker_id', 'me|[0-9]+');
            Route::delete('',[ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersApiController@deleteSpeaker'])->where('speaker_id', 'me|[0-9]+');
            Route::get('', 'OAuth2SummitSpeakersApiController@getSpeaker');
            Route::post('/photo', [ 'middleware' => 'auth.user:administrators|summit-front-end-administrators', 'uses' => 'OAuth2SummitSpeakersApiController@addSpeakerPhoto']);
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
    Route::group(['prefix' => 'summits'], function () {

        Route::group(['prefix' => '{id}'], function () {

            // events
            Route::group(['prefix' => 'events'], function () {

                Route::group(['prefix' => '{event_id}'], function () {
                   Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedbackByMember');
                   Route::put('/feedback', 'OAuth2SummitEventsApiController@updateEventFeedbackByMember');
                });
            });

        });
    });
});
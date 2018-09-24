<?php
/**
 * Copyright 2018 OpenStack Foundation
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

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
            Route::get('', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summit_response_lifetime', 1200), 'uses' => 'OAuth2SummitApiController@getSummit'])->where('id', 'current|[0-9]+');
            // locations
            Route::group(['prefix' => 'locations'], function () {
                Route::group(['prefix' => '{location_id}'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getLocation');
                    Route::get('/events/published','OAuth2SummitLocationsApiController@getLocationPublishedEvents');
                    Route::group(['prefix' => 'banners'], function () {
                        Route::get('', 'OAuth2SummitLocationsApiController@getLocationBanners');
                    });
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

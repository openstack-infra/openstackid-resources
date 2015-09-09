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
    'middleware' => ['oauth2.protected', 'rate.limit', 'etags']
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

    Route::group(array('prefix' => 'summits'), function () {

        Route::get('/{id}', 'OAuth2SummitApiController@getSummit');

    });
});
<?php

use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::group(['namespace' => '\Laravel\Passport\Http\Controllers'], function () {
        Route::post('login', [
            'as' => 'oauth.login',
            'middleware' => ['throttle'],
            'uses' => 'AccessTokenController@issueToken'
        ]);
    });
    Route::post('register', [
        'as' => 'auth.register',
        'uses' => 'AuthController@register'
    ]);
});

Route::group(['middleware' => ['auth:api']], function () {

    Route::post('logout', [
        'as' => 'auth.logout',
        'uses' => 'UserController@logout',
    ]);

    Route::group(['prefix' => '/user'], function () {
        Route::get('/all/{id?}', [
            'as' => 'user.index',
            'uses' => 'UserController@index'
        ]);

        Route::get('/me', [
            'as' => 'user.me',
            'uses' => 'UserController@me'
        ]);

        Route::get('/{id}', [
            'as' => 'user.view',
            'uses' => 'UserController@view'
        ]);

        Route::post('/create', [
            'as' => 'user.create',
            'uses' => 'UserController@create',
        ]);

        Route::match(['post', 'put'], '/update/{id?}', [
            'as' => 'user.update',
            'uses' => 'UserController@update',
        ]);

        Route::delete('/delete/{id}', [
            'as' => 'user.delete',
            'uses' => 'UserController@delete',
        ]);

        Route::delete('/unregister', [
            'as' => 'user.unregister',
            'uses' => 'UserController@unregister',
        ]);

        Route::post('/register', [
            'as' => 'user.register',
            'uses' => 'UserController@register',
        ]);
    });
});

Route::group(['middleware' => ['auth:api'], 'prefix' => '/product'], function () {
    Route::get('/all', [
        'as' => 'product.index',
        'uses' => 'ProductController@index'
    ]);

    Route::get('/{id}', [
        'as' => 'product.view',
        'uses' => 'ProductController@view'
    ]);

    Route::post('/create', [
        'as' => 'product.create',
        'uses' => 'ProductController@create'
    ]);

    Route::match(['post', 'put'], '/update/{id}', [
        'as' => 'product.update',
        'uses' => 'ProductController@update'
    ]);

    Route::delete('/delete/{id}', [
        'as' => 'product.delete',
        'uses' => 'ProductController@delete'
    ]);

    Route::post('/restore/{id}', [
        'as' => 'product.restore',
        'uses' => 'ProductController@restore'
    ]);

    Route::delete('/destroy/{id}', [
        'as' => 'product.destroy',
        'uses' => 'ProductController@destroy'
    ]);
});

Route::group(['middleware' => ['auth:api'], 'prefix' => '/order'], function () {
    Route::get('/all/{id?}', [
        'as' => 'order.index',
        'uses' => 'OrderController@index',
    ]);

    Route::get('/{id}', [
        'as' => 'order.view',
        'uses' => 'OrderController@view',
    ]);

    Route::post('/create', [
        'as' => 'order.create',
        'uses' => 'OrderController@create'
    ]);

    Route::delete('/delete/{id}', [
        'as' => 'order.delete',
        'uses' => 'OrderController@delete'
    ]);

    Route::post('/restore/{id}', [
        'as' => 'order.restore',
        'uses' => 'OrderController@restore'
    ]);
});

Route::group(['middleware' => ['auth:api'], 'prefix' => '/transport'], function () {
    Route::get('/all/{id?}', [
        'as' => 'transportation.all',
        'uses' => 'TransportationController@index'
    ]);

    Route::get('/{id}', [
        'as' => 'transportation.index',
        'uses' => 'TransportationController@view'
    ]);

    Route::post('/create', [
        'as' => 'transportation.create',
        'uses' => 'TransportationController@create'
    ]);

    Route::match(['post', 'put'], '/update/{id}', [
        'as' => 'transport.update',
        'uses' => 'TransportationController@update',
    ]);

    Route::delete('/delete/{id}', [
        'as' => 'transportation.delete',
        'uses' => 'TransportationController@delete'
    ]);

    Route::post('/restore/{id}', [
        'as' => 'transport.restore',
        'uses' => 'TransportController@restore'
    ]);

    Route::delete('/destroy/{id}', [
        'as' => 'transport.destroy',
        'uses' => 'TransportController@destroy'
    ]);
});

Route::group(['middleware' => ['auth:api'], 'prefix' => '/payment'], function () {

    Route::post('/create', [
        'as' => 'payment.create',
        'uses' => 'PaymentController@create'
    ]);

    Route::get('/all/{id?}', [
        'as' => 'payment.index',
        'uses' => 'PaymentController@index'
    ]);

    Route::get('/financial', [
        'as' => 'payment.financial',
        'uses' => 'PaymentController@financial'
    ]);

    Route::get('/{id}', [
        'as' => 'payment.view',
        'uses' => 'PaymentController@view'
    ]);

    Route::match(['post', 'put'], '/update/{ref_id}', [
        'as' => 'payment.update',
        'uses' => 'PaymentController@update'
    ]);

    Route::delete('/delete/{id}', [
        'as' => 'payment.delete',
        'uses' => 'PaymentController@delete'
    ]);

    Route::post('/restore/{id}', [
        'as' => 'payment.restore',
        'uses' => 'PaymentController@restore'
    ]);

    Route::delete('/destroy/{id}', [
        'as' => 'payment.destroy',
        'uses' => 'PaymentController@destroy'
    ]);
});

Route::group(['middleware' => [], 'prefix' => '/ticket'], function () {

    Route::post('/create', [
        'as' => 'ticket.create',
        'uses' => 'TicketController@create'
    ]);

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('/all/{id?}', [
            'as' => 'ticket.index',
            'uses' => 'TicketController@index'
        ]);

        Route::get('/{id}', [
            'as' => 'ticket.view',
            'uses' => 'TicketController@view'
        ]);

        Route::match(['post', 'put'], '/update/{id}', [
            'as' => 'ticket.update',
            'uses' => 'TicketController@update'
        ]);


        Route::delete('/delete/{id}', [
            'as' => 'ticket.delete',
            'uses' => 'TicketController@delete'
        ]);

        Route::post('/restore/{id}', [
            'as' => 'ticket.restore',
            'uses' => 'TicketController@restore'
        ]);

        Route::delete('/destroy/{id}', [
            'as' => 'ticket.destroy',
            'uses' => 'TicketController@destroy'
        ]);
    });

});

Route::group(['middleware' => ['auth:api'], 'prefix' => '/event'], function () {
    Route::get('/all/{id?}', [
        'as' => 'event.index',
        'uses' => 'EventController@index'
    ]);

    Route::get('/{id}', [
        'as' => 'event.view',
        'uses' => 'EventController@view'
    ]);

    Route::post('/create', [
        'as' => 'event.create',
        'uses' => 'EventController@create'
    ]);

    Route::match(['post', 'put'], '/update/{id}', [
        'as' => 'event.update',
        'uses' => 'EventController@update'
    ]);

    Route::delete('/delete/{id}', [
        'as' => 'event.delete',
        'uses' => 'EventController@delete'
    ]);

    Route::post('/restore/{id}', [
        'as' => 'event.restore',
        'uses' => 'EventController@restore'
    ]);

    Route::delete('/destroy/{id}', [
        'as' => 'event.destroy',
        'uses' => 'EventController@destroy'
    ]);
});

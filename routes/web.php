<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    // operator 
    $router->get('shift',  ['uses' => 'OperatorController@showAllShift']);
    $router->post('auth',  ['uses' => 'OperatorController@doLogin']);
    $router->post('operator/laporan',  ['uses' => 'OperatorController@laporan']);

    // menu parkir
    $router->get('parkir',  ['uses' => 'ParkirController@index']);

    // masuk parkir 
    $router->get('parkir/in/{kategori}',  ['uses' => 'ParkirController@parkirIn']);
    $router->post('member/in',  ['uses' => 'ParkirController@memberIn']);

    // keluar parkir
    $router->post('parkir/out',  ['uses' => 'ParkirController@parkirOut']);
    $router->post('member/out',  ['uses' => 'ParkirController@memberOut']);

    //member
    $router->post('member',  ['uses' => 'MemberController@index']);
    $router->post('member/topup',  ['uses' => 'MemberController@memberTopup']);

    // set expired data for cron_job *WARNING*
    $router->get('parkir/expired',  ['uses' => 'ParkirController@setExpiredPakir']);
    $router->get('parkir/expired/delete',  ['uses' => 'ParkirController@deleteExpiredPakir']);
    $router->get('member/validasi',  ['uses' => 'MemberController@ValidasiMember']);

});

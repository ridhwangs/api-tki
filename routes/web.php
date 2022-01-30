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
    //gate setting
    $router->post('gate/setting',  ['uses' => 'ParkirController@gateSetting']);

    // operator 
    $router->get('shift',  ['uses' => 'OperatorController@showAllShift']);
    $router->post('auth',  ['uses' => 'OperatorController@doLogin']);
    $router->post('auth/master',  ['uses' => 'OperatorController@checkMaster']);
    $router->post('operator/laporan',  ['uses' => 'OperatorController@laporan']);
    $router->post('operator/laporan/print',  ['uses' => 'OperatorController@print']);

    // menu parkir
    $router->get('parkir',  ['uses' => 'ParkirController@index']);
    $router->get('sync',  ['uses' => 'ParkirController@sync']);
    $router->post('parkir/info',  ['uses' => 'ParkirController@info']);

    // masuk parkir 
    $router->post('parkir/in',  ['uses' => 'ParkirController@parkirIn']);
    $router->post('parkir/manual',  ['uses' => 'ParkirController@parkirManual']);
    $router->post('member/in',  ['uses' => 'ParkirController@memberIn']);

    $router->get('image/{imageName}',  ['uses' => 'ParkirController@image']);

    // keluar parkir
    $router->post('parkir/out',  ['uses' => 'ParkirController@parkirOut']);
    $router->post('parkir/bayar',  ['uses' => 'ParkirController@parkirBayar']);
    $router->post('member/out',  ['uses' => 'ParkirController@memberOut']);
    

    $router->post('parkir/tarif',  ['uses' => 'ParkirController@getTarif']);
    $router->post('parkir/kategori',  ['uses' => 'ParkirController@setKategori']);
    $router->post('parkir/kendaraan',  ['uses' => 'ParkirController@setKendaraan']);

    //member
    $router->post('member',  ['uses' => 'MemberController@index']);
    $router->post('member/registrasi',  ['uses' => 'MemberController@MemberRegistrasi']);
    $router->post('member/topup',  ['uses' => 'MemberController@memberTopup']);

    //master
    $router->get('kendaraan',  ['uses' => 'KendaraanController@index']);

    // set expired data for cron_job *WARNING*
    $router->get('parkir/expired',  ['uses' => 'ParkirController@setExpiredPakir']);
    $router->get('parkir/expired/delete',  ['uses' => 'ParkirController@deleteExpiredPakir']);
    $router->get('member/validasi',  ['uses' => 'MemberController@ValidasiMember']);

});

<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Broadcast;
use Tutlance\Models\User;

Auth::routes();
 Route::get('loadwaf','WafController@waf');

Route::get('/logout', 'Auth\LoginController@logout');

Route::get('verification','AuthController@generate_verification');
Route::post('user/checklogin','AuthController@checkLoginStatus');

Route::group(['middleware' => ['auth']], function () {
    //Route::get('/','HomeController@index');

    Route::get('/services', 'ServicesController@services');
    Route::post('/services/new','ServicesController@new_service');
    Route::get('/services/{id}','ServicesController@service');
    Route::post('/services/update','ServicesController@update_service');

    Route::get('/businesses','BusinessController@businesses');
    Route::get('/business/{id}','BusinessController@business');
    Route::post('/business/new','BusinessController@new_business');
    Route::post('/business/update','BusinessController@update_business');
    Route::post('/business/services/new','BusinessController@new_service');
    Route::get('/business/services/delete/{_id}','BusinessController@del_service');
    Route::post('/business/working_hours/new','BusinessController@new_working_hours');
    Route::get('/business/working_hours/delete/{_id}','BusinessController@delete_working_hours');
    Route::get('/business/appointments/accept/{_id}','BusinessController@accept_appointment');
    Route::get('/business/appointments/reject/{_id}','BusinessController@reject_appointment');
    Route::post('/business/gallery/upload','BusinessController@upload_gallery');

    Route::get('/experts','ExpertsController@experts');
    Route::get('/expert/{id}','ExpertsController@expert');
    Route::post('/expert/new','ExpertsController@new_expert');
    Route::post('/expert/update','ExpertsController@update_expert');

    Route::post('/expert/services/new','ExpertsController@new_service');
    Route::get('/expert/services/delete/{_id}','ExpertsController@del_service');
    Route::post('/expert/working_hours/new','ExpertsController@new_working_hours');
    Route::get('/expert/working_hours/delete/{_id}','ExpertsController@delete_working_hours');
    Route::get('/expert/appointments/accept/{_id}','ExpertsController@accept_appointment');
    Route::get('/expert/appointments/reject/{_id}','ExpertsController@reject_appointment');
    Route::post('/expert/gallery/upload','ExpertsController@upload_gallery');

    Route::get('/users', 'UserController@index')->middleware('perm:1');
    Route::get('/users/delete/{user_id}', 'UserController@delete_user')->middleware('perm:1');
    Route::post('/enroll', 'UserController@register_user')->middleware('perm:1');
    Route::get('/users/profile/{user_id}', 'UserController@profile')->middleware('perm:1');
    Route::post('/users/profile/update', 'UserController@update')->middleware('perm:1');
   
});


require 'payments.php';


Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
//    if (Auth::guest() && preg_match('/online/', $request->channel_name)) {
//        Auth::login(User::makeGuestUser());
//    }
    dump($request);
    return Broadcast::auth($request);
});

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
Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/','HomeController@index');

    Route::get('/services', 'ServicesController@services');
    Route::post('/services/new','ServicesController@new_service');
    Route::get('/services/{id}','ServicesController@service');
    Route::post('/services/update','ServicesController@update_service');


    Route::get('/providers', 'ProviderController@service_providers');
    Route::post('/provider/new','ProviderController@new_provider');
    Route::get('/provider/{id}','ProviderController@service_provider');
    Route::post('/provider/update','ProviderController@update_provider');

    Route::get('/providers', 'ProviderController@service_providers');
    Route::post('/provider/new','ProviderController@new_provider');
    Route::get('/provider/{id}','ProviderController@service_provider');
    Route::post('/provider/update','ProviderController@update_provider');

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

    Route::get('/companies','CompanyController@index')->middleware('perm:1');
    Route::get('/companies/{id}','CompanyController@company')->middleware('perm:1');
    Route::post('/companies/new','CompanyController@new_company')->middleware('perm:1');
    Route::post('/companies/update','CompanyController@update_company')->middleware('perm:1');

    Route::get('/depots','DepotController@index')->middleware('perm:1');
    Route::get('/depots/{id}','DepotController@depot')->middleware('perm:1');
    Route::post('/depots/new','DepotController@new_depot')->middleware('perm:1');
    Route::post('/depots/update','DepotController@update_depot')->middleware('perm:1');

    Route::get('/drivers','DriverController@index')->middleware('perm:1');
    Route::get('/drivers/{id}','DriverController@driver')->middleware('perm:1');
    Route::post('/drivers/new','DriverController@new_driver');
    Route::post('/drivers/update','DriverController@update_driver')->middleware('perm:1');

    Route::get('/vehicles','VehicleController@index')->middleware('perm:1');
    Route::get('/vehicles/{id}','VehicleController@vehicle');//->middleware('perm:1');
    Route::post('/vehicles/new','VehicleController@new_vehicle');//->middleware('perm:1');
    Route::post('/vehicles/update','VehicleController@update_vehicle')->middleware('perm:1');
    Route::post('/vehicles/assign_driver','VehicleController@assign_driver')->middleware('perm:1');
    Route::get('/vehicles/revoke_driver/{vehicle_id}/{driver_id}','VehicleController@revoke_driver')->middleware('perm:1');
    Route::post('/vehicles/blacklist','VehicleController@blacklist_vehicle')->middleware('perm:1');

    Route::get('/users', 'UserController@index')->middleware('perm:1');
    Route::get('/users/delete/{user_id}', 'UserController@delete_user')->middleware('perm:1');
    Route::post('/enroll', 'UserController@register_user')->middleware('perm:1');
    Route::get('/users/profile/{user_id}', 'UserController@profile')->middleware('perm:1');
    Route::post('/users/profile/update', 'UserController@update')->middleware('perm:1');

    Route::post('/orders/new','OrderController@new_order');//->middleware('perm:1');
    Route::get('/orders/{order_id}','OrderController@view_order');//->middleware('perm:1');
    Route::get('/orders/mark/{order_id}','OrderController@mark_loaded');//->middleware('perm:1');

});

require 'payments.php';
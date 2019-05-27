<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('resend_verification','AuthController@resend_verification');
    Route::post('verify_code','AuthController@verify_code');
    Route::get('account/verify/{code}','AuthController@verify_code');
    

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

/** crude before service request can be done **/
Route::group([
    'prefix' => 'status-categories' 
], function() {
    Route::get('all', 'StatusCategoriesController@get');
    Route::get('get/{id}', 'StatusCategoriesController@get');
    Route::post('create', 'StatusCategoriesController@create');
    Route::put('update', 'StatusCategoriesController@update');
    Route::delete('del', 'StatusCategoriesController@delete');

});

/** crude before service request can be done **/
Route::group([
    'prefix' => 'statuses' 
], function() {
    Route::get('all', 'StatusesController@get');
    Route::get('get/{id}', 'StatusesController@get');
    Route::post('create', 'StatusesController@create');
    Route::put('update', 'StatusesController@update');
    Route::delete('del', 'StatusesController@delete');

});


Route::group([
    'prefix' => 'categories'
], function() {
    Route::get('all', 'ServiceCategoryController@get');
    Route::get('get/{id}', 'ServiceCategoryController@get');
    Route::post('create', 'ServiceCategoryController@create');
    Route::put('update', 'ServiceCategoryController@update');
    Route::delete('del', 'ServiceCategoryController@delete');
});

Route::group([
    'prefix' => 'service-packages'
], function() {
    Route::get('all', 'ServicePackagesController@get');
    Route::get('get/{category_id}', 'ServicePackagesController@get');
    Route::post('create', 'ServicePackagesController@create');
    Route::put('update', 'ServicePackagesController@update');
    Route::delete('del', 'ServicePackagesController@delete');

});

Route::group([
    'prefix' => 'service-package-details'
], function() {
    Route::get('all', 'ServicePackageDetailsController@get');
    Route::get('get/{package_id}', 'ServicePackageDetailsController@get');
    Route::post('create', 'ServicePackageDetailsController@create');
    Route::put('update', 'ServicePackageDetailsController@update');
    Route::post('update', 'ServicePackageDetailsController@update');
    Route::delete('del', 'ServicePackageDetailsController@delete');

});

Route::group([
    'prefix' => 'services' 
], function() {
    Route::get('all', 'ServicesController@get');
    Route::get('get/{category_id}', 'ServicesController@get');
    Route::post('create', 'ServicesController@create');
    Route::put('update', 'ServicesController@update');
    Route::delete('del', 'ServicesController@delete');

});

Route::group([
 'prefix' => 'provider-services'
], function(){
    Route::get('all', 'ProviderServicesController@get');
    Route::post('all', 'ProviderServicesController@get');
    Route::get('get/{id}', 'ProviderServicesController@get');
    Route::post('create', 'ProviderServicesController@create');
    Route::put('update', 'ProviderServicesController@update');
    Route::delete('del','ProviderServicesController@delete');
});

Route::group([
 'prefix' => 'service-providers'
], function(){
    Route::get('all', 'ServiceProvidersController@get');
    Route::get('popular', 'ServiceProvidersController@popular');
    Route::get('get/{id}', 'ServiceProvidersController@get');
    Route::get('services/{id}', 'ServiceProvidersController@getwithserviceid');
    Route::get('details/{id}', 'ServiceProvidersController@details');
    Route::post('create', 'ServiceProvidersController@create');
    Route::post('update', 'ServiceProvidersController@update');
    Route::post('time-slots', 'ServiceProvidersController@timeslots');
    Route::put('update', 'ServiceProvidersController@update');
    Route::delete('del','ServiceProvidersController@delete');
    Route::get('location_service','ServiceProvidersController@search_by_location_service');

});


Route::group([
 'prefix' => 'user-personal-details'
], function(){
    Route::get('all', 'UserPersonalDetailsController@get');
    Route::get('get/{id}', 'UserPersonalDetailsController@get');
    Route::post('create', 'UserPersonalDetailsController@create');
    Route::post('update', 'UserPersonalDetailsController@update');
    Route::delete('del','UserPersonalDetailsController@delete');
});

Route::group([
 'prefix' => 'service-providers/portfolios'
], function(){
    Route::get('all', 'ServiceProviderPortfoliosController@get');
    Route::get('get/{id}', 'ServiceProviderPortfoliosController@get');
    Route::post('create', 'ServiceProviderPortfoliosController@create');
    Route::delete('del','ServiceProviderPortfoliosController@delete');
});


Route::group([
 'prefix' => 'service-providers/operating-hours'
], function(){
    Route::get('all', 'OperatingHoursController@get');
    Route::get('get/{id}', 'OperatingHoursController@get');
    Route::post('create', 'OperatingHoursController@create');
    Route::put('update', 'OperatingHoursController@update');
    Route::delete('del','OperatingHoursController@delete');
});


Route::group([
 'prefix' => 'service-providers/reviews'
], function(){
    Route::get('all', 'ServiceProviderReviewsController@get');
    Route::get('get/{id}', 'ServiceProviderReviewsController@get');
    Route::post('create', 'ServiceProviderReviewsController@create');
    Route::delete('del','ServiceProviderReviewsController@delete');
});


Route::group([
'prefix' => 'bookings'
], function(){
   Route::get('all', 'BookingsController@get');
   Route::get('service-providers/all/{id}', 'BookingsController@get');
   Route::get('users/all/{id}', 'BookingsController@getUserBookings');
   Route::get('details/{id}', 'BookingsController@getBookingDetails');
   Route::get('user/booking-with-details/{id}', 
        'BookingsController@getUserBookingWithDetails');
   Route::get('provider/booking-with-details/{id}', 
        'BookingsController@getProviderBookingWithDetails');

   Route::post('create', 'BookingsController@create');
   Route::put('update', 'BookingsController@updateBooking');
   Route::delete('delete','BookingsController@delete');

});


Route::get('balances/all','BalancesController@get_all');
Route::post('balances/create','BalancesController@store');


Route::group([
'prefix' => 'home'
], function(){
   Route::get('get', 'HomePageController@get');

});

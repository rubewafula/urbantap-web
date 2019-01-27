<?php

use Illuminate\Http\Request;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::group([
//    'prefix' => 'businesses'
//], function () {
//    Route::get('featured', function() {
//        // If the Content-Type and Accept headers are set to 'application/json',
//        // this will return a JSON structure. This will be cleaned up later.
//        return \App\Business::paginate(10);
//    });
//
//    Route::get('featured/{id}', function($id) {
//        return \App\Business::find($id);
//    });
//});


Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});


Route::apiResource('business', 'BusinessController');
Route::apiResource('experts', 'ExpertsController');



Route::group([
    'prefix' => 'business'
], function () {

    Route::get('services/{id}', function($id) {
        // If the Content-Type and Accept headers are set to 'application/json',
        // this will return a JSON structure. This will be cleaned up later.
        return \App\ProviderServices::where('service_provider_id', $id)->get();
    });

    Route::get('salons', 'ApiController@salons');
    Route::get('massage', 'ApiController@massage');
    Route::get('details/{id}', 'ApiController@business_details');
    Route::get('reviews/{id}', 'ApiController@business_reviews');
    Route::get('portfolio/{id}', 'ApiController@business_portfolio');

});

Route::group([
    'prefix' => 'professional'
], function () {

    Route::get('services/{id}', function($id) {
        // If the Content-Type and Accept headers are set to 'application/json',
        // this will return a JSON structure. This will be cleaned up later.
        return \App\ProviderServices::where('service_provider_id', $id)->get();
    });

    Route::get('details/{id}', 'ApiController@expert_details');
    Route::get('reviews/{id}', 'ApiController@business_reviews');
    Route::get('portfolio/{id}', 'ApiController@business_portfolio');

});

Route::group([
    'prefix' => 'explore'
], function () {

    Route::get('salons', function() {
        return \App\Http\Resources\SalonsResource::collection(\App\ProviderCategory::where('category_id',1)->orderBy('id', 'desc')->paginate(20));
    });

    Route::get('massage', function() {
        return \App\Http\Resources\MassageResource::collection(\App\ProviderCategory::where('category_id',2)->orderBy('id', 'desc')->paginate(20));

    });

});

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::post('appointments', 'ApiController@get_appointments');
    Route::post('appointments/book', 'ApiController@book_appointment');
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


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
    Route::post('resend_verification', 'AuthController@resend_verification');
    Route::post('verify-code', 'AuthController@verify_code');
    Route::post('account/verify/{code}', 'AuthController@verify_code');
    Route::get('account/verify/{code}', 'AuthController@verify_code');

    Route::post('forgot-password', 'AuthController@forgot_password');
    Route::post('reset-password', 'AuthController@reset_password');


    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::post('change-password', 'AuthController@change_password');
        Route::get('user', 'AuthController@user');
    });

    // Facebook Auth
    Route::post('facebook', 'FacebookAuthController@store');
    Route::post('google', 'GoogleAuthController@store');
});

/** crude before service request can be done **/
Route::group([
    'prefix' => 'status-categories'
], function () {
    Route::get('all', 'StatusCategoriesController@get');
    Route::get('get/{id}', 'StatusCategoriesController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'StatusCategoriesController@create');
        Route::put('update', 'StatusCategoriesController@update');
        Route::delete('del', 'StatusCategoriesController@delete');
    });

});

/** crude before service request can be done **/
Route::group([
    'prefix' => 'statuses'
], function () {
    Route::get('all', 'StatusesController@get');
    Route::get('get/{id}', 'StatusesController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'StatusesController@create');
        Route::put('update', 'StatusesController@update');
        Route::delete('del', 'StatusesController@delete');
    });

});


Route::group([
    'prefix' => 'categories'
], function () {
    Route::get('all', 'ServiceCategoryController@get');
    Route::get('get/{id}', 'ServiceCategoryController@get');

    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'ServiceCategoryController@create');
        Route::put('update', 'ServiceCategoryController@update');
        Route::delete('del', 'ServiceCategoryController@delete');
    });
});

Route::group([
    'prefix' => 'service-packages'
], function () {
    Route::get('all', 'ServicePackagesController@get');
    Route::get('get/{category_id}', 'ServicePackagesController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'ServicePackagesController@create');
        Route::put('update', 'ServicePackagesController@update');
        Route::delete('del', 'ServicePackagesController@delete');
     });
});

Route::group([
    'prefix' => 'service-package-details'
], function () {
    Route::get('all', 'ServicePackageDetailsController@get');
    Route::get('get/{package_id}', 'ServicePackageDetailsController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'ServicePackageDetailsController@create');
        Route::put('update', 'ServicePackageDetailsController@update');
        Route::post('update', 'ServicePackageDetailsController@update');
        Route::delete('del', 'ServicePackageDetailsController@delete');
     });
});

Route::group([
    'prefix' => 'services'
], function () {
    Route::get('all', 'ServicesController@get');
    Route::get('get/{category_id}', 'ServicesController@get');

      Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'ServicesController@create');
        Route::put('update', 'ServicesController@update');
        Route::delete('del', 'ServicesController@delete');
     });

});

Route::group([
    'prefix' => 'provider-services'
], function () {
    Route::get('all', 'ProviderServicesController@get');
    Route::post('all', 'ProviderServicesController@get');
    Route::get('service/{id}', 'ProviderServicesController@provider_service_detail');
    Route::get('get/{id}', 'ProviderServicesController@get');

     Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'ProviderServicesController@create');
        Route::post('update', 'ProviderServicesController@update');
        Route::delete('delete', 'ProviderServicesController@delete');
     });
});

Route::group([
    'prefix' => 'service-providers'
], function () {
    Route::get('all', 'ServiceProvidersController@get');
    Route::get('popular', 'ServiceProvidersController@popular');
    Route::get('get/{id}', 'ServiceProvidersController@get');
    Route::get('services/{id}', 'ServiceProvidersController@getwithserviceid');
    Route::get('details/{id}', 'ServiceProvidersController@details');
    Route::post('time-slots', 'ServiceProvidersController@timeslots');
    Route::get('location_service', 'ServiceProvidersController@search_by_location_service');

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::post('create', 'ServiceProvidersController@create');
        Route::post('update', 'ServiceProvidersController@update');
        Route::delete('del', 'ServiceProvidersController@delete');
        Route::put('update', 'ServiceProvidersController@update');
        Route::get('transactions', 'ServiceProvidersController@transactions');

    });

});


Route::group([
    'prefix'     => 'user-personal-details',
    'middleware' => 'auth:api'
], function () {
    Route::get('all', 'UserPersonalDetailsController@get');

    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::get('get', 'UserPersonalDetailsController@get');
        Route::get('transactions', 'UserPersonalDetailsController@transactions');
        Route::post('create', 'UserPersonalDetailsController@create');
        Route::post('update', 'UserPersonalDetailsController@update');
        Route::delete('del', 'UserPersonalDetailsController@delete');
     });
});

Route::group([
    'prefix' => 'service-providers/portfolios'
], function () {
    Route::get('all', 'ServiceProviderPortfoliosController@get');
    Route::get('get/{id}', 'ServiceProviderPortfoliosController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'ServiceProviderPortfoliosController@create');
        Route::delete('del', 'ServiceProviderPortfoliosController@delete');
     });
});


Route::group([
    'prefix' => 'service-providers/operating-hours'
], function () {
    Route::get('all', 'OperatingHoursController@get');
    Route::get('get/{id}', 'OperatingHoursController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::post('create', 'OperatingHoursController@create');
        Route::put('update', 'OperatingHoursController@update');
        Route::delete('del', 'OperatingHoursController@delete');
     });
});


Route::group([
    'prefix' => 'service-providers/reviews'
], function () {
    Route::get('all', 'ServiceProviderReviewsController@get');
    Route::group([
        'middleware' => 'auth:api'
        ], function () {
        Route::get('get', 'ServiceProviderReviewsController@getUserReviews');
        Route::post('create', 'ServiceProviderReviewsController@create');
        Route::delete('del', 'ServiceProviderReviewsController@delete');
     });
});


Route::group([
    'prefix'     => 'bookings',
    'middleware' => 'auth:api'
], function () {
    Route::get('all', 'BookingsController@get');
    Route::get('service-providers/all/{id}', 'BookingsController@get');
    Route::get('user/all', 'BookingsController@getUserBookings');
    Route::get('details/{id}', 'BookingsController@getBookingDetails');
    Route::get('user/booking-with-details/{id}',
        'BookingsController@getUserBookingWithDetails');
    Route::get('provider/booking-with-details/{id}',
        'BookingsController@getProviderBookingWithDetails');

    Route::post('create', 'BookingsController@create');
    Route::delete('delete', 'BookingsController@delete');

    // Update booking status
    Route::patch('accept', 'BookingStatusController@update');
    Route::patch('reject', 'BookingStatusController@update');

});


Route::group([
    'prefix' => 'home'
], function () {
    Route::get('get', 'HomePageController@get');

});

Route::group(['prefix' => 'notifications', 'middleware' => 'auth:api'], function () {
    Route::post('', 'UserNotificationController@index');
});

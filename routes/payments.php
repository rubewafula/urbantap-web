<?php

Route::post('/mpesa/c2b/confirm', 'PaymentsController@receive_mpesa');
Route::post('/mpesa/c2b/process', 'PaymentsController@mpesa_payment');
Route::post('/booking/checkstatus', 'PaymentsController@booking_status');

Route::post('/api/sms/sendsms', 'SMSController@send_sms');

?>
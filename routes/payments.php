<?php

Route::post('/mpesa/c2b/payment', 'PaymentsController@receive_mpesa');
Route::post('/mpesa/c2b/process', 'PaymentsController@mpesa_payment');
Route::post('/booking/checkstatus', 'PaymentsController@booking_status');
Route::post('/mpesa/c2b/tips', 'PaymentsController@receive_mpesa_tips');
Route::post('/api/sms/sendsms', 'SMSController@send_sms');
Route::post('/api/email/sendemail', 'EmailController@sendEmail');

?>
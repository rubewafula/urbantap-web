<?php

Route::post('/mpesa/c2b/confirm', 'PaymentsController@receive_mpesa');
Route::post('/mpesa/c2b/process', 'PaymentsController@mpesa_payment');

?>
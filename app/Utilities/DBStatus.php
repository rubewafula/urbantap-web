<?php
// This can be found in the Symfony\Component\HttpFoundation\Response class
// Reuben Wafula

namespace App\Utilities;

class DBStatus{
	const RECORD_PENDING = 1;
	const RECORD_APPROVED = 2;
	const RECORD_DELETED = 3;
	const COMPLETE = 4;
	const SUCCESS = 5;
	const USER_NEW= 20;
	const USER_ACTIVE= 21;
	const USER_SUSPENDED= 22;
	const USER_BLOCKED= 23;
	const  SMS_NEW= 24;
	const  SMS_SENT= 25;
	const  SMS_DELIVERED =26;
	const  SMS_FAILED= 27;


}


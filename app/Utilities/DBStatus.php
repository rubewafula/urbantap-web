<?php
// This can be found in the Symfony\Component\HttpFoundation\Response class
// Reuben Wafula

namespace App\Utilities;

class DBStatus
{
    const TRANSACTION_PENDING           = 1;
    const TRANSACTION_ACTIVE            = 1;
    const TRANSACTION_PENDING_APPROVAL  = 2;
    const TRANSACTION_FAILED            = 3;
    const TRANSACTION_DELETED           = 3;
    const TRANSACTION_COMPLETE          = 5;
    const TRANSACTION_CANCELLED         = 4;
    const TRANSACTION_REVERSED          = 6;


    /** Booking status **/
    const BOOKING_NEW                   = 7;
    const BOOKING_ACCEPTED              = 8;
    const BOOKING_CANCELLED             = 11;
    const BOOKING_COMPLETE              = 10;
    const BOOKING_REJECTED              = 9;
    const BOOKING_CLOSED                = 12;
    const BOOKING_NO_SHOW               = 13;
    const BOOKING_POSTPONED             = 14;

    const BOOKING_POST_REJECTED         = 29;
    const BOOKING_PAID                  = 11;

    /** user status **/
    const USER_NEW                      = 15;
    const USER_ACTIVE                   = 16;
    const USER_SUSPENDED                = 17;
    const USER_BLOCKED                  = 18;
    const USER_AVAILABLE                = 19;

    /** Notification status **/
    const USER_NOT_AVAILABLE            = 20;
    const NOTIFICATION_NEW              = 21;
    const NOTIFICATION_SENT             = 22;
    const NOTIFICATION_DELIVERED        = 23;
    const NOTIFICATION_FAILED           = 24;
    const NOTIFICATION_SEEN             = 25;

    /** Service status **/
    const SERVICE_ACTIVE                = 26;
    const SERVICE_SUSPENDED             = 27;
    const SERVICE_NOT_AVAILABLE         = 28;

}

@component('mail::message')
# Booking Received

Your booking has been received.

@component('mail::table')
|                  |                              |
|:---------------- |:---------------------------- |
| ** Service Provider ** | {{ $data['business_name'] }} |
| ** Service  ** | {{ $data['service_name'] }} |
| ** Service Cost  ** | {{ $data['service_cost'] }} |
| ** Service Duration  ** | {{ $data['service_duration'] }} |
| ** Service Duration  ** | {{ $data['service_duration'] }} (minutes) |
@endcomponent

#### Service Description

{{ $data['description'] }}

** <u>Booking Date: {{ $data['booking_time'] }}</u> **

To change or cancel your booking, log into [{{ config('app.name') }}]({{ config('app.url') }})  and select "My Bookings" bookmark

Thanks,
{{ config('app.name') }}
@endcomponent

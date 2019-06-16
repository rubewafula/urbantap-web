@component('mail::message')
# Booking Confirmed

@component('mail::table')
|               |                 |
|:------------- |:--------------- |
| ** Service  ** | {{ $data['service_name'] }} |
| ** Service Duration  ** | {{ $data['service_duration'] }} (minutes) |
| ** Booking Date  ** | {{ $data['booking_time'] }} |
| ** Service Cost  ** | {{ $data['service_cost'] }} |
| ** Amount Paid  ** | {{ $data['amount_paid'] }} |
@endcomponent

Kindly note the booking time.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

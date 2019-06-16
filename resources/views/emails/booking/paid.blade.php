@component('mail::message')
# Payment Received

Your payment has been received.

@component('mail::table')
|               |                 |
|:------------- |:--------------- |
| ** Service Provider ** | {{ $data['business_name'] }} |
| ** Service  ** | {{ $data['service_name'] }} |
| ** Service Duration  ** | {{ $data['service_duration'] }} (minutes) |
| ** Booking Date  ** | {{ $data['booking_time'] }} |
| ** Service Cost  ** | {{ $data['service_cost'] }} |
| ** Amount Paid  ** | {{ $data['amount_paid'] }} |
| ** Payment Reference  ** | {{ $data['payment_ref'] }} |
| ** Balance  ** | ** {{ $data['balance'] }} **|
@endcomponent

@if($data['reserved'])
Your slot has been reserved for {{ $data['service_name'] }}.
@else
Pay at least {{ $data['amount_to_booking'] }} to reserve your booking.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent

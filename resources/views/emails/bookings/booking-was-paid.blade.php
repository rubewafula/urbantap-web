@component('mail::message')
# Payment Received

Your payment has been received.

@component('mail::table')
|               |                 |
|:------------- |:--------------- |
| ** Amount Received ** | {{ $data['amount'] }} |
| ** Reference   ** | {{ $data['ref'] }} |
| ** Time Paid  ** | {{ $data['transaction_time'] }} |
@endcomponent


Kindly log into [{{ config('app.name') }}]({{ config('app.url') }}) and select "My Bookings" bookmark to confirm your booking.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

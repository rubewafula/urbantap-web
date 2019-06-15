@component('mail::message')
# Booking Rejected

Your booking for {{ $data['service_name'] }} from {{ $data['business_name'] }} has been rejected.

Go to [{{ config('app.name') }}]({{ config('app.url') }}), and book with a different provider.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

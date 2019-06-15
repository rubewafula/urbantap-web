@component('mail::message')
# Booking Cancelled

You have successfully cancelled your booking, {{ $data['service_name'] }} from {{ $data['business_name'] }}

To make a new booking, visit [{{ config('app.name') }}]({{ config('app.url') }}), and select your preferred provider.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

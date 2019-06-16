@component('mail::message')
# Booking Rejected

Unfortunately your booking for {{ $data['service_name'] }} from {{ $data['business_name'] }} has been rejected.

Feel free to visit [{{ config('app.name') }}]({{ config('app.url') }}) and book with a different provider.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

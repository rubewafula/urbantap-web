@component('mail::message')
    # Booking Received

    Your booking has been received.

    {{ $data['business_name'] }}
    {{ $data['service_name'] }}
    {{ $data['description'] }}

    {{ $data['booking_time'] }}

    @component('mail::button', ['url' => config('app.url')])
        Button Text
    @endcomponent

    To change or cancel your booking, log into ({{ config('app.url') }})[{{ config('app.url') }}]  and select "My Bookings" bookmark

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent

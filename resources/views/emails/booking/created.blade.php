@component('mail::message')
    # Booking Received

    Your booking has been received.

    **{{ $data['business_name'] }}**

    __{{ $data['service_name'] }}__
    {{ $data['description'] }}

    {{ $data['booking_time'] }}

    To change or cancel your booking, log into ({{ config('app.url') }})[{{ config('app.url') }}]  and select "My Bookings" bookmark

    Thanks,
    {{ config('app.name') }}
@endcomponent

@component('mail::message')
    # Booking Received

    Your booking has been received.

    ## {{ $data['business_name'] }}

    __ {{ $data['service_name'] }} __
    {{ $data['description'] }}

    {{ $data['booking_time'] }}

    Use two asterisks for **strong emphasis**.

    To change or cancel your booking, log into [{{ config('app.name') }}]({{ config('app.url') }})  and select "My Bookings" bookmark

    Thanks,
    {{ config('app.name') }}
@endcomponent

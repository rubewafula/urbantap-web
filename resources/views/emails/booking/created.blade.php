@component('mail::message')
    # Booking Received

    Your booking has been received.

    {{ $data['business_name'] }}<br>
    {{ $data['service_name'] }}<br>
    {{ $data['description'] }}<br>

    {{ $data['booking_time'] }}

    @component('mail::button', ['url' => config('app.url')])
        Button Text
    @endcomponent

    To change or cancel your booking, log into
    <a href="{{ config('app.url') }}">{{ config('app.name') }}</a> and select "My Bookings" bookmark

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent

@component('mail::message')
# Booking Received

You have received a new booking from {{ $data['user_name'] }}

@component('mail::table')
|               |                 |
|:------------- |:--------------- |
| ** Service Location  ** | {{ $data['location_name'] }} |
| ** Location Description ** | {{ $data['location_description'] }} |
| ** Booking Date  ** | {{ $data['booking_time'] }} |
@endcomponent

To respond to this booking, log into [{{ config('app.name') }}]({{ config('app.url') }})  and select "My Bookings" on your provider profile.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

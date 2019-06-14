@component('mail::message')
# Booking Accepted

Your booking for {{ $data['service_name'] }} from {{ $data['business_name'] }} has been accepted. Please follow the instructions below to pay and confirm.

## Payment Information

- Go to Safaricom SIM Tool Kit, select ** M-PESA ** menu, select ** Lipa na M-PESA **
- Select ** Pay Bill **
- Select ** Enter Business no. **, Enter {{ config('app.name') }} Lipa na M-PESA PayBill Number *{{ env('URBANTAP_PAYBILL') }}* and press OK
- Select ** Enter Account no. **, Enter your * Booking Number * * {{ $data['booking_id'] }} * and press OK
- ** Enter Amount **, * {{ $data["service_cost"] }} * and press OK
- ** Enter your M-PESA PIN ** and press OK
- ** Confirm ** all the details are correct and press OK
- You will receive a ** confirmation SMS ** from M-PESA.

You can also log into [{{ config('app.name') }}]({{ config('app.url') }}), select "My Bookings" bookmark and complete your payment

Thanks,<br>
{{ config('app.name') }}
@endcomponent

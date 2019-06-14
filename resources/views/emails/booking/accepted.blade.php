@component('mail::message')
# Booking Accepted

Your booking for {{ $data['service_name'] }} from {{ $data['business_name'] }} has been accepted. Please follow the instructions below to pay and confirm.

<h3 style="padding: 8px">Payment Information</h3>
<ol>
<li>
    Go to Safaricom SIM Tool Kit, select <strong>M-PESA</strong> menu, select “<strong>Lipa</strong><strong> na </strong><strong>M-PESA</strong>“
</li>
<li>Select “<strong>Pay Bill</strong>“</li>
<li>Select “<strong>Enter Business no.</strong>“, Enter {{ config('app.name') }}’s Lipa na M-PESA PayBill Number
    <span class="emphasize">{{ env('URBANTAP_PAYBILL') }}</span>&nbsp;and press “OK”
</li>
<li>Select “<strong>Enter Account no.</strong>“, Enter your <span
            class="emphasize"> Booking Number </span> <span class="emphasize">{{ $data['booking_id'] }}</span> and
    press “OK”
</li>
<li>“<strong>Enter Amount</strong>“, <span class="emphasize">{{ $data["service_cost"] }}</span> and press “OK”</li>
<li><strong>Enter your M-PESA PIN</strong> and press “OK”</li>
<li><strong>Confirm</strong> all the details are correct and press “OK”</li>
<li>You will receive a <strong>confirmation SMS</strong> from M-PESA.</li>
</ol>

You can also log into [{{ config('app.name') }}]({{ config('app.url') }}), select "My Bookings" bookmark and complete your payment

Thanks,<br>
{{ config('app.name') }}
@endcomponent

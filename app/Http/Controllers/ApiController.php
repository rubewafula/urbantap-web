<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Business;
use App\Expert;
use App\Http\Resources\MassageResource;
use App\Http\Resources\SalonsResource;
use App\OperatingHours;
use App\Portfolio;
use App\ProviderCategory;
use App\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function business_details($service_provider_id){
        $business = Business::where('service_provider_id',$service_provider_id)->first();
        $business_hours = OperatingHours::where('service_provider_id',$service_provider_id);
        return response()->json([
            'business' => optional($business)->details(),
            'business_hours' => optional($business_hours)->get()
        ], 200);
    }

    public function expert_details($service_provider_id){
        $expert = Expert::where('service_provider_id',$service_provider_id)->first();
        $business_hours = OperatingHours::where('service_provider_id',$service_provider_id);
        return response()->json([
            'professional' => optional($expert)->details(),
            'business_hours' => optional($business_hours)->get()
        ], 200);
    }

    public function business_reviews($service_provider_id){
        $reviews = Review::where('service_provider_id',$service_provider_id)->orderBy('id', 'desc')->get();
        return response()->json([
            'reviews' =>$reviews,
        ], 200);
    }

    public function business_portfolio($service_provider_id){
        $reviews = Portfolio::where('service_provider_id',$service_provider_id)->orderBy('id', 'desc')->get();
        return response()->json([
            'portfolio' =>$reviews,
        ], 200);
    }

    public function book_appointment(Request $request){

        $appointment = new Appointment();
        $appointment->provider_services_id = $request->provider_services_id;
        $appointment->service_provider_id = $request->service_provider_id;
        $appointment->customer_id = $request->user()->id;
        $appointment->date =  Carbon::createFromFormat('Y-m-d', $request->date)->toDateString();
        $appointment->time = Carbon::createFromFormat('H:i:s', $request->time)->toTimeString();

        $appointment->saveOrFail();

        return response()->json([
            'message' => "Your appointment has been booked, please wait for confirmation from the service provider",
        ], 200);
    }

    public function get_appointments(Request $request){

        $appointments = Appointment::where('date', $request->date)->where('customer_id', $request->user()->id)->orderBy('time', 'asc')->get();

        return response()->json(
            $appointments
        , 200);
    }
}

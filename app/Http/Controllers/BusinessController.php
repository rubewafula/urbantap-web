<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Business;
use App\Http\Resources\BusinessResource;
use App\OperatingHours;
use App\Portfolio;
use App\ProviderServices;
use App\ServiceProvider;
use App\ServiceProviderImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return BusinessResource::collection(
            Business::paginate(25));

    }


    /**
     * Display the specified resource.
     *
     * @param Business $business
     * @return BusinessResource
     * @internal param int $id
     */
    public function show(Business $business)
    {
        return new BusinessResource($business);

    }

    public function businesses()
    {
        $businesses = Business::orderBy('id', 'desc')->paginate(10);
        return view('business.businesses', ['businesses' => $businesses]);

    }

    function new_business(Request $request)
    {
        $this->validate($request, [
            'business_name' => 'required',
            'location' => 'required',
            'phone_no' => 'required',
        ]);

        if (is_null(Business::where('service_provider_id', 
            $request->service_provider)->first())){

            DB::transaction(function() use ($request) {
                $business = new Business();
                $business->service_provider_id = $request->service_provider;
                $business->business_name = $request->business_name;
                $business->location = $request->location;
                $business->phone_no = $request->phone_no;
                $business->facebook = $request->facebook_link;
                $business->instagram = $request->instagram_link;
                $business->description = $request->description;
                $business->lat = $request->lat;
                $business->lng = $request->lng;
                $business->saveOrFail();
                Session::flash("success", "Business created Successfully!");
            });

        }else{

            Session::flash("error", "Service provider is already registered to 
                another business!");
        }


        return redirect('/businesses');
    }

    function business($_id)
    {
        $business = Business::find($_id);
        if (is_null($business)){
            abort(404);
        }else{
            $services = ProviderServices::where('service_provider_id', 
                $business->serviceProvider->id)->orderBy('id', 'desc')->get();
            $appointments = Appointment::where('service_provider_id',
             $business->serviceProvider->id)->orderBy('id', 'desc')->paginate(20);
            $operatingHours = OperatingHours::where('service_provider_id', 
                $business->serviceProvider->id)->orderBy('id', 'desc')->get();
            $images = ServiceProviderImages::where('service_provider_id', 
                $business->serviceProvider->id)->orderBy('id', 'desc')->get();
            return view('business.business')
                ->with('business', $business)
                ->with('services', $services)
                ->with('appointments', $appointments)
                ->with('images', $images)
                ->with('operatingHours', $operatingHours);
        }
    }

    function update_business(Request $request)
    {
        $provider = ServiceProvider::find($request->provider_id);
        if (is_null($provider)){
            abort(404);
        }else{
            DB::transaction(function() use ($provider, $request) {
                $provider->service_provider_name = $request->provider_name;
                $provider->type = $request->type;
                $provider->update();
                Session::flash("success", "Service provider updated Successfully!");
            });
            return redirect('/providers');

        }
    }

    function new_service(Request $request)
    {
        $this->validate($request, [
            'service_name' => 'required',
            'cost' => 'required',
            'duration' => 'required',
            'provider_id' => 'required',
        ]);

        if (!is_null(ServiceProvider::find($request->provider_id))){
            DB::transaction(function() use ($request) {
                $providerService = new ProviderServices();
                $providerService->service_provider_id = $request->provider_id;
                $providerService->service_id = $request->service_name;
                $providerService->duration = $request->duration;
                $providerService->cost = $request->cost;
                $providerService->description = $request->description;
                $providerService->saveOrFail();
                Session::flash("success", "Service created Successfully!");
            });

        }else{

            Session::flash("error", "Invalid service provider. Please contact admin!");
        }


        return redirect()->back();
    }

    function del_service($_id)
    {

        $providerService = ProviderServices::find($_id);

        DB::transaction(function ()  use ($providerService) {
            if ($data = $providerService->delete()) {
                Session::flash("success", "Deleted Successfully!");

            }
        });
        return redirect()->back();
    }

    function accept_appointment($_id)
    {
        $appointment = Appointment::find($_id);

        if (!is_null($appointment)){
            $appointment->status = "ACCEPTED";
            $appointment->update();
            Session::flash("success", "Appointment has been accepted!");

        }else{
            Session::flash("error", "Appointment not found!");

        }
        return redirect()->back();
    }

    function reject_appointment($_id)
    {
        $appointment = Appointment::find($_id);

        if (!is_null($appointment)){
            $appointment->status = "CANCELLED";
            $appointment->update();
            Session::flash("success", "Appointment has been rejected!");

        }else{
            Session::flash("error", "Appointment not found!");

        }
        return redirect()->back();
    }

    function new_working_hours(Request $request)
    {
        $this->validate($request, [
            'day' => 'required',
            'time_from' => 'required',
            'time_to' => 'required',
            'provider_id' => 'required',
        ]);

        if (!is_null(ServiceProvider::find($request->provider_id))){
            DB::transaction(function() use ($request) {
                $operatingHour = new OperatingHours();
                $operatingHour->service_provider_id = $request->provider_id;
                $operatingHour->day = $request->day;
                $operatingHour->time_from = $request->time_from;
                $operatingHour->time_to = $request->time_to;
                $operatingHour->saveOrFail();
                Session::flash("success", "Operating period added Successfully!");
            });

        }else{

            Session::flash("error", "Invalid service provider. Please contact admin!");
        }


        return redirect()->back();
    }

    function delete_working_hours($_id)
    {

        $working = OperatingHours::find($_id);

        DB::transaction(function ()  use ($working) {
            if ($data = $working->delete()) {
                Session::flash("success", "Deleted Successfully!");

            }
        });
        return redirect()->back();
    }

    function upload_gallery(Request $request)
    {

        $this->request = $request;
        $this->validate($request, [
            'filesToUpload.*' => 'required|mimes:jpg,jpeg'
//            'filesToUpload.*' => 'required|mimes:jpg,jpeg,png,bmp,pdf,doc,docx,xls,xlsx|max:2000'
        ],[
            'filesToUpload.*.required' => 'Please select at least one file',
            'filesToUpload.*.mimes' => 'Only jpg/ jpeg files are allowed',
//            'filesToUpload.*.max' => 'Sorry! Maximum allowed size for a file is 5MB',
        ]);


        DB::transaction(function () use ($request) {

            if ($request->hasFile('filesToUpload')) {
                $files = $request->file('filesToUpload');
                $destinationPath = 'uploads';

                foreach ($files as $file) {

                    $file->move($destinationPath,$request->provider_id.'-'.$file->getClientOriginalName());

                    $providerImage = new ServiceProviderImages();
                    $providerImage->service_provider_id = $request->provider_id;
                    $providerImage->image = 'uploads/'.$request->provider_id.'-'.$file->getClientOriginalName();

                    $providerImage->saveOrFail();

                }

                Session::flash('success', 'Uploaded successfully');

            }else{

                Session::flash('error', 'Please select one or more files');


            }
        });

        return redirect()->back();
    }

}
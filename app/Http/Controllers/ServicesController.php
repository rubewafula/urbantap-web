<?php

namespace App\Http\Controllers;

use App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ServicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function services()
    {
        $services= Services::orderBy('id', 'desc')->paginate(10);
        return view('services.services', ['services' => $services]);

    }

    function new_service(Request $request)
    {
        $this->validate($request, [
            'service_name' => 'required',
        ]);

        $service_name = $request->service_name;
        $category_id = $request->category_id;

        DB::transaction(function() use ($service_name,$category_id) {
            $service = new Services();
            $service->service_name = $service_name;
            $service->category_id = $category_id;
            $service->saveOrFail();
            Session::flash("success", "Service created Successfully!");
        });

        return redirect('/services');
    }

    function service($service_id)
    {
        $service = Services::find($service_id);
        if (is_null($service)){
            abort(404);
        }else{
            return view('services.service')->with('service', $service);
        }
    }

    function update_service(Request $request)
    {
        $service = Services::find($request->service_id);
        if (is_null($service)){
            abort(404);
        }else{
            DB::transaction(function() use ($service, $request) {
                $service->service_name = $request->service_name;
                $service->category_id = $request->category_id;
                $service->update();
                Session::flash("success", "Service updated Successfully!");
            });
            return redirect()->back();

        }
    }



}

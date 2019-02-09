<?php

namespace App\Http\Controllers;

use App\ProviderCategory;
use App\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProviderController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function service_providers()
    {
        $providers = ServiceProvider::orderBy('id', 'desc')->paginate(10);
        return view('providers.providers', ['providers' => $providers]);

    }

    function new_provider(Request $request)
    {
        $this->validate($request, [
            'service_provider_name' => 'required',
        ]);

        $provider_name = $request->service_provider_name;
        $type = $request->type;

        DB::transaction(function() use ($provider_name,$type,$request) {
            $provider = new ServiceProvider();
            $provider->service_provider_name = $provider_name;
            $provider->type = $type;

            if ($provider->saveOrFail()){
                $providerCategory = new ProviderCategory();
                $providerCategory->service_provider_id = $provider->id;
                $providerCategory->category_id = $request->category;
                $providerCategory->saveOrFail();

                Session::flash("success", "Service provider created Successfully!");

            }
        });

        return redirect('/providers');
    }

    function service_provider($provider_id)
    {
        $provider = ServiceProvider::find($provider_id);
        if (is_null($provider)){
            abort(404);
        }else{
            return view('providers.provider')->with('provider', $provider);
        }
    }

    function update_provider(Request $request)
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
}

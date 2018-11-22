@extends('layouts.app')

@section('content')

    <section class="content">
        <div class="container-fluid">
        <div class="block-header">
            <h2>
                Smart Soko
            </h2>
        </div>

            <!-- Tab -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Businesses
                                <small>Add, remove, edit busionesses to the system</small>

                                <br>

                                @if (Session::has('message'))
                                    <div class="alert alert-info">{{ Session::get('message') }}</div>
                                @endif
                                @if (Session::has('error'))
                                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                                @endif
                                @if (Session::has('success'))
                                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                                @endif

                                @if (count($errors) > 0)
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif


                            </h2>
                            <ul class="header-dropdown m-r--5">
                                <li class="dropdown">
                                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <i class="material-icons">more_vert</i>
                                    </a>
                                    <ul class="dropdown-menu pull-right">
                                        <li><a href="javascript:void(0);">Action</a></li>
                                        <li><a href="javascript:void(0);">Another action</a></li>
                                        <li><a href="javascript:void(0);">Something else here</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs tab-nav-right" role="tablist">
                                <li role="presentation" class="active"><a href="#home" data-toggle="tab">BUSINESSES</a></li>
                                <li role="presentation"><a href="#adduser" data-toggle="tab">ADD BUSINESS</a></li>

                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="home">
                                    <b>All Businesses</b>

                                    <table class="table tabe-responsive">
                                        <thead>
                                        <tr>
                                            <th>Provider Name</th>
                                            <th>Business Name</th>
                                            <th>Location</th>
                                            <th>Phone No.</th>
                                            <th>Description</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($businesses as $business)
                                            <tr>
                                                <td>
                                                    {{$business->serviceProvider->service_provider_name}}
                                                </td>
                                                <td>
                                                    <a href="{{url('/business/'.($business->id))}}">{{$business->business_name}}</a>

                                                </td>
                                                <td>
                                                    {{$business->location}}
                                                </td>

                                                <td>
                                                    {{$business->phone_no}}
                                                </td>

                                                <td>
                                                    {{$business->description}}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    </table>
                                    {!! $businesses->render() !!}



                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="adduser">
                                    <b>Add Business</b>
                                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/business/new') }}">
                                        {{ csrf_field() }}
                                        <div class="modal-body">
                                            <div class="card">
                                                <div class="body">
                                                    <h2 class="card-inside-title">Business Details</h2>

                                                    <input type="hidden" id="lat" name="lat">
                                                    <input type="hidden" id="lng" name="lng">

                                                    <div class="row clearfix">
                                                        <div class="col-md-6 {{ $errors->has('service_provider') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class=" input-group-sm">
                                                                <select name="service_provider" class="select2 show-tick" required data-live-search="true">
                                                                    <option value="">Select Service Provider</option>
                                                                    @foreach(\App\ServiceProvider::all() as $provider)
                                                                        <option value="{{$provider->id}}">{{$provider->service_provider_name}}</option>
                                                                    @endforeach
                                                                    {{$errors->first("service_provider") }}
                                                                </select>
                                                            </div>
                                                        </div>



                                                        <div class="col-md-6 {{ $errors->has('business_name') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">N</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="business_name" autofocus required placeholder="Business Name">
                                                                    {{$errors->first("business_name") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('location') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">add_location</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" id="locationOfOperation" name="location" value="{{old('location')}}" required placeholder="Location">
                                                                    {{$errors->first("location") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('phone_no') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">local_phone</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="phone_no" required placeholder="Phone Number">
                                                                    {{$errors->first("phone_no") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('facebook_link') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">people</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="facebook_link"  placeholder="Facebook Link">
                                                                    {{$errors->first("facebook_link") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('instagram_link') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">add_a_photo</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="instagram_link"  placeholder="Instagram Link">
                                                                    {{$errors->first("instagram_link") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 {{ $errors->has('description') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <div class="form-line">
                                                                    <textarea rows="4" class="form-control no-resize" name="description"  placeholder="Please describe your business briefly..."></textarea>
                                                                    {{$errors->first("description") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row clearfix">
                                                        <div class="col-md-6" style="margin-bottom: 0px">
                                                            <button type="submit" class="btn btn-success waves-effect">Save</button>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Tab -->


    </div>
    </section>



@endsection

@section('scripts')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBCu-daCkuAjqXunJWuZ88LQG5OCYmV1p0&libraries=places"></script>

    <script>
        var autocomplete;
        var input = document.getElementById('locationOfOperation');
        var options = {
            //types: ['(cities)'],
            componentRestrictions: {country: 'ke'}
        };

        autocomplete = new google.maps.places.Autocomplete(input, options);

        var lat = document.getElementById('lat');
        var lng = document.getElementById('lng');




        function rm(nm,artistID){
            bootbox.confirm("Are you sure you want to delete \"" + nm + "\" ? ", function(result) {
                if(result) {

                    $.ajax({
                        url: 'users/delete/' + artistID,
                        type: 'get',
                        headers: {
                            'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                        },
                        success: function (html) {
                            location.reload();
                        }
                    });
                }
            });
        }


        $(document).ready(function() {
            google.maps.event.addListener(autocomplete, 'place_changed', function() {
//                infowindow.close();
//                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }

                // If the place has a geometry, then present it on a map.
//                if (place.geometry.viewport) {
//                    map.fitBounds(place.geometry.viewport);
//                } else {
//                    map.setCenter(place.geometry.location);
//                    map.setZoom(17); // Why 17? Because it looks good.
//                }
//                marker.setIcon( /** @type {google.maps.Icon} */ ({
//                    url: place.icon,
//                    size: new google.maps.Size(71, 71),
//                    origin: new google.maps.Point(0, 0),
//                    anchor: new google.maps.Point(17, 34),
//                    scaledSize: new google.maps.Size(35, 35)
//                }));
//                marker.setPosition(place.geometry.location);
//                marker.setVisible(true);

//                    console.log(place.geometry.location.lat());
//                    console.log(place.geometry.location.lng());
                lat.value = place.geometry.location.lat();
                lng.value = place.geometry.location.lng();

            });


            });
    </script>
@endsection

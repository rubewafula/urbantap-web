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
                                Experts
                                <small>Add, remove, edit experts to the system</small>

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
                                <li role="presentation" class="active"><a href="#home" data-toggle="tab">EXPERTS</a></li>
                                <li role="presentation"><a href="#adduser" data-toggle="tab">ADD EXPERT</a></li>

                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="home">
                                    <b>All Experts</b>

                                    <table class="table tabe-responsive">
                                        <thead>
                                        <tr>
                                            <th>Provider Name</th>
                                            <th>ID No.</th>
                                            <th>Work Location</th>
                                            <th>Work Phone No.</th>
                                            <th>Description</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($experts as $expert)
                                            <tr>
                                                <td>
                                                    {{$expert->serviceProvider->service_provider_name}}
                                                </td>
                                                <td>
                                                    <a href="{{url('/expert/'.($expert->id))}}">{{$expert->id_number}}</a>
                                                </td>
                                                <td>
                                                    {{$expert->work_location}}
                                                </td>

                                                <td>
                                                    {{$expert->work_phone_no}}
                                                </td>

                                                <td>
                                                    {{$expert->business_description}}
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    </table>
                                    {!! $experts->render() !!}



                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="adduser">
                                    <b>Add Expert</b>
                                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/expert/new') }}">
                                        {{ csrf_field() }}
                                        <div class="modal-body">
                                            <div class="card">
                                                <div class="body">
                                                    <h2 class="card-inside-title">Expert Details</h2>

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



                                                        <div class="col-md-6 {{ $errors->has('id_number') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">N</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="id_number" autofocus required placeholder="ID Number">
                                                                    {{$errors->first("id_number") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('home_location') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">add_location</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" id="homeLocation" name="home_location" value="{{old('home_location')}}" required placeholder="Where do you live?">
                                                                    {{$errors->first("home_location") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('work_location') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">add_location</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" id="locationOfOperation" name="work_location" value="{{old('work_location')}}" required placeholder="Where do you prefer to work from?">
                                                                    {{$errors->first("work_location") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 {{ $errors->has('work_phone_no') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">local_phone</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="work_phone_no" required placeholder="Work Phone Number">
                                                                    {{$errors->first("work_phone_no") }}
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-12 {{ $errors->has('business_description') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <div class="form-line">
                                                                    <textarea rows="4" class="form-control no-resize" name="business_description"  placeholder="Please describe your business briefly..."></textarea>
                                                                    {{$errors->first("business_description") }}
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
        var homeAutocomplete;
        var input = document.getElementById('locationOfOperation');
        var homeLocation = document.getElementById('homeLocation');
        var options = {
            //types: ['(cities)'],
            componentRestrictions: {country: 'ke'}
        };

        autocomplete = new google.maps.places.Autocomplete(input, options);
        homeAutocomplete = new google.maps.places.Autocomplete(homeLocation, options);

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

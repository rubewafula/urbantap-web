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

                <div class="row">
                    @if (Session::has('message'))
                        <div class="alert alert-info">{{ Session::get('message') }}</div>
                    @endif
                    @if (Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif
                    @if (Session::has('success'))
                        <div class="alert alert-success">{{ Session::get('success') }}</div>
                    @endif

                </div>

                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                {{$business->serviceProvider->service_provider_name}}

                                <br>


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
                            <b>Appointments</b>

                            <table class="table table-responsive">
                                <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($appointments as $appointment)
                                    <tr>
                                        <td>{{optional($appointment->customer)->name}}</td>
                                        <td>{{optional(optional($appointment->providerService)->service)->service_name}}</td>
                                        <td>{{$appointment->date}}</td>
                                        <td>{{$appointment->time}}</td>
                                        <td>
                                            @switch($appointment->status)
                                                @case('BOOKED')
                                                    <a href="javascript:void(0);" onclick="accept_appointment('{{optional($appointment->customer)->name}}','{{$appointment->id}}');" class="btn btn-success btn-block btn-xs waves-effect">Accept</a>
                                                    <a href="javascript:void(0);" onclick="reject_appointment('{{optional($appointment->customer)->name}}','{{$appointment->id}}');" class="btn btn-danger btn-block btn-xs waves-effect">Reject</a>
                                                @break

                                            @case('ACCEPTED')
                                                <span class="label bg-green"> Accepted</span>
                                                @break

                                            @case('CANCELLED')
                                                <span class="label bg-red">Rejected</span>
                                                @break
                                            @default
                                                <span class="label bg-green">Accept</span>
                                                <span class="label bg-red">Reject</span>
                                            @endswitch
                                            {{--<a href="javascript:void(0);" onclick="rm('{{optional($service->service)->service_name}}','{{$service->id}}');">--}}
                                                {{--<span class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span>--}}
                                                {{--&nbsp;Delete</span></a>--}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>

                            {!! $appointments->render() !!}





                        </div>
                    </div>
                </div>
                <div class=" col-md-6 col-sm-6 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                Business

                                <br>

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
                                <li role="presentation" class="active"><a href="#workingHours" data-toggle="tab">WORKING HOURS</a></li>
                                <li role="presentation"><a href="#services" data-toggle="tab">SERVICES</a></li>
                                {{--<li role="presentation"><a href="#portfolio" data-toggle="tab">PORTFOLIO</a></li>--}}
                                <li role="presentation"><a href="#gallery" data-toggle="tab">GALLERY</a></li>

                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="workingHours">
                                    <b>Working Hours</b>

                                    <button type="button" class="btn btn-success right waves-effect m-r-20" data-toggle="modal" data-target="#newWorkingHours">Add new.</button>

                                    <table class="table table-responsive">
                                        <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($operatingHours as $operatingHour)
                                            <tr>
                                                <td>{{$operatingHour->day}}</td>
                                                <td>{{$operatingHour->time_from}}</td>
                                                <td>{{$operatingHour->time_to}}</td>
                                                <td>
                                                <a href="javascript:void(0);" onclick="rm_day('{{$operatingHour->day}}','{{$operatingHour->id}}');">
                                                <span class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span>
                                                &nbsp;Delete</span></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    </table>



                                </div>

                                <div role="tabpanel" class="tab-pane fade" id="services">
                                    <b>Provider Services</b>

                                    <button type="button" class="btn btn-success right waves-effect m-r-20" data-toggle="modal" data-target="#newService">Add new Service.</button>


                                    <table class="table table-responsive">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Cost</th>
                                            <th>Duration (Mins)</th>
                                            <th>Description</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($services as $service)
                                            <tr>
                                                <td>{{optional($service->service)->service_name}}</td>
                                                <td>Ksh. {{$service->cost}}</td>
                                                <td>{{$service->duration}}</td>
                                                <td>{{$service->description}}</td>
                                                <td>
                                                <a href="javascript:void(0);" onclick="rm('{{optional($service->service)->service_name}}','{{$service->id}}');">
                                                <span class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span>
                                                &nbsp;Delete</span></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    </table>

                                </div>

                                <div role="tabpanel" class="tab-pane fade" id="portfolio">
                                    <b>Edit Group</b>
                                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/users/groups/update') }}">
                                        {{ csrf_field() }}
                                        {{--{!! Form::hidden('group_id',$usergroup->id) !!}--}}

                                        <div class="modal-body">
                                            <div class="card">
                                                <div class="body">
                                                    <h2 class="card-inside-title">Group Details</h2>

                                                    <div class="row clearfix">
                                                        <div class="col-md-12 {{ $errors->has('name') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">N</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    {{--{!! Form::text('name',$usergroup->name,['class'=>'form-control','required','autofocus', ]) !!}--}}

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row clearfix">
                                                        <div class="col-md-12 {{ $errors->has('description') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">N</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    {{--{!! Form::text('description',$usergroup->description,['class'=>'form-control','required']) !!}--}}

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success waves-effect">UPDATE</button>
                                        </div>

                                    </form>
                                </div>

                                <div role="tabpanel" class="tab-pane fade" id="gallery">

                                    <div class="row clearfix">
                                        <div class="col-sm-12">
                                            <b>Gallery</b>

                                            <button type="button" class="btn btn-success right waves-effect m-r-20" data-toggle="modal" data-target="#uploadGallery">Add new Image.</button>

                                        </div>
                                    </div>

                                    <div id="aniimated-thumbnials" class="list-unstyled row clearfix display-flex">

                                        @foreach($images as $image)
                                            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                                <a href="{{url($image->image)}}" target="_blank" data-sub-html="image">
                                                    <img class="img-responsive thumbnail" src="{{url($image->image)}}">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>


                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Tab -->


        </div>
    </section>


    <div class="modal fade" id="newService" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="defaultModalLabel">New Service for {{$business->serviceProvider->service_provider_name}}</h4>
                </div>
                <form class="form-horizontal" role="form" method="POST" action="{{ url('/business/services/new') }}">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="card">
                            <div class="body">
                                <h2 class="card-inside-title">Service Details</h2>

                                <input type="hidden" name="provider_id" value="{{$business->serviceProvider->id}}">
                                <div class="row clearfix">
                                    <div class="col-md-12 {{ $errors->has('service_name') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <select name="service_name" class="select2 show-tick" required data-live-search="true">
                                                    <option value="">Select Service</option>
                                                    @foreach(\App\Services::all() as $service)
                                                        <option value="{{$service->id}}">{{$service->service_name}}</option>
                                                    @endforeach
                                                    {{$errors->first("service_name") }}
                                                </select>
                                                {{$errors->first("service_name") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix">
                                    <div class="col-md-12" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <input type="number" name="cost" required class="form-control" placeholder="Cost (Ksh)">
                                                {{$errors->first("cost") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix">
                                    <div class="col-md-12" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <input type="number" name="duration" required class="form-control" placeholder="Duration (Minutes)">
                                                {{$errors->first("duration") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix">
                                    <div class="col-md-12" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <textarea name="description" rows="5" class="form-control" placeholder="Description"></textarea>
                                                {{$errors->first("description") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success waves-effect">Save</button>
                    </div>

                </form>


            </div>
        </div>
    </div>

    <div class="modal fade" id="newWorkingHours" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="defaultModalLabel">New Working hours for {{$business->serviceProvider->service_provider_name}}</h4>
                </div>
                <form class="form-horizontal" role="form" method="POST" action="{{ url('/business/working_hours/new') }}">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="card">
                            <div class="body">
                                <h2 class="card-inside-title">Working hours</h2>

                                <input type="hidden" name="provider_id" value="{{$business->serviceProvider->id}}">
                                <div class="row clearfix">
                                    <div class="col-md-12 {{ $errors->has('day') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <select name="day" class="select2 show-tick" required data-live-search="true">
                                                    <option value="Mon.">Monday</option>
                                                    <option value="Tue.">Tuesday</option>
                                                    <option value="Wed.">Wednesday</option>
                                                    <option value="Thur.">Thursday</option>
                                                    <option value="Fri.">Friday</option>
                                                    <option value="Sat.">Saturday</option>
                                                    <option value="Sun.">Sunday</option>
                                                    {{$errors->first("day") }}
                                                </select>
                                                {{$errors->first("day") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix">
                                    <div class="col-md-12" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <input type="text" name="time_from" required class="timepicker form-control" placeholder="From Time">
                                                {{$errors->first("time_from") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix">
                                    <div class="col-md-12" style="margin-bottom: 0px">
                                        <div class="input-group input-group-sm">
                                            <div class="form-line">
                                                <input type="text" name="time_to" required class="timepicker form-control" placeholder="Time To">
                                                {{$errors->first("time_to") }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success waves-effect">Save</button>
                    </div>

                </form>


            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadGallery" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="defaultModalLabel">Upload Gallery</h4>
                </div>

                <form enctype='multipart/form-data' method='POST' action='{{url('/business/gallery/upload')}}'>
                    {{ csrf_field() }}

                    <input type="hidden" name="provider_id" value="{{$business->serviceProvider->id}}">
                    <div class="modal-body">

                        <input onchange="makeFileList()" name="filesToUpload[]" id="filesToUpload" type="file" required multiple="" />

                        <p>
                        <ul>
                            <li>Accepted formats: jpg/ jpeg</li>
                            <li>Maximum size per file: 5MB</li>
                            <li>Maximum files: 5</li>
                        </ul>

                        <strong>Files You Selected:</strong>
                        </p>
                        <ul id="fileList"><li>No Files Selected</li></ul>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-link waves-effect">UPLOAD</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




@endsection

@section('css')
    <link href="{{url('/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet" />
    <style type="text/css">
        /* highlight col-* */
        /*.row [class*='col-'] {*/
            /*background-color: #cceeee;*/
            /*background-clip: content-box;*/
        /*}*/

        .row.display-flex {
            display: flex;
            flex-wrap: wrap;
        }
        .row.display-flex > [class*='col-'] {
            display: flex;
            flex-direction: column;
        }
    </style>

@endsection
@section('scripts')

    <script src="{{url('/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>

    <script>

        $('.timepicker').bootstrapMaterialDatePicker({
            format: 'HH:mm',
            clearButton: true,
            date: false
        });


        function rm(nm,id){
            bootbox.confirm("Are you sure you want to delete \"" + nm + "\" ? ", function(result) {
                if(result) {

                    $.ajax({
                        url: '/business/services/delete/' + id,
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

        function rm_day(nm,id){
            bootbox.confirm("Remove \"" + nm + "\" from your working days? ", function(result) {
                if(result) {

                    $.ajax({
                        url: '/business/working_hours/delete/' + id,
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


        function accept_appointment(nm,id){
            bootbox.confirm("Accept appointment from \"" + nm + "\" ? ", function(result) {
                if(result) {

                    $.ajax({
                        url: '/business/appointments/accept/' + id,
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


        function reject_appointment(nm,id){
            bootbox.confirm("Reject appointment from \"" + nm + "\" ? ", function(result) {
                if(result) {

                    $.ajax({
                        url: '/business/appointments/reject/' + id,
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
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label"
            });
        });


        function makeFileList() {
            var input = document.getElementById("filesToUpload");
            var ul = document.getElementById("fileList");
            while (ul.hasChildNodes()) {
                ul.removeChild(document.getElementById('fileList').firstChild);
            }
            for (var i = 0; i < input.files.length; i++) {
                var li = document.createElement("li");
                li.innerHTML = input.files[i].name+' - '+bytesToSize(input.files[i].size);
                ul.appendChild(li);
            }
            if(!ul.hasChildNodes()) {
                var li = document.createElement("li");
                li.innerHTML = 'No Files Selected';
                ul.appendChild(li);
            }
        }

        function bytesToSize(bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes === 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }
    </script>
@endsection

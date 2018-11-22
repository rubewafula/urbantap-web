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
                                Services
                                <small>Add, remove, edit services to the system</small>

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
                                <li role="presentation" class="active"><a href="#home" data-toggle="tab">SERVICES</a></li>
                                <li role="presentation"><a href="#adduser" data-toggle="tab">ADD SERVICE</a></li>

                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="home">
                                    <b>All Services</b>

                                    <table class="table tabe-responsive">
                                        <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Service Name</th>
                                            <th>Category</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($services as $service)
                                            <tr>
                                                <td>
                                                    {{$service->id}}
                                                </td>
                                                <td>
                                                    <a href="{{url('/services/'.($service->id))}}">{{$service->service_name}}</a>
                                                </td>
                                                <td>{{$service->category->category_name}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    </table>
                                    {!! $services->render() !!}



                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="adduser">
                                    <b>Add Service</b>
                                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/services/new') }}">
                                        {{ csrf_field() }}
                                        <div class="modal-body">
                                            <div class="card">
                                                <div class="body">
                                                    <h2 class="card-inside-title">Service Details</h2>

                                                    <div class="row clearfix">
                                                        <div class="col-md-4 {{ $errors->has('category_id') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class=" input-group-sm">
                                                                <select name="category_id" class="select2 show-tick" required data-live-search="true">
                                                                    <option value="">Select Category</option>
                                                                    @foreach(\App\Category::all() as $category)
                                                                        <option value="{{$category->id}}">{{$category->category_name}}</option>
                                                                    @endforeach
                                                                </select>
                                                                {{$errors->first("category_id") }}
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <div class="row clearfix">
                                                        <div class="col-md-4 {{ $errors->has('service_name') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">N</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" name="service_name" autofocus required placeholder="Service Name">
                                                                    {{$errors->first("service_name") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row clearfix">
                                                        <div class="col-md-4 {{ $errors->has('service_name') ? ' has-error' : '' }}" style="margin-bottom: 0px">
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
    <script>
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
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label"
            });
        });
    </script>
@endsection

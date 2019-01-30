@extends('layouts.app')

@section('content')

    <section class="content">
        <div class="container-fluid">
        <div class="block-header">
            <h2>
                Rich Box Admin
            </h2>
        </div>

            <!-- Tab -->
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                User Management - Users
                                <small>Add, remove edit users of the portal</small>

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
                                <li role="presentation" class="active"><a href="#adduser" data-toggle="tab">Users > {{$user->name}}</a></li>

                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="adduser">
                                    <b>Add User</b>
                                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/users/profile/update') }}">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="user_id" value="{{$user->id}}">
                                        <div class="modal-body">
                                            <div class="card">
                                                <div class="body">
                                                    <h2 class="card-inside-title">User Details</h2>

                                                    <div class="row clearfix">
                                                        <div class="col-md-12 {{ $errors->has('name') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                            <span class="input-group-addon">
                                                                <i class="material-icons">N</i>
                                                            </span>
                                                                <div class="form-line">
                                                                    <input type="text" class="form-control" value="{{$user->name}}" name="name" autofocus required placeholder="Name eg. Big Shaq">
                                                                    {{$errors->first("name") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <div class="row clearfix">
                                                        <div class="col-md-12" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-addon"><i class="material-icons">attach_money</i></span>
                                                                <div class="form-line">
                                                                    <input type="email" name="email" value="{{$user->email}}" class="form-control" placeholder="E-Mail">
                                                                    {{$errors->first("email") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row clearfix">
                                                        <div class="col-md-4 {{ $errors->has('user_group_id') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <div class="form-line">
                                                                    <label>User Group</label>
                                                                    <select name="user_group" class="form-control show-tick" required data-live-search="true">
                                                                        <option value="{{$user->user_group}}">{{\App\UserGroup::find($user->user_group)->name}}</option>
                                                                        @foreach(\App\UserGroup::all() as $userGroup)
                                                                            <option value="{{$userGroup->id}}">{{$userGroup->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    {{$errors->first("user_group_id") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success waves-effect">Update</button>
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

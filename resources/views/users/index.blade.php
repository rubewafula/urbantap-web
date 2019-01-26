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
                                <li role="presentation" class="active"><a href="#home" data-toggle="tab">USERS</a></li>
                                <li role="presentation"><a href="#adduser" data-toggle="tab">ADD USER</a></li>

                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="home">
                                    <b>All Users</b>

                                    <table class="table tabe-responsive">
                                        <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>E-Mail</th>
                                            <th>User Group</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($users as $user)
                                            <tr>
                                                <td>
                                                    {{$user->id}}
                                                </td>
                                                <td>
                                                    <a href="{{url('/users/profile/'.($user->id))}}">{{$user->name}}</a>
                                                </td>
                                                <td>{{$user->email}}</td>
                                                <td>{{$user->get_user_group ? $user->get_user_group->name : ""}}</td>
                                                <td>
                                                    <a href="javascript:void(0);" onclick="rm('{{$user->name}}','{{$user->id}}');">
                                                        <span class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span>
                                                        &nbsp;Delete</span></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    </table>
                                    {!! $users->appends(['search' => Input::get('search')])->render() !!}



                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="adduser">
                                    <b>Add User</b>
                                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/enroll') }}">
                                        {{ csrf_field() }}
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
                                                                    <input type="text" class="form-control" name="name" autofocus required placeholder="Name eg. Big Shaq">
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
                                                                    <input type="email" name="email" required class="form-control" placeholder="E-Mail">
                                                                    {{$errors->first("email") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row clearfix">
                                                        <div class="col-md-12" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-addon"><i class="material-icons">local_phone</i></span>
                                                                <div class="form-line">
                                                                    <input type="number" name="phone_no" required class="form-control" placeholder="Phone No.">
                                                                    {{$errors->first("phone_no") }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row clearfix">
                                                        <div class="col-md-4 {{ $errors->has('user_group_id') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <div class="form-line">
                                                                    <label>User Group</label>
                                                                    <select id="user-group" name="user_group_id" class="form-control show-tick" required data-live-search="true">
                                                                        <option value="">Select User Group</option>
                                                                        @foreach(\App\UserGroup::where('id','<',100)->get() as $userGroup)
                                                                            <option value="{{$userGroup->id}}">{{$userGroup->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    {{$errors->first("user_group_id") }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div id="org-div" class="col-md-4 {{ $errors->has('org_id') ? ' has-error' : '' }}" style="margin-bottom: 0px">
                                                            <div class="input-group input-group-sm">
                                                                <div class="form-line">
                                                                    <label>Organisation</label>

                                                                    <select class="form-control show-tick"  id="organisation-id" name="org_id"  >
                                                                    </select>
                                                                    {{$errors->first("org_id") }}
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

            var userGroup = $("#user-group");
            var organisation = $("#organisation-id");

            $("#org-div").hide();
            organisation.attr("disabled", true);


            userGroup.on('change', function () {
                organisation.empty();
                organisation.html('');
                $.ajax("{{url('/get_orgs')}}/" + userGroup.val(), {
                    success: function (message) {
                        //console.log(message);
                        $("#org-div").show();
                        var temp = JSON.parse(message);
                        var listItems = "<option value='' disabled>--Select organisation--</option>";
                        $.each(temp, function (i, item) {
                            listItems += '<option value=' + temp[i].id + '>' + temp[i].name + '</option>';
                        });
                        organisation.html(listItems);
                        console.log(listItems);
                        organisation.attr("disabled", false);
                    },
                    error: function (error) {
                        console.log(error);
                        $("#org-div").hide();
                    }
                });
            });



        });




    </script>
@endsection

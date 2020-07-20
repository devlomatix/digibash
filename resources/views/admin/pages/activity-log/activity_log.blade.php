
@extends('admin.layout.admin')

@section('title','Activity Logs')


@section('style')
   <link href="{{asset('public/admin/lib/datatables.net-dt/css/jquery.dataTables.min.css')}}" rel="stylesheet">
   <link href="{{asset('public/admin/lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css')}}" rel="stylesheet">
@endsection


@section('content')

<div class="content-body " id="contentbody">

   <div class="card">
      <div class="d-sm-flex align-items-right justify-content-between mg-b-5 mg-lg-b-5 mg-xl-b-5">
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mg-b-10">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
            </ol>
          </nav>
        </div>
      </div>

      <div class="">

         <div class="d-flex mg-b-20">
            <h4>Activity Logs</h4>
         </div>

         <div class="mg-t-20">
            <div data-label="Example" class="mg-t-15">
                <table id="example2" class="table table-color-primary">
                   <thead>
                   <tr style="padding-left:20px">
                      <th style="" class=""><b>Log Name</b></th>
                      <th style="" class=""><b>Description</b></th>
                      <th style="" class=""><b>Created</b></th>
                      <th style="" class=""><b>Actions</b></th>
                   </tr>
                   </thead>

                   <tbody>
                   </tbody>

                </table>
            </div><!-- df-example -->
         </div>

      </div><!-- row -->
   </div>

</div>

@endsection


@section('modal')



@endsection


@section('javascript')
    <script src="{{asset('public/admin/lib/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('public/admin/lib/datatables.net-dt/js/dataTables.dataTables.min.js')}}"></script>
    <script src="{{asset('public/admin/lib/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('public/admin/lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js')}}"></script>

  	<script>
        $(function(){
         'use strict'

            $('#example2').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('activity.index') !!}',
                columns:[
                    { data: 'log_name', name: 'log_name'},
                    { data: 'description', name: 'description'},
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action' },
                ]
            });


            $(document).on('click','.delete',function(){
                var id =  $(this).attr('id');
                swalWithBootstrapButtons({
                title: "Delete Selected Activity Log?",
                text: "You won't be able to revert this!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
                reverseButtons: true
                }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: "activity/"+id,
                        type:"post",
                        data: {_method: 'delete', _token: "{{ csrf_token() }}"},
                        success: function(result){
                            location.reload();
                            toast({
                            type: "success",
                            title: "Activity Log Deleted Successfully"
                            });
                        }
                    });
                }
                });
            });


        });
  	</script>

@endsection

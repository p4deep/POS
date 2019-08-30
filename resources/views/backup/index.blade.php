@extends('layouts.app')
@section('title', __('lang_v1.backup'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.backup')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
  @if (session('notification') || !empty($notification))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                @if(!empty($notification['msg']))
                    {{$notification['msg']}}
                @elseif(session('notification.msg'))
                    {{ session('notification.msg') }}
                @endif
              </div>
          </div>  
      </div>     
  @endif

  <div class="row">
    <div class="col-sm-12">
      <div class="box">
        <div class="box-body">
            <div class="row">
              <div class="col-sm-4">
                <br/>
                  <a href="{{action('BackUpController@create')}}" class="btn btn-success"><i class="fa fa-download"></i> @lang('lang_v1.download_complete_backup')</a>
                <br/>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>

</section>
@endsection
@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Support Reply')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-1">{{__('Support Reply')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('support.index')}}">{{__('Support')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Support Reply')}}</li>
@endsection
@section('action-btn')
@endsection
@section('filter')
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{$support->subject}}</h6>
                </div>
                @if(!empty($support->descroption))
                    <div class="card-body py-3 flex-grow-1">
                        <p class="text-sm mb-0">
                            {{$support->descroption}}
                        </p>
                    </div>
                @endif
                <div class="card-footer py-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Created By')}}:</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{!empty($support->createdBy)?$support->createdBy->name:''}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Ticket Code')}}:</span>
                                </div>
                                <div class="col-6 text-right">
                                    {{$support->ticket_code}}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Priority')}}:</span>
                                </div>
                                <div class="col-6 text-right">
                                    @if($support->priority == 0)
                                        <span class="badge bg-primary p-1 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                    @elseif($support->priority == 1)
                                        <span class="badge bg-info p-1 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                    @elseif($support->priority == 2)
                                        <span class="badge bg-warning p-1 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                    @elseif($support->priority == 3)
                                        <span class="badge bg-danger p-1 px-3 rounded">   {{ __(\App\Models\Support::$priority[$support->priority]) }}</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <span class="form-control-label">{{__('Status')}}:</span>
                                </div>
                                <div class="col-6 text-right">
                                    @if($support->status == 'Open')
                                        <span class="badge bg-primary p-1 px-3 rounded"> {{__('Open')}}</span>
                                    @elseif($support->status == 'Close')
                                        <span class="badge bg-danger p-1 px-3 rounded">   {{ __('Closed') }}</span>
                                    @elseif($support->priority == 'On Hold')
                                        <span class="badge bg-warning p-1 px-3 rounded">   {{ __('On Hold') }}</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <small>{{__('Start Date')}}:</small>
                                    <div class="h6 mb-0">{{\Auth::user()->dateFormat($support->created_at)}}</div>
                                </div>
                            </div>
                        </li>

                        <li class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-6">
                                    <small>{{__('Description')}}:</small>
                                    <div class="h6 mb-0">{{$support->description}}</div>
                                </div>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        {{-- <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="mt-0 mb-3">{{__('Comments')}}</h5>
                    {{ Form::open(array('route' => array('support.reply.answer',$support->id))) }}
                    <textarea class="form-control form-control-light mb-2" name="description" placeholder="Your comment" id="example-textarea" rows="3" required=""></textarea>
                    <div class="text-right">
                        <div class=" mb-2 ml-2">
                            {{Form::submit(__('Send'),array('class'=>'btn btn-primary btn-sm'))}}
                        </div>
                    </div>
                    {{ Form::close() }}
                    <div class="scrollbar-inner">
                        <div class="list-group list-group-flush support-reply-box">
                            @foreach($replyes as $reply)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <img alt="" class="avatar rounded-circle mr-2" @if(!empty($reply->users) && !empty($reply->users->avatar)) src="{{asset(Storage::url('uploads/avatar/')).'/'.$reply->users->avatar}}" @else  avatar="{{!empty($reply->users)?$reply->users->name:''}}" @endif>
                                        </div>
                                        <div class="flex-fill ml-3">
                                            <div class="h6 text-sm mb-0">{{!empty($reply->users)?$reply->users->name:''}} <small class="float-right text-muted"> {{$reply->created_at}}</small></div>
                                            <p class="text-sm lh-140 mb-0">
                                                {{$reply->description}}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5> {{ __('Support Reply') }}</h5>
                        </div>
                        <div class="card-body">
                           
                            <ul class="list-group team-msg">
                                @foreach($replyes as $reply)
                                    <li class="list-group-item border-0 d-flex align-items-start mb-2">
                                        
                                            <div class="avatar me-3">
                                                <img @if(!empty($reply->users) && !empty($reply->users->avatar)) src="{{asset(Storage::url('uploads/avatar/')).'/'.$reply->users->avatar}}" @else  avatar="{{!empty($reply->users)?$reply->users->name:''}}" @endif
                                                    class="img-user">
                                            </div>
                                            <div class="d-block d-sm-flex align-items-center">
                                                <div class="d-flex align-items-start flex-column justify-content-center mb-3 mb-sm-0">
                                                    <h6 class="mb-1">{{!empty($reply->users)?$reply->users->name:''}}</h6>
                                                    <p class="mb-0 text-sm">{{$reply->description}}</p>
                                                </div>
                                                <ul class="text-end">
                                                    <li class="list-inline-item"><i
                                                            class="f-16 text-primary ti ti-clock"></i>{{$reply->created_at}}
                                                    </li>
                                                   
                                                </ul>
                                            </div>
                                        
                                    </li>
                                    @endforeach
                                {{ Form::open(array('route' => array('support.reply.answer',$support->id))) }}
                                    <li class="list-group-item border-0 d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <img @if(!empty($reply->users) && !empty($reply->users->avatar)) src="{{asset(Storage::url('uploads/avatar/')).'/'.$reply->users->avatar}}" @else  avatar="{{!empty($reply->users)?$reply->users->name:''}}" @endif
                                                class="img-user">
                                        </div>
                                        <div class="form-group mb-0 form-send w-100">
                                            <textarea class="form-control form-control-light mb-2" name="description" placeholder="Your comment" id="example-textarea" rows="3" required=""></textarea>
                                            {{Form::submit(__('Send'),array('class'=>'btn btn-send text-primary'))}} 
                                            {{-- <form type="submit" class="btn btn-send"><i class="f-16 text-primary ti ti-brand-telegram"></i></form> --}}
                                        </div>
                                    </li>
                                {{ Form::close() }}   
                            </ul>
                        </div>
                    </div>
                </div>
           

    </div>
@endsection


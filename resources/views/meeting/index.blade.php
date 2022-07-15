@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Meeting')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Meeting')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('HR')}}</li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Meeting')}}</li>
@endsection
@section('action-btn')
    <a class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="collapse" href="#collapseExample" role="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Filter" aria-expanded="false" aria-controls="collapseExample">
        <i data-bs-toggle="tooltip" data-bs-original-title="{{__('Filter')}}" class="ti ti-filter"></i>
    </a>
    <a href="{{route('meeting.calendar')}}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Calendar View"> <span class="text-white">
        <i class="ti ti-calendar-event text-white"></i></span>
    </a>

    @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')


    {{-- <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('meeting.export') }}"
    data-bs-whatever="{{__('Export meeting CSV file')}}"  data-bs-placement="top">
        <i class="ti ti-file-export text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Export meeting CSV file')}}"></i>
    </a> --}}

    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('meeting.create') }}"
    data-bs-whatever="{{__('Create New Meeting')}}" data-bs-placement="top">
        <i class="ti ti-plus text-white"  data-bs-toggle="tooltip" data-bs-original-title="{{__('Create')}}"></i>
    </a>

    @endif


@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="collapse {{isset($_GET['department'])?'show':''}}" id="collapseExample">
                <div class="card card-body">
                    {{ Form::open(array('url' => 'meeting','method'=>'get')) }}
                    <div class="row filter-css">
                        @if(\Auth::user()->type=='company')
                            <div class="col-md-2">
                                {{ Form::select('department', $departments,isset($_GET['department'])?$_GET['department']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                            </div>
                            <div class="col-md-2">
                                {{ Form::select('designation', $designations,isset($_GET['designation'])?$_GET['designation']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                            </div>
                        @endif
                        <div class="col-auto">
                            {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="col-auto">
                            {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',array('class'=>'form-control'))}}
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <a href="{{route('meeting.index')}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('Reset')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <th>{{__('title')}}</th>
                            <th>{{__('Date')}}</th>
                            <th>{{__('Time')}}</th>
                            <th>{{__('Department')}}</th>
                            <th>{{__('Designation')}}</th>
                            @if(\Auth::user()->type=='company')
                                <th class="text-right" width="200px">{{__('Action')}}</th>
                            @endif
                        </thead>
                        <tbody>
                            @foreach ($meetings as $meeting)
                            <tr>
                                <td>{{ $meeting->title }}</td>
                                <td>{{  \Auth::user()->dateFormat($meeting->date) }}</td>
                                <td>{{  \Auth::user()->timeFormat($meeting->time) }}</td>
                                <td>{{ !empty($meeting->departments)?$meeting->departments->name:'All' }}</td>
                                <td>{{ !empty($meeting->designations)?$meeting->designations->name:'All' }}</td>
                                @if(\Auth::user()->type=='company')
                                    <td class="text-right">
                                        {{-- <a href="#" class="action-item" data-url="{{ route('meeting.edit',$meeting->id) }}" data-ajax-popup="true" data-title="{{__('Edit Meeting')}}" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                            <i class="far fa-edit"></i>
                                        </a> --}}

                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-url="{{ route('meeting.edit',$meeting->id)}}"
                                                data-bs-whatever="{{__('Edit Meeting')}}" data-bs-placement="top" title="Edit"> <span class="text-white"> <i
                                                        class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}" ></i></span></a>
                                        </div>

                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['meeting.destroy', $meeting->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                            {!! Form::close() !!}
                                        </div>

                                        {{-- <a href="#!" class="action-item" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" data-confirm-yes="document.getElementById('delete-form-{{$meeting->id}}').submit();">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['meeting.destroy', $meeting->id],'id'=>'delete-form-'.$meeting->id]) !!}
                                        {!! Form::close() !!} --}}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection


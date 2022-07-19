@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{__('Expenses Place')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Expenses Place')}}</h5>
    </div>
@endsection

@section('action-btn')
    <a href="{{ route('project.grid') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Grid View') }}">
        <i class="ti ti-layout-grid text-white"></i>
    </a>
    @if(\Auth::user()->type=='company')
        <a href="{{ route('place.create') }}" class="btn btn-sm btn-primary btn-icon m-1"
           data-bs-whatever="{{__('Create New Place')}}" data-bs-toggle="tooltip"
           data-bs-original-title="{{__('Create')}}"> <i class="ti ti-plus text-white"></i></a>
    @endif
@endsection
@section('filter')

@endsection
@section('content')


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                        <tr>
                            <th scope="col" class="sort" data-sort="name">{{__('Name')}}</th>
                            <th scope="col" width="80" class="text-right">{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses_place as $expp)
                            <tr>
                                <td>
                                {{ $expp->name }}
                                </td>

                                <td class="text-right">
                                    <div class="action-btn bg-info ms-3">
                                        <a href="{{ route('place.edit',  $expp->id ) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                           data-bs-whatever="{{__('Edit Place')}}" data-bs-toggle="tooltip"
                                           data-bs-original-title="{{__('Edit')}}"> <span class="text-white"> <i
                                                    class="ti ti-edit"></i></span></a>
                                    </div>
                                    <div class="action-btn bg-danger ms-3">
                                        <form action="{{ route('place.destroy', $expp) }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                        </a>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


@endsection




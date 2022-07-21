@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{__('Expenses')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Expenses Edit') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Expenses')}}</li>
    <li class="breadcrumb-item active" aria-current="page">{{__('All Expenses')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
        <a href="#" data-size="lg" data-url="{{ route('expenses.create') }}"data-bs-toggle="modal" data-bs-target="#exampleModal1"
           data-bs-whatever="{{__('Create New Expense')}}"
           class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
    @endif
@endsection
@section('filter')

@endsection
@section('content')



        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
{{--                    <h5>Test</h5><br>--}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                            <tr>
                                <th scope="col" class="sort" data-sort="name">{{__('Name')}}</th>
                                <th scope="col" class="sort" data-sort="code_pay">{{__('Code Pay')}}</th>
                                <th scope="col" width="80" class="text-right">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($newexpenses as $nexp)
                                <tr>
                                    <td>{{ $nexp->name }}</td>
                                    <td>{{ $nexp->code_pay }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('expenses.store') }}" method="post">
                            @csrf
                            @method('post')
                            <div class="row">
                                <div class="form-group  col-md-12">
                                    <label for="">Name *</label>
                                    <input type="text" name="name" class="form-control">

                                    <label for="">Code Pay (Optional)</label>
                                    <input type="text" name="code_pay" class="form-control">

                                    <input type="hidden" name="place_code" value="{{ $place->code }}">
                                </div>
                            </div>
                            <div class="modal-footer pr-0">
                                <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                <button type="submit" class="btn  btn-primary" data-bs-dismiss="modal">{{ __('Create') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>




@endsection




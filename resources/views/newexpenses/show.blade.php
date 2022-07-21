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
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ $place->name }}</h5>
    </div>
@endsection

@section('action-btn')
    @if(\Auth::user()->type=='company')
        <a href="#" data-size="lg" data-url="{{ route('expenses.create') }}"data-bs-toggle="modal" data-bs-target="#exampleModal1"
           data-bs-whatever="{{__('Create New Expense')}}"
           class="btn btn-sm btn-primary btn-icon m-1" style="background-color: green">
            <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
    @endif
@endsection
@section('filter')

@endsection
@section('content')


    @foreach($newexpenses as $nexxp)
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ $nexxp->name }} / Code: {{ $nexxp->code_pay }}</h5><br>
                    <div class="table-responsive">
                        <table class="table" >
                            <thead>
                            <tr>
                                <th scope="col">{{__('Name')}}</th>
                                <th scope="col">{{__('Amount')}}</th>
                                <th scope="col">{{__('Due Date')}}</th>
                                <th scope="col">{{__('Status')}}</th>
                                <th scope="col">{{__('Receipt')}}</th>
                                <th scope="col" width="80" class="text-right">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($newexpensesInfo as $nexxpInfo)
                                @if($nexxp->id == $nexxpInfo->exp_category_id)
                                        <tr>
                                            <td>{{ $nexxpInfo->name }}</td>
                                            <td>{{ $nexxpInfo->amount }}</td>
                                            <td>{{ $nexxpInfo->due_date }}</td>
                                            <td>
                                                @if($nexxpInfo->status == 0)
                                                    <script>
                                                        function mysubmit(){
                                                            document.getElementById('changestatus').submit();
                                                        }
                                                    </script>
                                                    <form action="{{ route('expensesInfo.update', $nexxpInfo->id) }}" method="post" id="changestatus">
                                                        @csrf
                                                        @method('put')
{{--                                                        <span style="color: red">Unpaid</span>--}}
                                                        <button class="btn btn-sm" type="submit"  style="background-color: green; color: white">
                                                        Change to Paid<i class="ti ti-arrow-right text-white" style="color: white !important; " onclick="mysubmit()"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span style="color: Green">Paid</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($nexxpInfo->status == 0 and $nexxpInfo->receipt == NULL)
                                                    <form action="{{ route('expensesInfo.update', $nexxpInfo->id) }}" id="uploadform" method="post" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('put')
                                                        <script>
                                                            function myformsub(){
                                                                document.getElementById("uploadform").submit();// Form submission
                                                            }
                                                        </script>
                                                        <div class="image-upload">
                                                            <label for="file-input">
                                                                <i class="ti ti-upload text-white" style="color: green !important;"></i>
                                                            </label>
                                                            <input id="file-input" name="receipt" type="file" style="display: none" onchange="myformsub()"/>
                                                            <input type="hidden" name="ok" value="1">
                                                        </div>
                                                    </form>
                                                @else
                                                    @if($nexxpInfo->receipt == NULL)
                                                        <form action="{{ route('expensesInfo.update', $nexxpInfo->id) }}" id="uploadform1" method="post" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('put')
                                                            <script>
                                                                function myformsub(){
                                                                    document.getElementById("uploadform1").submit();// Form submission
                                                                }
                                                            </script>
                                                            <div class="image-upload">
                                                                <label for="file-input1">
                                                                    <i class="ti ti-upload text-white" style="color: green !important;"></i>
                                                                </label>
                                                                <input id="file-input1" name="receipt" type="file" style="display: none" onchange="myformsub()"/>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <img src="{{ asset('uploads/attachment/'. $nexxpInfo->receipt) }}" alt="">
                                                        <a href="{{ url('/storage/uploads/attachment/'. $nexxpInfo->receipt) }}" target="_blank">
                                                        <i class="ti ti-eye text-white" style="color: green !important;"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-btn ms-3" style="background-color: red !important;">
                                                    <form action="{{ route('expensesInfo.destroy', $nexxpInfo->id) }}" method="post">
                                                        @csrf
                                                        @method('delete')
                                                        <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('expensesInfo.store') }}" method="post">
                        @csrf
                        @method('post')
                        <div class="row">
                            <div class="form-group  col-md-12">
                                <label for="">Name *</label>
                                <input type="text" name="name" class="form-control">

                                <label for="">Category</label>
                                <select name="exp_category_id" id="" class="form-control">
                                    @foreach($newexpenses as $nexp2)
                                        <option value="{{ $nexp2->id }}">{{ $nexp2->name }}</option>
                                    @endforeach
                                </select>

                                <label for="">Amount</label>
                                <input type="number" class="form-control" name="amount">

                                <label for="">Due Date</label>
                                <input type="date" class="form-control" name="due_date">

                                <input type="hidden" value="{{ $place->code }}" name="place_code">
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

    <script>


    </script>

@endsection




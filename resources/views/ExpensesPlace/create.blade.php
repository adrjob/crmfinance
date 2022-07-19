@extends('layouts.admin')
@section('page-title')
    {{__('Project Create')}}
@endsection
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Project Create')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('project.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Create')}}</li>
@endsection
@section('content')
    <form action="{{ route('place.store') }}" method="post">
        @csrf
        @method('post')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="">Place</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="">Place</label>
                    <input type="date" name="init_date" class="form-control" required value="00/00/00">
                </div>
{{--                <div class="form-group col-md-4">--}}
{{--                    {{ Form::label('price', __('Price'),['class'=>'form-label']) }}--}}
{{--                    {{ Form::number('price', null, array('class' => 'form-control','required'=>'required','stage'=>'0.01')) }}--}}
{{--                </div>--}}
            </div>
            <button type="submit">save</button>
        </div>
    </div>
    </form>

@endsection

<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>



@extends('layouts.admin')
@push('css-page')
@endpush
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush
@push('pre-purpose-script-page')
<script>
    var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: '#useradd-sidenav',
        offset: 300
    })
</script>
<script src="{{asset('assets/js/plugins/tinymce/tinymce.min.js')}}"></script>
<script>
    if ($(".pc-tinymce-2").length) {
        tinymce.init({
            selector: '.pc-tinymce-2',
            height: "400",
            content_style: 'body { font-family: "Inter", sans-serif; }'
        });
    }
</script>
    {{-- <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script> --}}
    <script src="{{ asset('assets/libs/dropzone/dist/min/dropzone.min.js')}}"></script>
    <script>
        var Dropzones = function () {
            var e = $('[data-toggle="dropzone"]'), t = $(".dz-preview");
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            e.length && (Dropzone.autoDiscover = !1, e.each(function () {
                var e, a, n, o, i;
                e = $(this), a = void 0 !== e.data("dropzone-multiple"), n = e.find(t), o = void 0, i = {
                    url: "{{route('deal.file.upload',$deal->id)}}",
                    headers: {
                        'x-csrf-token': CSRF_TOKEN,
                    },
                    thumbnailWidth: null,
                    thumbnailHeight: null,
                    previewsContainer: n.get(0),
                    previewTemplate: n.html(),
                    maxFiles: a ? null : 1,
                    acceptedFiles: a ? null : "image/*",
                    success: function (file, response) {

                        if (response.is_success) {
                            dropzoneBtn(file, response);
                        } else {
                            // Dropzones.removeFile(file);
                            toastrs('Error', response.error, 'error');
                        }
                    },
                    error: function (file, response) {
                        // Dropzones.removeFile(file);
                        if (response.error) {
                            toastrs('Error', response.error, 'error');
                        } else {
                            toastrs('Error', response, 'error');
                        }
                    },
                    init: function () {
                        this.on("addedfile", function (e) {
                            !a && o && this.removeFile(o), o = e
                        })
                    }
                }, n.html(""), e.dropzone(i)
            }))
        }()


        $(document).on("click", ".task-checkbox", function () {
            var chbox = $(this);

            var lbl = chbox.parent().parent().parent().parent().parent().find('.checklist-title');

            $.ajax({
                url: chbox.attr('data-url'),
                data: {_token: $('meta[name="csrf-token"]').attr('content'), status: chbox.val()},
                type: 'post',
                success: function (response) {
                    if (response.is_success) {
                        chbox.val(response.status);
                        if (response.status) {
                            lbl.addClass('strike');
                            lbl.find('.badge').removeClass('badge-warning').addClass('badge-success');
                        } else {
                            lbl.removeClass('strike');
                            lbl.find('.badge').removeClass('badge-success').addClass('badge-warning');
                        }
                        lbl.find('.badge').html(response.status_label);

                        toastrs('Success', response.success, 'success');
                    } else {
                        toastrs('Error', response.error, 'error');
                    }
                },
                error: function (response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        toastrs('Error', response.error, 'error');
                    } else {
                        toastrs('Error', response, 'error');
                    }
                }
            })
        });

    </script>
@endpush
@section('page-title')
    {{__('Deal Detail')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">   {{$deal->name}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('PreSale')}}</li>
    <li class="breadcrumb-item"><a href="{{route('deal.index')}}"> {{__('Deal')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{$deal->name}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
        <div class="col-auto">
            <div class="actions">
                <div class="action-btn bg-warning ms-2">
                    <a href="#" data-url="{{ route('deal.labels',$deal->id) }}"  data-bs-toggle="modal"
                        data-bs-target="#exampleModal" data-bs-whatever="{{__('Add Label')}}"  class="mx-3 btn btn-sm d-inline-flex align-items-center">
                        <i class="ti ti-tag text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Label')}}"></i>
                    </a>
                </div>

                <div class="action-btn bg-info ms-2">
                    <a href="#" data-size="lg" data-url="{{ route('deal.edit',$deal->id) }}" data-bs-toggle="modal"
                        data-bs-target="#exampleModal"  data-title="{{__('Create New Deal')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                        <i class="ti ti-edit text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i>
                    </a>
                </div>

                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['deal.destroy', $deal->id, $deal->id]]) !!}
                    <a href="#!"
                        class="mx-3 btn btn-sm  align-items-center show_confirm ">
                        <i class="ti ti-trash text-white"
                            data-bs-toggle="tooltip"
                            data-bs-original-title="{{ __('Delete') }}"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
                <!-- <div class="action-btn bg-danger ms-2">
                    <form method="POST" action="{{ route('deal.destroy', $deal->id) }}">
                        @csrf
                        <input name="_method" type="hidden" value="DELETE">
                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip"
                        title='Delete'>
                        <span class="text-white">
                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i></span>
                        </button>
                    </form>
                </div> -->
            </div>
        </div>
    @endif
@endsection
@section('content')
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top" style="top:30px">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        <a href="#useradd-1" class="list-group-item list-group-item-action border-0">{{ __('General') }} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-2" class="list-group-item list-group-item-action border-0">{{ __('Task') }} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-3" class="list-group-item list-group-item-action border-0">{{__('Sources')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-4" class="list-group-item list-group-item-action border-0">{{__('Files')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-5" class="list-group-item list-group-item-action border-0">{{__('Discussion')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-6" class="list-group-item list-group-item-action border-0">{{__('Notes')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-7" class="list-group-item list-group-item-action border-0">{{__('Calls')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-8" class="list-group-item list-group-item-action border-0">{{__('Emails')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-9" class="list-group-item list-group-item-action border-0">{{__('Client')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                    </div>
                </div>
            </div>
            @php
                $tasks = $deal->tasks;
                $products = $deal->items1();
                $sources = $deal->sources();
                $calls = $deal->calls;
                $emails = $deal->emails;
                $files = $deal->files;
            @endphp
            <div class="col-xl-9">
                <div id="useradd-1">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-lg-5 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-user-plus"></i>
                                            </div>
                                            <h6 class="mb-3 mt-2">{{__('Task')}}</h6>
                                            <h3 class="mb-0">{{count($tasks)}} </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-click"></i>
                                            </div>
                                            <h6 class="mb-3 mt-2">{{__('Product')}}</h6>
                                            <h3 class="mb-0">{{count($products)}} </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-warning">
                                                <i class="ti ti-file"></i>
                                            </div>
                                            <h6 class="mb-3 mt-2">{{__('Source')}}</h6>
                                            <h3 class="mb-0">{{count($sources)}}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="theme-avtar bg-secondary">
                                                <i class="ti ti-file"></i>
                                            </div>
                                            <h6 class="mb-3 mt-2">{{__('Files')}}</h6>
                                            <h3 class="mb-0">{{count($deal->files)}} </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!--User-->
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-url="{{ route('deal.users.edit',$deal->id) }}"
                                            data-bs-whatever="{{__('Create New User')}}"> <span class="text-white">
                                                <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a>
                                        </p>
                                </div>
                                <h5 class="mb-0">{{ __('Users') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    @foreach($deal->users as $user)
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-sm-auto mb-3 mb-sm-0">
                                                    <div class="d-flex align-items-center ">
                                                        <img @if(!empty($user->avatar)) src="{{asset('storage/uploads/avatar/'.$user->avatar)}}" @else avatar="{{$user->name}}" @endif
                                                        class="avatar  rounded-circle avatar-sm">
                                                        <div class="div">
                                                            <h6 class="m-0 ms-3"> {{$user->name}} </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                                    @if($user->type!='company')
                                                            @if($deal->created_by == \Auth::user()->id )
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['deal.users.destroy',$deal->id,$user->id]]) !!}
                                                                <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm m-2">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}


                                                            </div>
                                                            @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach

                                </ul>
                            </div>
                        </div>

                        <!--Attachment-->
                        <div class="card">
                            <div class="card-header">
                                <h5>{{__('Attachments')}}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush mt-3 w-100">
                                    @foreach($files as $file)
                                        <div class="card mb-3 border shadow-none">
                                            <div class="px-3 py-3">
                                                <div class="row align-items-center">
                                                    <div class="col ml-n2">
                                                        <h6 class="text-sm mb-0">
                                                            <a href="#!">{{$file->file_name}}</a>
                                                        </h6>
                                                        <p class="card-text small text-muted">
                                                            {{number_format(\File::size(storage_path('uploads/deal_files/'.$file->file_path)) / 1048576, 2).' '.__('MB')}}
                                                        </p>
                                                    </div>
                                                    <div class="col-auto actions">
                                                        <a class="action-item" href="{{asset(Storage::url('uploads/deal_files')).'/'.$file->file_path}}" download="" data-toggle="tooltip" data-original-title="{{__('Download')}}">
                                                            <i class="ti ti-download"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['deal.file.delete', $deal->id, $file->id],'id'=>'general_file_delete_form-'.$file->file_name]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                            <i class="ti ti-trash text-white"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    <!-- <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['deal.file.delete',$deal->id,$file->id],'id'=>'general_file_delete_form-'.$file->file_name]) !!}
                                                            @csrf
                                                            <input name="_method" type="hidden" value="DELETE">
                                                            <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2" data-toggle="tooltip"
                                                            title='{{ __('Delete') }}'>
                                                            <span class="text-white"> <i
                                                                class="ti ti-trash text-white"></i></span>
                                                            </button>
                                                        </form>
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Deal Detail')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                {{$deal->name}}
                                            </div>
                                            <div class="col">
                                                @php $labels = $deal->labels() @endphp
                                                @if($labels)
                                                    @foreach($labels as $label)
                                                        <span class="badge bg-{{$label->color}} rounded">{{$label->name}}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6 mt-2">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-success mt-2">
                                                    <i class="ti ti-calendar-stats "></i>
                                                </div>
                                                <div class="ms-2 mt-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Created')}}</p>
                                                    <h6 class="mb-0 text-success">{{\Auth::user()->dateFormat($deal->created_at)}}</h6>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6 my-3 my-sm-0 mt-4">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-info mt-3">
                                                    <i class="ti ti-thumb-up"></i>
                                                </div>
                                                <div class="ms-2 mt-3">
                                                    <p class="text-muted text-sm mb-0">{{__('Price')}}:</p>
                                                    <h6 class="mb-0 text-info">{{\Auth::user()->priceFormat($deal->price)}}</h6>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-6 my-3 my-sm-0 mt-4">
                                            <div class="d-flex align-items-start">
                                                <div class="theme-avtar bg-warning mt-3">
                                                    <i class="ti ti-heart"></i>
                                                </div>
                                                <div class="ms-2 mt-3">
                                                    <p class="text-muted text-sm mb-0">{{__('Pipeline')}}:</p>
                                                    <h6 class="mb-0 text-warning">{{$deal->pipeline->name}}</h6>

                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!--Items-->
                            <div class="card">
                                <div class="card-header">
                                    <div class="float-end">
                                        <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                            <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-url="{{ route('deal.items.edit',$deal->id) }}"
                                            data-bs-whatever="{{__('Create New Item')}}"> <span class="text-white">
                                                <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                            </a></p>
                                    </div>
                                    <h5 class="mb-0">{{ __('Items') }}</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @if($deal->items1())
                                            @foreach($deal->items1() as $product)
                                            <li class="list-group-item px-0">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-sm-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">

                                                            <div class="div">
                                                                <h5 class="m-0">{{$product->name}}</h5>
                                                                <small class="text-muted">{{\Auth::user()->priceFormat($product->sale_price)}}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['deal.items.destroy',$deal->id,$product->id]]) !!}
                                                            <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm m-2">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <!--Activity-->

                            <div id="activity" class="card">
                                <div class="card-header">
                                    <h5>{{__('Activity')}}</h5>
                                </div>
                                <div class="card-body height-450">
                            
                                    <div class="row" style="height:450px !important;overflow-y: scroll;">
                                        <ul class="event-cards list-group list-group-flush mt-3 w-100">
                                            @if(!$deal->activities->isEmpty())
                                            @foreach($deal->activities as $k=>$activity)
                                            @if($activity->log_type == 'Move')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>

                                                @elseif($activity->log_type == 'Add Product')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                                @elseif($activity->log_type == 'Create Invoice')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                                @elseif($activity->log_type == 'Add Contact')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                                @elseif($activity->log_type == 'Create Task')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                                @elseif($activity->log_type == 'Upload File')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                                @elseif($activity->log_type == 'Create Deal Call')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                                @elseif($activity->log_type == 'Create Deal Email')
                                                <li class="list-group-item card mb-3">
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-auto mb-3 mb-sm-0">
                                                            <div class="d-flex align-items-center">
                                                                <div class="theme-avtar bg-primary">
                                                                    <i class="ti ti-activity text-white" class="fas {{ $activity->logIcon() }}"></i>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getRemark() !!}</h6>
                                                                    <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                            
                                                        </div>
                                                    </div>
                                                </li>
                                            @endif
                                            @endforeach
                                            @else
                                                {{ __('No activity found yet.') }}
                                            @endif
                                        </ul>
                                    </div>
                            
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!--Task-->
                <div id="useradd-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-end">
                                <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal" data-url="{{ route('deal.tasks.create',$deal->id) }}"
                                    data-bs-whatever="{{__('Create New Task')}}"data-bs-toggle="tooltip" title="{{ __('Create') }}"
                                    data-bs-original-title="{{__('Create New Task')}}"> <span class="text-white">
                                        <i class="ti ti-plus text-white"></i></span>
                                    </a></p>
                            </div>
                            <h5 class="mb-0">{{ __('Tasks') }}</h5>
                        </div>
                        <div class="card-body">
                            @foreach($tasks as $task)
                                <div class="card card-progress border shadow-none draggable-item">
                                    <div class="card-body row align-items-center">
                                        <div class="col-sm-6 checklist-title {{($task->status)?'strike':''}}">
                                            <a class="h6" href="#" data-toggle="modal"> {{$task->name}}</a>
                                            <div class="actions d-inline-block float-right float-sm-none">
                                                <div class="action-item">
                                                    @if($task->status)
                                                        <div class="badge bg-success p-2 px-3 rounded">{{__(\App\Models\DealTask::$status[$task->status])}}</div>
                                                    @else
                                                        <div class="badge bg-warning p-2 px-3 rounded">{{__(\App\Models\DealTask::$status[$task->status])}}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <br>
                                            <small class="mr-2"> <i class="fas fa-check-circle mr-2"></i>{{__(\App\Models\DealTask::$priorities[$task->priority])}}</small>
                                            <small><i class="fas fa-clock mr-2"></i> {{Auth::user()->dateFormat($task->date)}} {{Auth::user()->timeFormat($task->time)}}</small>
                                        </div>
                                        <div class="col-auto card-meta d-inline-flex align-items-center ml-sm-auto">
                                            @if(\Auth::user()->type=='company')
                                                {{-- <div class="float-end mt-2"> --}}
                                                    <div class="action-btn bg-warning ms-2 float-end">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal" data-url="{{route('deal.tasks.edit',[$deal->id,$task->id])}}"
                                                        data-bs-whatever="{{__('Edit Task')}}"> <span class="text-white">
                                                            <i class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['deal.tasks.destroy',$deal->id,$task->id]]) !!}
                                                        <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm m-2">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    <div class="form-check form-switch custom-switch-v1 ms-2">
                                                        <input type="checkbox" class="form-check-input input-primary task-checkbox" name="task-checkbox" @if($task->status) checked="checked" @endif  value="{{$task->status}}" id="chk-todo-task-{{$task->id}}" value="{{$task->status}}" data-url="{{route('deal.tasks.update_status',[$deal->id,$task->id])}}">
                                                        <label class="form-check-label" for="chk-todo-task-{{$task->id}}"></label>
                                                    </div>

                                                    {{-- <a href="#" data-url="{{route('deal.tasks.edit',[$deal->id,$task->id])}}" data-ajax-popup="true" data-title="{{__('Edit Task')}}" class="action-item" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                        <i class="far fa-edit"></i>
                                                    </a>

                                                    <a href="#!" class="action-item" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?" data-confirm-yes="document.getElementById('task-delete-form-{{$task->id}}').submit();">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <a href="#" class="action-item">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input task-checkbox" name="task-checkbox" @if($task->status) checked="checked" @endif  value="{{$task->status}}" id="chk-todo-task-{{$task->id}}" value="{{$task->status}}" data-url="{{route('deal.tasks.update_status',[$deal->id,$task->id])}}"">
                                                            <label class="custom-control-label form-control-label" for="chk-todo-task-{{$task->id}}"></label>
                                                        </div>
                                                    </a>
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['deal.tasks.destroy',$deal->id,$task->id],'id'=>'task-delete-form-'.$task->id]) !!}
                                                    {!! Form::close() !!} --}}
                                                {{-- </div> --}}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!--Source-->
                <div id="useradd-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-end">
                                <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal" data-url="{{ route('deal.sources.edit',$deal->id) }}"
                                    data-bs-whatever="{{__('Create New Source')}}"> <span class="text-white">
                                        <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                    </a></p>
                            </div>
                            <h5 class="mb-0">{{ __('Sources') }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                @if($sources)
                                    @foreach($sources as $source)
                                    <li class="list-group-item px-0">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-sm-auto mb-3 mb-sm-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="div">
                                                        <h6 class="m-0">{{$source->name}}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-auto text-sm-end d-flex align-items-center">
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['deal.sources.destroy',$deal->id,$source->id]]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <!--Attachment-->
                <div id="useradd-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Deal attachments') }}</h5>
                            <small> {{ __('Drag and drop deal files') }} </small>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="dropzone dropzone-multiple" data-toggle="dropzone" data-dropzone-url="http://" data-dropzone-multiple>
                                    <div class="fallback">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="dropzone-1" multiple>
                                            <label class="custom-file-label" for="customFileUpload">{{__('Choose file')}}</label>
                                        </div>
                                    </div>
                                    <ul class="dz-preview dz-preview-multiple list-group list-group-lg list-group-flush">
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="avatar">
                                                        <img class="rounded" src="" alt="Image placeholder" data-dz-thumbnail>
                                                    </div>

                                                </div>
                                                <div class="col">
                                                    <h6 class="text-sm mb-1" data-dz-name></h6>
                                                    <p class="small text-muted mb-0" data-dz-size></p>
                                                </div>
                                                <div class="col-auto">
                                                    <a href="#" class="dropdown-item" data-dz-remove>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="scrollbar-inner">
                                    <div class="card-wrapper p-3 lead-common-box">
                                        @foreach($files as $file)
                                            <div class="card mb-3 border shadow-none">
                                                <div class="px-3 py-3">
                                                    <div class="row align-items-center">
                                                        <div class="col ml-n2">
                                                            <h6 class="text-sm mb-0">
                                                                <a href="#!">{{$file->file_name}}</a>
                                                            </h6>
                                                            <p class="card-text small text-muted">
                                                                {{number_format(\File::size(storage_path('uploads/lead_files/'.$file->file_path)) / 1048576, 2).' '.__('MB')}}
                                                            </p>
                                                        </div>

                                                        <div class="action-btn bg-warning ">
                                                            <a href="{{asset(Storage::url('uploads/lead_files')).'/'.$file->file_path}}"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center" download=""
                                                            data-bs-toggle="tooltip" title="Download" > <span class="text-white"> <i
                                                                    class="ti ti-download"></i></span></a>
                                                        </div>

                                                        <div class="col-auto actions">
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['lead.file.delete', $deal->id, $file->id],'id'=>'user-delete-form-'.$user->id]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                                    <i class="ti ti-trash text-white"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                            <!-- <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE',  'route' => ['lead.file.delete', $lead->id,$file->id],'id'=>'user-delete-form-'.$user->id]) !!}
                                                                    @csrf
                                                                    <input name="_method" type="hidden" value="DELETE">
                                                                    <button type="submit" class=" show_confirm" data-bs-toggle="tooltip"
                                                                    title='{{ __('Delete') }}'>
                                                                    <span class="text-white"> <i
                                                                        class="ti ti-trash text-white"></i></span>
                                                                    </button>
                                                                </form>
                                                            </div> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Discussion-->
                <div id="useradd-5">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-end">
                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                                    <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-url="{{ route('deal.discussions.create',$deal->id) }}"
                                        data-bs-whatever="{{__('Create New Discussion')}}"> <span class="text-white">
                                            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                        </a>
                                    </p>
                                @endif
                            </div>
                            <h5 class="mb-0">{{ __('Discussion') }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                @foreach($deal->discussions as $discussion)
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <img @if(!empty($discussion->user) && !empty($discussion->user->avatar))
                                                src="{{asset(Storage::url('uploads/avatar')).'/'.$discussion->user->avatar}}" @else
                                                avatar="{{!empty($discussion->user)?$discussion->user->name:''}}" @endif class="avatar  rounded-circle avatar-sm">
                                            </div>
                                            <div class="flex-fill ml-3">
                                                <div class="h6 text-sm mb-0 ms-3">{{!empty($discussion->user)?$discussion->user->name:''}} <small class="float-end text-muted">{{$discussion->created_at->diffForHumans()}}</small></div>
                                                <p class="text-sm lh-140 mb-0 ms-3">
                                                    {{$discussion->comment}}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                            @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!--Notes-->
                <div id="useradd-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Notes') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            {{ Form::open(array('route' => array('deal.note.store',$deal->id))) }}
                            <div class="col-md-12">
                                <div class="form-group mt-3">
                                    {{-- {{ Form::textarea('notes',null, array('class' => 'tox-target pc-tinymce-2','id'=>'pc_demo1')) }} --}}
                                    <textarea class="tox-target pc-tinymce-2" name="notes" id="pc_demo1" rows="8">{!! $deal->notes !!}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12 text-end">
                                <div class="form-group mt-3 me-3">
                                    {{Form::submit(__('Add'),array('class'=>'btn  btn-primary'))}}
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

                <!--Calls-->
                <div id="useradd-7">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-end">
                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                                    <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-size="lg"
                                        data-bs-target="#exampleModal" data-url="{{ route('deal.call.create',$deal->id) }}"
                                        data-bs-whatever="{{__('Create New Call')}}"> <span class="text-white">
                                            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                        </a>
                                    </p>
                                @endif
                            </div>
                            <h5 class="mb-0">{{ __('Calls') }}</h5>
                        </div>
                        <div class="table">
                            <table class="table align-items-center">
                                <thead>
                                <tr>
                                    <th>{{__('Subject')}}</th>
                                    <th>{{__('Call Type')}}</th>
                                    <th>{{__('Duration')}}</th>
                                    <th>{{__('User')}}</th>
                                    @if(\Auth::user()->type=='company')
                                        <th class="text-end">{{__('Action')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($calls as $call)
                                    <tr>
                                        <td>{{ $call->subject }}</td>
                                        <td>{{ ucfirst($call->call_type) }}</td>
                                        <td>{{ $call->duration }}</td>
                                        <td>{{ !empty($call->getLeadCallUser)?$call->getLeadCallUser->name:'--' }}</td>
                                        @if(\Auth::user()->type=='company')
                                            <td class="text-end">
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal" data-url="{{ route('deal.call.edit',[$deal->id,$call->id]) }}"
                                                    data-bs-whatever="{{__('Edit Call')}}"data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-size="lg"
                                                    data-bs-original-title="{{__('Edit Call')}}"> <span class="text-white">
                                                        <i class="ti ti-edit text-white"></i></span>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['deal.call.destroy',$deal->id ,$call->id]]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!--Email-->
                <div id="useradd-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-end">
                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                                    <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-url="{{ route('deal.email.create',$deal->id) }}"
                                        data-bs-whatever="{{__('Create New Email')}}"> <span class="text-white">
                                            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                        </a>
                                    </p>
                                @endif
                            </div>
                            <h5 class="mb-0">{{ __('Email') }}</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                @foreach($emails as $email)
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <img  src=""  avatar="{{$email->to}}"  class="avatar  rounded-circle avatar-sm">
                                            </div>
                                            <div class="flex-fill ml-3">
                                                <div class="h6 text-sm mb-0 ms-3">{{$email->to}} <small class="float-end text-muted">
                                                    {{$email->created_at->diffForHumans()}}</small></div>
                                                <p class="text-sm lh-140 mb-0 ms-3">
                                                    {{$email->subject}}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                            @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!--Client-->
                <div id="useradd-9">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-end">
                                @if(\Auth::user()->type=='company')
                                    <p class="text-muted d-none d-sm-flex align-items-center mb-0">
                                        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal" data-url="{{route('deal.clients.edit',$deal->id)}}"
                                        data-bs-whatever="{{__('Create New Client')}}"> <span class="text-white">
                                            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
                                        </a>
                                    </p>
                                @endif
                            </div>
                            <h5 class="mb-0">{{ __('Client') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="scrollbar-inner">
                                @foreach ($deal->clients as $client)
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <a href="#" class="avatar rounded-circle">
                                                        <img @if(!empty($client->avatar)) src="{{$profile.'/'.$client->avatar}}" @else avatar="{{$client->name}}" @endif class="avatar  rounded-circle avatar-md">
                                                    </a>
                                                </div>
                                                <div class="col ml-n2">
                                                    <a href="#!" class="d-block h6 mb-0">{{ $client->name }}</a>
                                                    <div>
                                                        <small>{{ $client->email }}</small>
                                                    </div>
                                                </div>
                                                @if(\Auth::user()->type=='company')
                                                <div class="col-auto">
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['deal.clients.destroy',$deal->id,$client->id]]) !!}
                                                        <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm m-2">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

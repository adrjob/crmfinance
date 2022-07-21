@extends('layouts.auth')

@php

        $footer_text="Copyright 2022";
@endphp

@push('custom-scripts')
@if(env('RECAPTCHA_MODULE') == 'yes')
{!! NoCaptcha::renderJs() !!}
@endif
@endpush

@section('content')

<!-- [ auth-signup ] start -->

		<div class="card" style="margin-left: 30% !important;">
			<div class="row align-items-center text-start">
				<div class="col-xl-12">
					<div class="card-body">
						<div class="">
							<h2 class="mb-3 f-w-600 text-center">{{ __('Login') }}</h2>
						</div>
						{{Form::open(array('route'=>'login','method'=>'post','id'=>'loginForm','class'=> 'login-form'
						))}}
						<div class="">
							<div class="form-group mb-3">
								<label class="form-label">{{ __('Email') }}</label>
								{{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter Your Email')))}}
								@error('email')
								<span class="error invalid-email text-danger" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror

							</div>
							<div class="form-group mb-3">
								<label class="form-label">{{ __('Password') }}</label>
								{{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter Your Password'),'id'=>'input-password'))}}

								@if (Route::has('password.request'))
								<div class="mb-2 ms-2 mt-3">
									<a href="{{ route('password.request',$lang) }}"
										class="small text-muted text-underline--dashed border-primary">
										{{__('Forgot Your Password?')}}</a>
								</div>
								@endif

								@error('password')
								<span class="error invalid-password text-danger" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror

							</div>


							@if(env('RECAPTCHA_MODULE') == 'yes')
							<div class="form-group col-lg-12 col-md-12 mt-3">
								{!! NoCaptcha::display() !!}
								@error('g-recaptcha-response')
								<span class="small text-danger" role="alert">
									<strong>{{ $message }}</strong>
								</span>
								@enderror
							</div>
							@endif
							<div class="d-grid">
								{{Form::submit(__('Login'),array('class'=>'btn btn-primary btn-block mt-2','id'=>'saveBtn'))}}
							</div>
							{{ Form::close() }}
							@if(Utility::getValByName('SIGNUP') == 'on')
							<p class="my-4 text-center">{{ __('Dont have an account?') }}
									<a href="{{ route('register',$lang) }}" class="my-4 text-primary">{{ __('Register') }}</a>
							</p>
							@endif
						</div>

					</div>
				</div>

			</div>
		</div>
		<div class="auth-footer" style="margin-left: 31%">
			<div class="container-fluid text-center">
				<div class="row">
					<div class="col-6">
						<p class="text-dark">{{ $footer_text }}</p>
					</div>
				</div>
			</div>
		</div>

<!-- [ auth-signup ] end -->

@endsection

{{ Form::open(array('url' => 'account-assets')) }}
<div class="row">
    <div class="form-group col-md-6">
        {{ Form::label('name', __('Name'),['class' => 'col-form-label']) }}
        {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('amount', __('Amount'),['class' => 'col-form-label']) }}
        {{ Form::number('amount', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('purchase_date', __('Purchase Date'),['class' => 'col-form-label']) }}
        {{ Form::date('purchase_date','', array('class' => 'form-control')) }}
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('supported_date', __('Support Until'),['class' => 'col-form-label']) }}
        {{ Form::date('supported_date','', array('class' => 'form-control')) }}
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>'3')) }}
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
    </div>
</div>
{{ Form::close() }}


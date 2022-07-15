{{Form::open(array('url'=>'company-policy','method'=>'post', 'enctype' => "multipart/form-data"))}}
<div class="card-body p-0">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('title',__('Title'),['class' => 'col-form-label'])}}
            {{Form::text('title',null,array('class'=>'form-control','required'=>'required'))}}
        </div>

        <div class="form-group col-md-12">
            {{Form::label('attachment',__('Attachment'),['class' => 'col-form-label'])}}
            {{Form::file('attachment',array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
            {{ Form::textarea('description',null, array('class' => 'form-control','rows'=>3)) }}
        </div>
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>
{{Form::close()}}

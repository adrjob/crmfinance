{{Form::open(array('url'=>'award','method'=>'post'))}}
<div class="card-body p-0">
    <div class="row">
        <div class="form-group col-md-6 col-lg-6 ">
            {{ Form::label('employee_id', __('Employee'),['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees,null, array('class' => 'form-control multi-select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('award_type', __('Award Type'),['class' => 'col-form-label']) }}
            {{ Form::select('award_type', $awardtypes,null, array('class' => 'form-control multi-select','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('date',__('Date'),['class' => 'col-form-label'])}}
            {{Form::date('date',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('gift',__('Gift'),['class' => 'col-form-label'])}}
            {{Form::text('gift',null,array('class'=>'form-control','placeholder'=>__('Enter Gift')))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('description',__('Description'),['class' => 'col-form-label'])}}
            {{Form::textarea('description',null,array('class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Description')))}}
        </div>
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>
{{Form::close()}}


<script src="{{asset('assets/js/plugins/choices.min.js')}}"></script>

<script>
    if ($(".multi-select").length > 0) {
              $( $(".multi-select") ).each(function( index,element ) {
                  var id = $(element).attr('id');
                     var multipleCancelButton = new Choices(
                          '#'+id, {
                              removeItemButton: true,
                          }
                      );
              });
         }
</script>
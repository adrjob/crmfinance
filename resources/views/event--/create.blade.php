{{ Form::open(array('url' => 'event')) }}
<div class="row">
    <div class="form-group col-md-6">
        {{ Form::label('name', __('Event title'),['class' => 'col-form-label']) }}
        {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('where', __('Where'),['class' => 'col-form-label']) }}
        {{ Form::text('where', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('department',__('Department'),['class' => 'col-form-label'])}}
        {{ Form::select('department[]', $departments,null, array('class' => 'form-control multi-select department','id'=>'choices-multiple','multiple'=>'','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{Form::label('employee',__('Employee'),['class' => 'col-form-label'])}}<br>
        <div class="emp_div">
            {{ Form::select('employee[]', [],null, array('class' => 'employee form-control multi-select','id'=>'choices-multiple1','multiple'=>'','required'=>'required')) }}
        </div>
        {{-- <select class="form-control" data-toggle="select" class="multi-select" name="employee[]" id="employee choices-multiple1" placeholder="{{__('Select Employee')}}" multiple> --}}
        {{-- </select> --}}
        <small class="text-muted">{{__('Department is require for employee selection')}}</small>
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_date', __('Start Date'),['class' => 'col-form-label']) }}
        {{ Form::date('start_date', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_time', __('Start Time'),['class' => 'col-form-label']) }}
        {{ Form::time('start_time', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('end_date', __('End Date'),['class' => 'col-form-label']) }}
        {{ Form::date('end_date', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('end_time', __('End Time'),['class' => 'col-form-label']) }}
        {{ Form::time('end_time', '', array('class' => 'form-control','required'=>'required')) }}
    </div>
   
    <div class="form-group col-md-12">
        <label class="form-control-label d-block mb-3">{{__('Status color')}}</label>
        <div class="btn-group btn-group-toggle btn-group-colors event-tag mb-0" data-toggle="buttons">
            <label class="btn bg-info active mr-2">
                <input type="radio" name="color" value="bg-info" autocomplete="off" checked style="display: none; ">
            </label>
            <label class="btn bg-warning mr-2">
                <input type="radio" name="color" value="bg-warning" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-danger mr-2">
                <input type="radio" name="color" value="bg-danger" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-success mr-2">
                <input type="radio" name="color" value="bg-success" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-secondary mr-2">
                <input type="radio" name="color" value="bg-secondary" autocomplete="off" style="display: none">
            </label>
            <label class="btn bg-primary mr-2">
                <input type="radio" name="color" value="bg-primary" autocomplete="off" style="display: none">
            </label>
        </div>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description'),['class' => 'col-form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']) !!}
    </div>
    <div class="modal-footer pr-0">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
    </div>
</div>
{{ Form::close() }}

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
    // var multipleCancelButton = new Choices('#choices-multiple', {
    //         removeItemButton: true,
    //     }
    // );

  </script>



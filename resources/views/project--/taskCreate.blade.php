@if($project_id==0)
    {{ Form::open(array('route' => array('project.task.store',0))) }}
@else
    {{ Form::open(array('route' => array('project.task.store',$project_id))) }}
@endif
<div class="row">

    @if($project_id==0)
        <div class="form-group col-md-12">
            {{ Form::label('title', __('Title')) }}
            {{ Form::text('title', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('project', __('Project')) }}
            {{ Form::select('project', $projects,'', array('class' => 'form-control','data-toggle'=>'select','required'=>'required')) }}
        </div>
    @else
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title')) }}
            {{ Form::text('title', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    @endif
    <div class="form-group col-md-6">
        {{ Form::label('priority', __('Priority')) }}
        {{ Form::select('priority', $priority,'', array('class' => 'form-control','data-toggle'=>'select','required'=>'required')) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('start_date', __('Start Date')) }}
        {{Form::date('start_date',null,array('class'=>'form-control'))}}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('due_date', __('Due Date')) }}
        {{Form::date('due_date',null,array('class'=>'form-control'))}}
    </div>

    @if($project_id==0)
        <div class="form-group col-md-6">
            {{ Form::label('assign_to', __('Assign To')) }}
            <select class="form-control" name="assign_to" id="assign_to" data-toggle="select" required>

            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('milestone_id', __('Milestone')) }}
            <select class="form-control" name="milestone_id" id="milestone_id" data-toggle="select">

            </select>
        </div>
    @else

        <div class="form-group col-md-6">
            {{ Form::label('assign_to', __('Assign To')) }}
            {!! Form::select('assign_to', $users, null,array('class' => 'form-control','data-toggle'=>'select','required'=>'required')) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('milestone_id', __('Milestone')) }}
            {!! Form::select('milestone_id', $milestones, null,array('class' => 'form-control','data-toggle'=>'select')) !!}
        </div>
    @endif
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description')) }}
        {{ Form::textarea('description','', array('class' => 'form-control','rows'=>'3')) }}
    </div>

</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
</div>
{{ Form::close() }}

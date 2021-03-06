<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Mail\LeaveActionSend;
use App\Models\User;
use App\Models\Utility;
use App\Models\Employee;
use Illuminate\Http\Request;

class LeaveController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $employees = User::where('type', 'employee')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');

            if(\Auth::user()->type == 'company')
            {
                $leaves = Leave::where('created_by', '=', \Auth::user()->creatorId());
            }
            else
            {
                $leaves = Leave::where('employee_id', '=', \Auth::user()->id);
            }

            if(!empty($request->employee))
            {
                $leaves->where('employee_id', $request->employee);
            }

            if(!empty($request->start_date))
            {
                $leaves->where('start_date', '>=', $request->start_date);
            }

            if(!empty($request->end_date))
            {
                $leaves->where('end_date', '<=', $request->end_date);
            }

            $leaves = $leaves->get();

            return view('leave.index', compact('leaves', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function create()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $leaveTypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            $leaveTypesDays = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            $employees = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');

            return view('leave.create', compact('employees', 'leaveTypes', 'leaveTypesDays'));
        }


    }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $validator = \Validator::make(
                $request->all(), [

                                   'leave_type' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required|date|after_or_equal:start_date',
                                   'leave_reason' => 'required',
                                   'remark' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $leave = new Leave();
            if(\Auth::user()->type == 'employee')
            {
                $leave->employee_id = \Auth::user()->id;
            }
            else
            {
                $leave->employee_id = $request->employee_id;
            }

            $leave->leave_type       = $request->leave_type;
            $leave->applied_on       = date('Y-m-d');
            $leave->start_date       = $request->start_date;
            $leave->end_date         = $request->end_date;
            $leave->total_leave_days = 0;
            $leave->leave_reason     = $request->leave_reason;
            $leave->remark           = $request->remark;
            $leave->status           = 'Pending';
            $leave->created_by       = \Auth::user()->creatorId();

            $leave->save();

            return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(Leave $leave)
    {
        //
    }


    public function edit(Leave $leave)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $employees = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');
            $leaveTypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('leave.edit', compact('leave', 'employees', 'leaveTypes'));
        }

    }


    public function update(Request $request, Leave $leave)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $validator = \Validator::make(
                $request->all(), [

                                   'leave_type' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'leave_reason' => 'required',
                                   'remark' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $leave->leave_type       = $request->leave_type;
            $leave->start_date       = $request->start_date;
            $leave->end_date         = $request->end_date;
            $leave->total_leave_days = 0;
            $leave->leave_reason     = $request->leave_reason;
            $leave->remark           = $request->remark;

            $leave->save();

            return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Leave $leave)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $leave->delete();

            return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function action($id)
    {
        if(\Auth::user()->type == 'company')
        {
            $leave    = Leave::find($id);
            $employee = User::find($leave->employee_id);

            return view('leave.action', compact('employee', 'leave'));
        }

    }

    public function changeAction(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $leave = Leave::find($request->leave_id);

            $leave->status = $request->status;
            if($leave->status == 'Approve')
            {
                $startDate               = new \DateTime($leave->start_date);
                $endDate                 = new \DateTime($leave->end_date);
                $total_leave_days        = $startDate->diff($endDate)->days;
                $leave->total_leave_days = $total_leave_days;
                $leave->status           = 'Approve';
            }

            $leave->save();
            $employee = Employee::where('user_id',$leave->employee_id)->first();
            $setting  = Utility::settings();
            if(isset($setting['twilio_leave_approve_reject_notification']) && $setting['twilio_leave_approve_reject_notification'] ==1)
            {
                 $msg = __("Your leave has been").' '.$leave->status.'.';
                 Utility::send_twilio_msg($employee->emergency_contact,$msg);
            }
            return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function jsonCount(Request $request)
    {
        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))->leftjoin(
            'leaves', function ($join) use ($request){
            $join->on('leaves.leave_type', '=', 'leave_types.id');
            $join->where('leaves.employee_id', '=', $request->employee_id);
        }
        )->groupBy('leave_types.id')->get();

        return $leave_counts;

    }
}

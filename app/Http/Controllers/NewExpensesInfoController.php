<?php

namespace App\Http\Controllers;

use App\Models\NewExpensesInfo;
use Illuminate\Http\Request;

class NewExpensesInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new NewExpensesInfo;

        $data->name = $request->name;
        $data->amount = $request->amount;
        $data->due_date = $request->due_date;
        $data->place_code = $request->place_code;
        $data->status =  0;
        $data->receipt =  NULL;
        $data->exp_category_id = $request->exp_category_id;

        if ($data->save())
        {
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NewExpensesInfo  $newExpensesInfo
     * @return \Illuminate\Http\Response
     */
    public function show(NewExpensesInfo $newExpensesInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NewExpensesInfo  $newExpensesInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(NewExpensesInfo $newExpensesInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NewExpensesInfo  $newExpensesInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewExpensesInfo $newExpensesInfo, $id)
    {
        $expense = NewExpensesInfo::where('id', $id)->first();

        $filee = $request->file('receipt');



        if($filee)
        {
            if($expense->receipt)
            {
                \File::delete(storage_path('uploads/attachment/' . $expense->receipt));
            }
            $imageName = 'expense_' . time() . "." . $filee->getClientOriginalExtension();
            $filee->storeAs('uploads/attachment', $imageName, ['disk' => 'public']);
            $expense->receipt = $imageName;

            $expense->save();
            return redirect()->back();
        }
        else {



            $data = NewExpensesInfo::find($id);

            $data->status = 1;

            $data->save();
            return redirect()->back();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewExpensesInfo  $newExpensesInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewExpensesInfo $newExpensesInfo, $id)
    {
        if(\Auth::user()->type == 'company')
        {
            $expensesPlace = NewExpensesInfo::where('id', $id)->first();
            $expensesPlace->delete();
            return redirect()->back()->with('success', __('Expense deleted successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

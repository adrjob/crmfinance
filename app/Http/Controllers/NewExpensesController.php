<?php

namespace App\Http\Controllers;

use App\Models\ExpensesPlace;
use App\Models\NewExpenses;
use Illuminate\Http\Request;

class NewExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $data = new NewExpenses;

        $data->name = $request->name;
        $data->place_code = $request->place_code;
        $data->code_pay = $request->code_pay;

        if($data->save()){
            return redirect()->back()->with('success', __('Category successfully created.'));
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NewExpenses  $newExpenses
     * @return \Illuminate\Http\Response
     */
    public function show(NewExpenses $newExpenses, $id)
    {
        $place = ExpensesPlace::where('id', $id)->first();
        $newexpenses = NewExpenses::where('place_code', $place->code)->get();
        $newexpensesInfo = NewExpenses::where('place_code', $place->code)->get();
        return view('newexpenses.show', compact('newexpenses', 'place'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NewExpenses  $newExpenses
     * @return \Illuminate\Http\Response
     */
    public function edit(NewExpenses $newExpenses)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NewExpenses  $newExpenses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewExpenses $newExpenses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewExpenses  $newExpenses
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewExpenses $newExpenses)
    {
        //
    }
}

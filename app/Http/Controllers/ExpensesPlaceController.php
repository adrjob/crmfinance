<?php

namespace App\Http\Controllers;

use App\Models\ExpensesPlace;
use App\Models\NewExpenses;
use Illuminate\Http\Request;

class ExpensesPlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $expenses_place = ExpensesPlace::all();

        return view('ExpensesPlace.index', compact('expenses_place'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ExpensesPlace.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new ExpensesPlace;


        $data->name = $request->name;
        $data->init_date = $request->init_date;
        $data->code = "#". rand(1000,9999);

        if ($data->save()) {
         return redirect()->route('place.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExpensesPlace  $expensesPlace
     * @return \Illuminate\Http\Response
     */
    public function show(ExpensesPlace $expensesPlace)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpensesPlace  $expensesPlace
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpensesPlace $expensesPlace, $id)
    {
        $place = ExpensesPlace::where('id', $id)->first();
        $newexpenses = NewExpenses::where('place_code', $place->code)->get();
        return view('ExpensesPlace.edit', compact('place', 'newexpenses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpensesPlace  $expensesPlace
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ExpensesPlace $expensesPlace)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpensesPlace  $expensesPlace
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpensesPlace $expensesPlace, $id)
    {
        if(\Auth::user()->type == 'company')
        {
            $expensesPlace = ExpensesPlace::where('id', $id)->first();
            $expensesPlace->delete();
            return redirect()->route('place.index')->with('success', __('Place deleted successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

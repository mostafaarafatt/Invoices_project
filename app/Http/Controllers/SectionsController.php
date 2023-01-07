<?php

namespace App\Http\Controllers;

use App\Models\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = Sections::all();
        // if ($sections->count() == 0) {
        //     Sections::truncate();
        // }
        return view('sections.sections', compact('sections'));
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
        // first way of vlidation:

        // $input = $request->all();
        // $db_row = Sections::where('section_name', $input['section_name'])->exists();
        // if ($db_row) {
        //     session()->flash('Error', '!خطأ القسم مسجل مسبقا');
        //     return redirect('/sections');
        // } else {
        //     Sections::create([
        //         'section_name' => $request->section_name,
        //         'description' => $request->description,
        //         'created_by' => Auth::user()->name
        //     ]);
        //     session()->flash('Success', 'تم اضافة القسم بنجاح');
        //     return redirect('/sections');
        // }


        //second way of validation:

        $validate = $request->validate([
            'section_name' => 'required|unique:sections|max:255',
            'description' => 'required',
        ], [
            'section_name.required' => 'يرجى ادخال اسم القسم',
            'section_name.unique' => 'اسم القسم موجود بالفعل',
            'description.required' => 'يرحى ادخال الوصف',
        ]);


        Sections::create([
            'section_name' => $request->section_name,
            'description' => $request->description,
            'created_by' => Auth::user()->name
        ]);
        session()->flash('Success', 'تم اضافة القسم بنجاح');
        return redirect('/sections');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sections  $sections
     * @return \Illuminate\Http\Response
     */
    public function show(Sections $sections)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sections  $sections
     * @return \Illuminate\Http\Response
     */
    public function edit(Sections $sections)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sections  $sections
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $this->validate($request, [
            'section_name' => 'required|max:255|unique:sections,section_name,' . $id,
            'description' => 'required',
        ], [
            'section_name.required' => 'يرجى ادخال اسم القسم',
            'section_name.unique' => 'اسم القسم موجود بالفعل',
            'description.required' => 'يرحى ادخال الوصف',
        ]);

        $sections = Sections::find($id);
        $sections->update([
            'section_name' => $request->section_name,
            'description' => $request->description
        ]);

        session()->flash('edit', 'تم التعديل بنجاح');
        return redirect('/sections');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sections  $sections
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        $id = $request->id;
        Sections::find($id)->delete();

        session()->flash('delete', 'تم الحذف بنجاح');
        return redirect('/sections');
    }
}

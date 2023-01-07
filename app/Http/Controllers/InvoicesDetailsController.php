<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use App\Models\Invoices_attachments;
use App\Models\Invoices_details;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class InvoicesDetailsController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function show(Invoices_details $invoices_details)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // return $id;
        $invoices = Invoices::where('id', $id)->first();
        $detailsInfo = Invoices_details::where('id_Invoice', $id)->get();
        $attachmentsInfo = Invoices_attachments::where('invoice_id', $id)->get();
        return view('invoices.details_invoices', compact('invoices', 'detailsInfo', 'attachmentsInfo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoices_details  $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invoices = Invoices_attachments::findOrFail($request->id_file);
        $invoices->delete();
        Storage::disk('public_view')->delete($request->invoice_number . '/' . $request->file_name);
        session()->flash('delete', 'تم حذف المرفق بنجاح');
        return back();
    }

    public function viewFile($invoice_number, $file_name)
    {
        $files = Storage::disk('public_view')->path($invoice_number . '/' . $file_name);
        return response()->file($files);
    }

    public function downloadFile($invoice_number, $file_name)
    {
        $files = Storage::disk('public_view')->path($invoice_number . '/' . $file_name);
        return response()->download($files);
    }

    public function addAttachment(Request $request){
        return $request;
    }
}




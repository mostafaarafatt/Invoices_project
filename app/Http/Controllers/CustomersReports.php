<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use App\Models\Sections;
use Illuminate\Http\Request;

class CustomersReports extends Controller
{
    public function index()
    {
        $sections = Sections::all();
        return view('reports.customer_reports', compact('sections'));
    }

    public function search_customers(Request $request)
    {

        // في حالة البحث بدون التاريخ
        if (($request->Section || $request->product) && $request->start_at == '' && $request->end_at == '') {

            if ($request->Section == "all") {
                $invoices = Invoices::all();
            } else {
                $invoices = Invoices::where('section_id', '=', $request->Section)->where('product', '=', $request->product)->get();
            }

            $sections = Sections::all();
            return view('reports.customer_reports', compact('sections'))->with('details',$invoices);
        }
        // في حالة البحث بتاريخ
        else {

            $start_at = date($request->start_at);
            $end_at = date($request->end_at);

            if ($request->Section == "all") {
                $invoices = Invoices::whereBetween('invoice_Date', [$start_at, $end_at])->get();
            } else {
                $invoices = Invoices::whereBetween('invoice_Date', [$start_at, $end_at])->where('section_id', '=', $request->Section)->where('product', '=', $request->product)->get();
            }

            $sections = Sections::all();
            return view('reports.customer_reports', compact('sections'))->with('details',$invoices);
        } 

    }
}

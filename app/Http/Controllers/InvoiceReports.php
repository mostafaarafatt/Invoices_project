<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use Illuminate\Http\Request;

class InvoiceReports extends Controller
{
    public function index()
    {
        return view('reports.invoice_reports');
    }

    public function search_invoices(Request $request)
    {
        $radio = $request->radio;
        $type = $request->invoice_type;

        // فى حاله البحث بنوع الفاتورة
        if ($radio == 1) {
            // فى حاله عدم تحديد التاريخ
            if ($request->invoice_type && $request->start_at == '' && $request->end_at == '') {

                if ($request->invoice_type == "all") {
                    $invoices = Invoices::all();
                } else {
                    $invoices = Invoices::where('Status', $request->invoice_type)->get();
                }

                return view('reports.invoice_reports', compact('type'))->with('details', $invoices);
            }
            // فى حالة تحديد التاريخ
            else {
                $start_at = date($request->start_at);
                $end_at = date($request->end_at);

                if ($request->invoice_type == "all") {
                    $invoices = Invoices::whereBetween('invoice_date', [$start_at, $end_at])->get();
                } else {
                    $invoices = Invoices::whereBetween('invoice_date', [$start_at, $end_at])->where('Status', $request->invoice_type)->get();
                }

                return view('reports.invoice_reports', compact('type', 'start_at', 'end_at'))->with('details', $invoices);
            }
        }
        // فى حالة البحث برقم الفاتروة
        else {
            $invoices = Invoices::where('invoice_number', $request->invoice_number)->get();
            return view('reports.invoice_reports')->with('details', $invoices);
        }
    }
}

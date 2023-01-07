<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use App\Models\Invoices_attachments;
use App\Models\Invoices_details;
use App\Models\Sections;
use App\Models\User;
use App\Notifications\InvoicePaid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;
use App\Notifications\AddInvoicesNotification;
use Illuminate\Notifications\DatabaseNotification;

class InvoicesController extends Controller
{


    function __construct()
    {
        $this->middleware('permission:الفواتير المدفوعة', ['only' => ['paid_invoices']]);
        $this->middleware('permission:الفواتير الغير مدفوعة', ['only' => ['unpaid_invoices']]);
        $this->middleware('permission:الفواتير المدفوعة جزئيا', ['only' => ['partialPaid_invoices']]);
        $this->middleware('permission:قائمة الفواتير', ['only' => ['index']]);
        $this->middleware('permission:اضافة فاتورة', ['only' => ['create', 'store']]);
        $this->middleware('permission:تعديل الفاتورة', ['only' => ['edit', 'update']]);
        $this->middleware('permission:حذف الفاتورة', ['only' => ['destroy']]);
        $this->middleware('permission:تغير حالة الدفع', ['only' => ['show', 'statusUpdate']]);
        $this->middleware('permission:تصدير EXCEL', ['only' => ['export']]);
        $this->middleware('permission:طباعةالفاتورة', ['only' => ['print_invoice']]);
        $this->middleware('permission:تغير حالة الدفع', ['only' => ['show', 'statusUpdate']]);
    }

    public function index()
    {
        $invoices = Invoices::all();
        return view('invoices.invoices', compact('invoices'));
    }


    public function create()
    {
        $sections = Sections::all();
        return view('invoices.add_invoice', compact('sections'));
    }


    public function store(Request $request)
    {
        Invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);

        $invoice_Id = Invoices::latest()->first()->id;
        Invoices_details::create([
            'id_Invoice' => $invoice_Id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name)
        ]);

        if ($request->hasFile('pic')) {

            $fileName = $request->file('pic')->getClientOriginalName();
            Invoices_attachments::create([
                'file_name' => $fileName,
                'invoice_number' => $request->invoice_number,
                'Created_by' => (Auth::user()->name),
                'invoice_id' => $invoice_Id
            ]);

            $request->pic->move(public_path('Attachments/' . $request->invoice_number), $fileName);
        }


        // $user=User::first();
        // Notification::send($user,new InvoicePaid($invoice_Id));

        //كدا هيبعت الاشعارات لصاحب الاشعار نفسه فقط وليس اى شخص اخر
        //$user=User::find(Auth::user()->id);

        //كدا هيبعت الاشعارات لكل المستخدمين
        $user=User::get();
        $invoice_Id = Invoices::latest()->first();
        Notification::send($user,new AddInvoicesNotification($invoice_Id));

        session()->flash('Success', 'تم اضافة القسم بنجاح');
        return redirect('/invoices');
    }


    public function show($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }


    public function edit($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        $sections = Sections::all();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));
    }


    public function update(Request $request)
    {
        $invoices = Invoices::where('id', $request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        $invoice_details = Invoices_details::where('id_Invoice', $request->invoice_id);
        $invoice_details->update([
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'note' => $request->note
        ]);

        session()->flash('updated', 'تم التعديل بنجاح');
        return back();
    }


    public function destroy(Request $request)
    {

        $id = $request->id_invoice;
        $invoice = Invoices::where('id', $id)->first();
        $invoice_attachment = Invoices_attachments::where('invoice_id', $id)->first();

        $page_id = $request->page_id;

        if (!$page_id == 2) {
            if (!empty($invoice_attachment->invoice_number)) {
                Storage::disk('public_view')->deleteDirectory($invoice_attachment->invoice_number);
            }
            $invoice->forceDelete();
            session()->flash('delete_invoice_permenantly');
            return redirect('/invoices');
        } else {
            $invoice->delete();
            session()->flash('archive_invoice');
            return redirect('/Archive');
        }
    }

    public function getproducts($id)
    {

        $products = DB::table('products')->where('section_id', $id)->pluck('product_name', 'id');
        return json_encode($products);
    }

    public function statusUpdate($id, Request $request)
    {
        $invoices = Invoices::findOrFail($id);
        if ($request->payment_status === "مدفوعة") {
            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->payment_status,
                'Payment_Date' => $request->payment_date
            ]);
            Invoices_details::create([
                'id_Invoice' => $id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->payment_status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->payment_date,
                'user' => (Auth::user()->name)
            ]);
        } else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->payment_status,
                'Payment_Date' => $request->payment_date
            ]);
            Invoices_details::create([
                'id_Invoice' => $id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->payment_status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->payment_date,
                'user' => (Auth::user()->name)
            ]);
        }

        session()->flash('status_updated');
        return redirect('/invoices');
    }

    public function paid_invoices()
    {
        $invoices = Invoices::where('Value_Status', 1)->get();
        return view('invoices.paid_invoices', compact('invoices'));
    }

    public function unpaid_invoices()
    {
        $invoices = Invoices::where('Value_Status', 2)->get();
        return view('invoices.unpaid_invoices', compact('invoices'));
    }

    public function partialPaid_invoices()
    {
        $invoices = Invoices::where('Value_Status', 3)->get();
        return view('invoices.partialPaid_invoices', compact('invoices'));
    }

    public function print_invoice($id)
    {
        $invoice = Invoices::where('id', $id)->first();
        return view('invoices.print_invoice', compact('invoice'));
    }

    public function export()
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }

    public function get_invoice($id,$notifiId){

        $invoices = Invoices::where('id', $id)->first();
        $detailsInfo = Invoices_details::where('id_Invoice', $id)->get();
        $attachmentsInfo = Invoices_attachments::where('invoice_id', $id)->get();
        DatabaseNotification::find($notifiId)->markAsRead();
        
        return view('invoices.details_invoices', compact('invoices', 'detailsInfo', 'attachmentsInfo'));
        
        
    }
    
    public function read_all_invoices(){
       
        $unreadNotifications = auth()->user()->unreadNotifications;
        if($unreadNotifications){
            $unreadNotifications->markAsRead();
            return back();
        }
    }



}

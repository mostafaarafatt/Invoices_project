<?php

namespace App\Exports;

use App\Models\Invoices;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoicesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
       // return Invoices::select('id','invoice_number','invoice_date')->get();
       return Invoices::all();
    }
    // public function headings(): array{
    //     return ["ID","invoice_number","invoice_date"];
    // }
}

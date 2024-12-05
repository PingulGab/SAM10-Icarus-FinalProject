<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;

class SentimentExport implements FromCollection
{
    public function collection()
    {
        return collect(Session::get('sentiments', []));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToCollection;

class SentimentImport implements ToCollection
{
    public function collection(\Illuminate\Support\Collection $rows)
    {
        if ($rows->isEmpty() || $rows->first()->count() < 5) {
            throw new \Exception("Invalid CSV structure");
        }

        $headers = $rows->first();
        if (!in_array('text', $headers) || !in_array('compound', $headers)) {
            throw new \Exception("Invalid CSV headers");
        }

        $sentiments = Session::get('sentiments', []);
        foreach ($rows->skip(1) as $row) {
            $sentiments[] = [
                'text' => $row[0],
                'neg' => $row[1],
                'neu' => $row[2],
                'pos' => $row[3],
                'compound' => $row[4],
            ];
        }

        Session::put('sentiments', $sentiments);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Sentiment;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Sentiment\Analyzer;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SentimentController extends Controller
{
    use AuthorizesRequests;
    
    public function index()
    {
        $history = Session::get('sentiments', []);
        return view('sentiment', compact('history'));
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'group_id' => 'required|exists:groups,id',
        ]);

        $group = Group::findOrFail($request->group_id);
        $this->authorize('view', $group);

        $analyzer = new Analyzer();
        $result = $analyzer->getSentiment($request->text);

        $compound = $result['compound'];
        $sentiment = 'Neutral';
        if ($compound > 0.05) $sentiment = 'Positive';
        elseif ($compound < -0.05) $sentiment = 'Negative';

        // Save sentiment to the database
        $sentimentModel = $group->sentiments()->create([
            'text' => $request->text,
            'sentiment' => $sentiment,
            'compound' => $compound,
            'created_at' => now(),
        ]);

        // Calculate the updated average compound score
        $averageCompoundScore = $group->sentiments()->avg('compound');

        return response()->json([
            'id' => $sentimentModel->id,
            'text' => $request->text,
            'sentiment' => $sentiment,
            'compound' => $compound,
            'averageCompoundScore' => round($averageCompoundScore, 2), // Return updated average
        ]);
    }

    public function export()
    {
        $sentiments = Sentiment::all();

        // Define the CSV headers
        $csvHeader = ['Text', 'Sentiment', 'Compound'];

        // Map the data to match the headers
        $csvData = $sentiments->map(function ($sentiment) {
            return [
                $sentiment->text,
                $sentiment->sentiment,
                $sentiment->compound,
            ];
        });

        $filename = 'sentiments_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $handle = fopen('php://output', 'w');
        ob_start();

        // Add headers to CSV
        fputcsv($handle, $csvHeader);

        // Add sentiment data rows
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        $content = ob_get_clean();

        // Return the response as a downloadable CSV
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=$filename");
    }

    public function import(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Invalid file format. Please upload a valid CSV file.');
        }

        $file = $request->file('csv_file');
        $handle = fopen($file, 'r');

        // Read and validate CSV headers
        $header = fgetcsv($handle);
        if ($header !== ['Text', 'Sentiment', 'Compound']) {
            return redirect()->back()->with('error', 'CSV format is invalid. The header must match: Text, Sentiment, Compound.');
        }

        // Import data for the current group
        while (($row = fgetcsv($handle)) !== false) {
            Sentiment::create([
                'group_id' => $request->group_id, // Use current group ID
                'text' => $row[0],
                'sentiment' => $row[1],
                'compound' => (float) $row[2],
            ]);
        }

        fclose($handle);

        return redirect()->back()->with('success', 'Sentiments imported successfully.');
    }

    public function destroy($id)
    {
        $sentiment = Sentiment::findOrFail($id);
        $sentiment->delete();

        return redirect()->back()->with('success', 'Sentiment deleted successfully.');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = explode(',', $request->input('selected_sentiments', ''));

        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No sentiments selected.');
        }

        Sentiment::whereIn('id', $selectedIds)->delete();

        return redirect()->back()->with('success', 'Selected sentiments deleted successfully.');
    }
}

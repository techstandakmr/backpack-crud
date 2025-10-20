<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
class FileExtractController extends Controller
{
    // index
    public function index()
    {
        return view('file-uploader');
    }
    public function extract(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'status' => 'error',
                'error' => 'No PDF uploaded.'
            ]);
        }

        // Validate file type
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240', // 10MB max
        ]);

        // Store the uploaded file
        $filePath = $request->file('file')->store('uploads', 'public');
        $absolutePath = storage_path('app/public/' . $filePath);

        // Build the command using full Python path
        $pythonPath = 'C:\\Users\\MK\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        $scriptPath = base_path('python/extract_text.py');

        // Escape paths properly for Windows shell
        $command = "\"$pythonPath\" \"$scriptPath\" \"$absolutePath\" 2>&1";

        // Run Python and capture output
        $output = shell_exec($command);

        // Clean up the uploaded file (optional)
        // Storage::delete('public/' . $filePath);

        if (!$output) {
            return response()->json([
                'status' => 'error',
                'error' => 'No output from Python script.'
            ]);
        }

        // Trim output to remove any extra whitespace
        $output = trim($output);

        // Try to find JSON in the output (in case there are extra messages)
        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
            $output = substr($output, $jsonStart);
        }

        // Decode JSON response from Python
        $data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => 'error',
                'error' => 'Invalid JSON response from Python script.',
                'json_error' => json_last_error_msg(),
                'raw_output' => substr($output, 0, 500) // First 500 chars for debugging
            ]);
        }

        // Return the structured result
        return response()->json($data);
    }
    // table based
    // public function extract(Request $request)
    // {
    //     if (!$request->hasFile('file')) {
    //         return response()->json(['status' => 'error', 'error' => 'No PDF uploaded.']);
    //     }

    //     $filePath = $request->file('file')->store('uploads', 'public');
    //     $absolutePath = storage_path('app/public/' . $filePath);

    //     // Build the command using full Python path
    //     $pythonPath = 'C:\\Users\\MK\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
    //     $scriptPath = base_path('python/extract_text.py');

    //     // Escape paths properly for Windows shell
    //     $command = "\"$pythonPath\" \"$scriptPath\" \"$absolutePath\" 2>&1";

    //     // Run python and capture output
    //     $output = shell_exec($command);

    //     if (!$output) {
    //         return response()->json(['status' => 'error', 'error' => 'No output from Python script.']);
    //     }

    //     // Try to decode JSON output
    //     $result = json_decode($output, true);

    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         // If not valid JSON, return raw output
    //         return response()->json([
    //             'status' => 'error',
    //             'error' => 'Invalid JSON from Python script',
    //             'raw_output' => $output
    //         ]);
    //     }

    //     // Check if extraction was successful
    //     if (!isset($result['success']) || !$result['success']) {
    //         return response()->json([
    //             'status' => 'error',
    //             'error' => $result['error'] ?? 'Unknown error'
    //         ]);
    //     }

    //     // Generate HTML tables for display
    //     $html = $this->generateHtmlTables($result['tables']);

    //     // Return result
    //     return response()->json([
    //         'status' => 'success',
    //         'total_tables' => $result['total_tables'],
    //         'tables' => $result['tables'],
    //         'html' => $html
    //     ]);
    // }

    // private function generateHtmlTables($tables)
    // {
    //     if (empty($tables)) {
    //         return "<p>No tables found in PDF</p>";
    //     }

    //     $html = "";
    //     foreach ($tables as $i => $table) {
    //         $html .= "<div class='table-container mb-4'>";
    //         $html .= "<h4>Table " . ($i + 1) . " (Page {$table['page']}, Method: {$table['method']})</h4>";
    //         $html .= "<div class='table-responsive'>";
    //         $html .= "<table class='table table-bordered'>";

    //         // Headers
    //         $html .= "<thead class='table-dark'><tr>";
    //         foreach ($table['headers'] as $header) {
    //             $html .= "<th>" . htmlspecialchars($header) . "</th>";
    //         }
    //         $html .= "</tr></thead>";

    //         // Data rows
    //         $html .= "<tbody>";
    //         foreach ($table['data'] as $row) {
    //             $html .= "<tr class='text-dark'>";
    //             foreach ($table['headers'] as $header) {
    //                 $value = $row[$header] ?? '';
    //                 $html .= "<td>" . htmlspecialchars($value) . "</td>";
    //             }
    //             $html .= "</tr>";
    //         }
    //         $html .= "</tbody></table>";
    //         $html .= "</div></div>";
    //     }

    //     return $html;
    // }


    // raw text based
    // public function extract(Request $request)
    // {
    //     if (!$request->hasFile('file')) {
    //         return response()->json(['status' => 'error', 'error' => 'No PDF uploaded.']);
    //     }

    //     $filePath = $request->file('file')->store('uploads', 'public');
    //     $absolutePath = storage_path('app/public/' . $filePath);
    //     // build the command using full Python path
    //     $pythonPath = 'C:\\Users\\MK\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
    //     $scriptPath = base_path('python/extract_text.py');

    //     // escape paths properly for Windows shell
    //     $command = "\"$pythonPath\" \"$scriptPath\" \"$absolutePath\"";

    //     // run python and capture output
    //     $output = shell_exec($command);

    //     if (!$output) {
    //         return response()->json(['status' => 'error', 'error' => 'No output from Python script.']);
    //     }

    //     // return result
    //     return response()->json([
    //         'status' => 'success',
    //         'text' => $output
    //     ]);
    // }
}

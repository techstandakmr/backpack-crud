<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FileExtractController extends Controller
{
    // index
    public function index(){
        return view('file-uploader');
    }
    public function extract(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['status' => 'error', 'error' => 'No PDF uploaded.']);
        }
        
        $filePath = $request->file('file')->store('uploads', 'public');
        $absolutePath = storage_path('app/public/' . $filePath);
        // build the command using full Python path
        $pythonPath = 'C:\\Users\\MK\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        $scriptPath = base_path('python/extract_text.py');

        // escape paths properly for Windows shell
        $command = "\"$pythonPath\" \"$scriptPath\" \"$absolutePath\"";

        // run python and capture output
        $output = shell_exec($command);

        if (!$output) {
            return response()->json(['status' => 'error', 'error' => 'No output from Python script.']);
        }

        // return result
        return response()->json([
            'status' => 'success',
            'text' => $output
        ]);
    }
}

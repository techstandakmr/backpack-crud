<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EnrollmentsExport;
use Maatwebsite\Excel\Excel as ExcelExcel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get all courses for dropdown
        $courses = Course::all();

        // Get selected course_id (if any)
        $courseId = $request->input('course_id');
        
        // Get enrollments based on filter
        $query = Enrollment::with('course')
            ->when($courseId, fn($q) => $q->where('course_id', $courseId));
        $enrollments = $query->paginate(10)->appends($request->all());
        return view('admin.reports.index', compact('courses', 'enrollments', 'courseId'));
    }


    public function export(Request $request)
    {
        if ($request->export_type == 'csv') {
            return Excel::download(new EnrollmentsExport($request->course_id), 'enrollments_report.csv', ExcelExcel::CSV);
        } else {
            return Excel::download(new EnrollmentsExport($request->course_id), 'enrollments_report.xlsx');
        };
    }
}

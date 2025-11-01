<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EnrollmentsExport;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $courseId = $request->course_id;
        $exportType = $request->export_type;

        if ($exportType === 'csv') {
            return Excel::download(new EnrollmentsExport($courseId), 'enrollments_report.csv', ExcelExcel::CSV);
        } elseif ($exportType === 'excel') {
            return Excel::download(new EnrollmentsExport($courseId), 'enrollments_report.xlsx');
        } elseif ($exportType === 'pdf') {
            $course = Course::find($courseId);
            $enrollments = Enrollment::with('user')
                ->where('course_id', $courseId)
                ->get();

            $pdf = Pdf::loadView('admin.reports.pdf', compact('enrollments', 'course'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('enrollments_report.pdf');
        }

        return back()->with('error', 'Invalid export type selected.');
    }
}

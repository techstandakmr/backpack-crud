<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EnrollmentsExport implements FromCollection, WithHeadings
{
    protected $courseId;

    public function __construct($courseId)
    {
        $this->courseId = $courseId;
    }

    public function collection()
    {
        // Get enrollments with user data
        return Enrollment::with('user')
            ->where('course_id', $this->courseId)
            ->get()
            ->map(function ($enroll) {
                return [
                    'id' => $enroll->id,
                    'student_name' => $enroll->user->name ?? $enroll->student_name,
                    'student_email' => $enroll->user->email ?? $enroll->student_email,
                    'phone' => $enroll->user->phone ?? $enroll->phone,
                    'created_at' => $enroll->created_at,
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Student Name', 'Email', 'Phone', 'Enrolled At'];
    }
}

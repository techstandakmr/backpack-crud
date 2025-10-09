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
        return Enrollment::where('course_id', $this->courseId)
            ->select('id', 'student_name', 'student_email', 'phone', 'created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['ID', 'Student Name', 'Email', 'Phone', 'Enrolled At'];
    }
}

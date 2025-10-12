<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UserExport implements FromView
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $user = User::with(['coursesEnrolled', 'enrollments.course', 'coursesAuthored'])->findOrFail($this->id);
        return view('admin.users.user_report_excel', compact('user'));
    }
}

<!DOCTYPE html>
<html>
<head>
    <title>Enrollments PDF</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Enrollments List</h2>
    <table>
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Student Name</th>
                <th>Student Email</th>
                <th>Phone</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->course_id }}</td>
                    <td>{{ $enrollment->student_name }}</td>
                    <td>{{ $enrollment->student_email }}</td>
                    <td>{{ $enrollment->phone }}</td>
                    <td>{{ $enrollment->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

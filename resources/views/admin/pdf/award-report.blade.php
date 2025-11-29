{{-- resources/views/admin/pdf/admin-report.blade.php --}}
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>SLEA Awards Report</title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h2 {
            margin: 0 0 4px 0;
            font-size: 18px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .meta {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            text-align: left;
        }

        th {
            background-color: #f3f4f6;
            font-weight: 600;
            font-size: 11px;
        }

        td {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Student Leadership Excellence Awards Report</h2>
    <div class="subtitle">
        List of qualified student leaders and their final compiled scores.
    </div>
    <div class="meta">
        Generated on: {{ $generatedAt->format('F j, Y g:i A') }} <br>
        Total students: {{ $students->count() }}
        @if(isset($filters) && ($filters['college'] || $filters['program'] || $filters['search']))
            <br>
            <strong>Filters Applied:</strong>
            @if($filters['college'])
                College: {{ $filters['college'] }}
            @endif
            @if($filters['program'])
                {{ $filters['college'] ? ' | ' : '' }}Program: {{ $filters['program'] }}
            @endif
            @if($filters['search'])
                {{ ($filters['college'] || $filters['program']) ? ' | ' : '' }}Search: {{ $filters['search'] }}
            @endif
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Student ID</th>
                <th style="width: 30%;">Full Name</th>
                <th style="width: 25%;">College</th>
                <th style="width: 15%;">Program</th>
                <th style="width: 15%;">Total Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                    <tr>
                        {{-- Student ID --}}
                        <td>{{ $student['student_id'] ?? 'N/A' }}</td>

                        {{-- Full Name --}}
                        <td>{{ $student['name'] ?? 'N/A' }}</td>

                        {{-- College --}}
                        <td>{{ $student['college'] ?? 'N/A' }}</td>

                        {{-- Program --}}
                        <td>{{ $student['program'] ?? 'N/A' }}</td>

                        {{-- Total Points: points / max_points --}}
                        <td class="text-right">
                            {{ $student['points_display'] ?? (number_format($student['points'] ?? 0, 2) . '/' . number_format($student['max_points'] ?? 0, 2)) }}
                        </td>
                    </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 12px; color: #6b7280;">
                        No data available for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
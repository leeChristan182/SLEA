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
            @forelse ($students as $row)
                    <tr>
                        {{-- Student ID --}}
                        <td>
                            {{ $row->student_number
                ?? ($row->user->studentAcademic->student_id ?? 'N/A') }}
                        </td>

                        {{-- Full Name --}}
                        <td>{{ $row->user->full_name ?? 'N/A' }}</td>

                        {{-- College --}}
                        <td>
                            {{ $row->college_name
                ?? optional(optional($row->user->studentAcademic)->college)->name
                ?? 'N/A' }}
                        </td>

                        {{-- Program --}}
                        <td>
                            {{ $row->program_code
                ?? optional(optional($row->user->studentAcademic)->program)->code
                ?? 'N/A' }}
                        </td>

                        {{-- RAW SCORE: total_score / max_points --}}
                        <td class="text-right">
                            @php
                                $score = $row->raw_total_score ?? ($row->total_score ?? 0);
                                $max = $row->raw_max_points ?? ($row->max_points ?? 0);
                            @endphp

                            {{ $score }} / {{ $max }}
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
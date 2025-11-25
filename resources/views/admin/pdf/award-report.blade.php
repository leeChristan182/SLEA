<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            @page { margin: 32px; }
            body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; color: #111827; }
            h2 { margin: 0 0 12px 0; font-size: 18px; }
            .meta { font-size: 11px; color: #6b7280; margin-bottom: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
            th { background: #f3f4f6; font-weight: 600; }
            .footer { position: fixed; bottom: 16px; left: 32px; right: 32px; font-size: 10px; color: #6b7280; }
            .program-title { font-size: 16px; font-weight: 600; margin-bottom: 8px; color: #7b0000; }
        </style>
    </head>
    <body>
        <h2>SLEA Recipients Report</h2>
        <div class="meta">
            Generated at: {{ now()->format('Y-m-d H:i') }} | Total Students: {{ count($students) }}
            @if(!empty($filters['college']))
                | College: {{ $filters['college'] }}
            @endif
            @if(!empty($filters['program']))
                | Program: {{ $filters['program'] }}
            @endif
            @if(!empty($filters['search']))
                | Search: {{ $filters['search'] }}
            @endif
        </div>

        @if(count($students) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 15%">Student ID</th>
                    <th style="width: 30%">Student Name</th>
                    <th style="width: 30%">College</th>
                    <th style="width: 15%">Program</th>
                    <th style="width: 10%">Total Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>{{ $student['student_id'] }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td>{{ $student['college'] }}</td>
                        <td>{{ $student['program'] }}</td>
                        <td>{{ $student['points'] }}/100</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No SLEA recipients found matching the selected filters.</p>
        @endif

        <div class="footer">
            SLEA System • Award Report Export • {{ now()->format('Y') }}
        </div>
    </body>
</html>

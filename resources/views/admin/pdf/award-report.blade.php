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
            .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
            .badge--blue { background: #dbeafe; color: #1e40af; }
            .badge--yellow { background: #fef3c7; color: #92400e; }
            .badge--green { background: #d1fae5; color: #065f46; }
            .footer { position: fixed; bottom: 16px; left: 32px; right: 32px; font-size: 10px; color: #6b7280; }
            .program-section { margin-bottom: 20px; }
            .program-title { font-size: 16px; font-weight: 600; margin-bottom: 8px; color: #7b0000; }
        </style>
    </head>
    <body>
        <h2>Award Report Export</h2>
        <div class="meta">
            Generated at: {{ now()->format('Y-m-d H:i') }} | Total Students: {{ count($allStudents) }}
        </div>

        @php
            $btvtedStudents = array_filter($allStudents, function($student) {
                return $student['program'] === 'BTVTED';
            });
            $bpedStudents = array_filter($allStudents, function($student) {
                return $student['program'] === 'BPED';
            });
        @endphp

        @if(count($btvtedStudents) > 0)
        <div class="program-section">
            <h3 class="program-title">BTVTED Program</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%">Student Name</th>
                        <th style="width: 15%">Student ID</th>
                        <th style="width: 15%">Program</th>
                        <th style="width: 15%">Total Points</th>
                        <th style="width: 20%">Leadership Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($btvtedStudents as $student)
                        <tr>
                            <td>{{ $student['name'] }}</td>
                            <td>{{ $student['student_id'] }}</td>
                            <td>{{ $student['program'] }}</td>
                            <td>{{ $student['points'] }}/100</td>
                            <td>
                                @php
                                    $statusClass = '';
                                    switch($student['status']) {
                                        case 'Tracking':
                                            $statusClass = 'badge--blue';
                                            break;
                                        case 'For Final Review':
                                            $statusClass = 'badge--yellow';
                                            break;
                                        case 'SLEA Qualified':
                                            $statusClass = 'badge--green';
                                            break;
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $student['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($bpedStudents) > 0)
        <div class="program-section">
            <h3 class="program-title">BPED Program</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%">Student Name</th>
                        <th style="width: 15%">Student ID</th>
                        <th style="width: 15%">Program</th>
                        <th style="width: 15%">Total Points</th>
                        <th style="width: 20%">Leadership Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bpedStudents as $student)
                        <tr>
                            <td>{{ $student['name'] }}</td>
                            <td>{{ $student['student_id'] }}</td>
                            <td>{{ $student['program'] }}</td>
                            <td>{{ $student['points'] }}/100</td>
                            <td>
                                @php
                                    $statusClass = '';
                                    switch($student['status']) {
                                        case 'Tracking':
                                            $statusClass = 'badge--blue';
                                            break;
                                        case 'For Final Review':
                                            $statusClass = 'badge--yellow';
                                            break;
                                        case 'SLEA Qualified':
                                            $statusClass = 'badge--green';
                                            break;
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $student['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            SLEA System • Award Report Export • {{ now()->format('Y') }}
        </div>
    </body>
</html>

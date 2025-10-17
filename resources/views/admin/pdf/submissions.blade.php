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
            .badge--yellow { background: #fef3c7; color: #92400e; }
            .badge--green { background: #d1fae5; color: #065f46; }
            .badge--red { background: #fee2e2; color: #991b1b; }
            .footer { position: fixed; bottom: 16px; left: 32px; right: 32px; font-size: 10px; color: #6b7280; }
        </style>
    </head>
    <body>
        <h2>Submission Export</h2>
        <div class="meta">
            Generated at: {{ now()->format('Y-m-d H:i') }} | Filter: {{ request('filter', '—') }} | Search: {{ request('q', '—') }} | Sort: {{ request('sort', '—') }} | Page: {{ request('page', 1) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 28%">Student</th>
                    <th style="width: 32%">Document Title</th>
                    <th style="width: 18%">Category</th>
                    <th style="width: 12%">Date</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 8%">Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $s)
                    <tr>
                        <td>
                            <div><strong>{{ optional($s->student)->name ?? '—' }}</strong></div>
                            <div style="color:#6b7280; font-size: 11px;">ID: {{ optional($s->student)->student_id ?? '—' }} | Program: {{ optional($s->student)->program ?? '—' }}</div>
                        </td>
                        <td>{{ $s->document_title ?? '—' }}</td>
                        <td>{{ $s->slea_section ?? '—' }}</td>
                        <td>{{ optional($s->submitted_at)->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @php $status = strtolower($s->status ?? 'pending'); @endphp
                            <span class="badge {{ $status === 'pending' ? 'badge--yellow' : ($status === 'approved' ? 'badge--green' : 'badge--red') }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td>{{ number_format($s->final_score ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#6b7280;">No submissions found for current filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            SLEA System • Submission Oversight Export • {{ now()->format('Y') }}
        </div>
    </body>
    </html>


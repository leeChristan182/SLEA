@php
    $categoryKey = 'community';
    $category = App\Models\RubricCategory::with(['sections.subsections'])
        ->where('key', $categoryKey)
        ->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">IV. COMMUNITY INVOLVEMENT</h4>

    <p class="rubric-category-description">
        This criterion refers to the student's participation in community activities, civic involvement or
        public service in local, regional, national, and international levels.
    </p>

    @if(!$category || $category->sections->isEmpty())
        <p class="text-muted text-center">No sections found for this category.</p>
    @else
        @foreach($category->sections as $section)
            @php
                $rowCount = max($section->subsections->count(), 1);
            @endphp

            <div class="table-wrap">
                <table class="guide-table">
                    <thead>
                        <tr>
                            <th>POSITION/TITLE</th>
                            <th>MAX 20 POINTS</th>
                            <th>EVIDENCE NEEDED</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section->subsections as $index => $sub)
                            @php
                                // 1) Base numeric values
                                $points = $sub->max_points ?? $sub->cap_points;

                                // 2) If still null and this is rate-based, use rate/day
                                $pointsDisplay = null;
                                if (!is_null($points)) {
                                    $pointsDisplay = rtrim(rtrim(number_format($points, 2), '0'), '.');
                                } else {
                                    $scoreParams = $sub->score_params ?? [];
                                    $rate = $scoreParams['rate'] ?? null;

                                    if ($sub->scoring_method === 'rate' && $rate !== null) {
                                        $pointsDisplay = rtrim(rtrim(number_format($rate, 2), '0'), '.') . '/day';
                                    }
                                }

                                // Evidence/notes fallback: subsection → section
                                $evidenceSource = $sub->evidence_needed ?: $section->evidence;
                            @endphp

                            <tr>
                                <td>{{ $sub->sub_section }}</td>
                                <td class="points-cell">{{ $pointsDisplay !== null ? $pointsDisplay : '—' }}</td>
                                @if($index === 0)
                                    <td rowspan="{{ $rowCount }}">
                                        @if(!empty($evidenceSource))
                                            <div class="evidence-notes-content">
                                                @foreach(explode("\n", $evidenceSource) as $idx => $line)
                                                    @if(trim($line) !== '')
                                                        @if($idx > 0)
                                                            <br>
                                                        @endif
                                                        - {{ $line }}
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>


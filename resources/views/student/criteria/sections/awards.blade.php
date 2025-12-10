@php
    $categoryKey = 'awards';
    $category = App\Models\RubricCategory::with(['sections.subsections'])
        ->where('key', $categoryKey)
        ->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">III. AWARDS/RECOGNITION RECEIVED</h4>

    <p class="rubric-category-description">
        This criterion shows co-curricular and extracurricular activities the student has participated
        throughout their stay in the university.
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
                            <th>POINTS MAX 20 POINTS</th>
                            <th>EVIDENCE NEEDED</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section->subsections as $index => $sub)
                            @php
                                // Show max_points if present, else cap_points
                                $points = $sub->max_points ?? $sub->cap_points;
                                $pointsDisplay = !is_null($points)
                                    ? rtrim(rtrim(number_format($points, 2), '0'), '.')
                                    : null;

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


@php
    $categoryKey = 'academic';
    $category = App\Models\RubricCategory::with(['sections.subsections.options'])
        ->where('key', $categoryKey)
        ->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">II. ACADEMIC EXCELLENCE</h4>

    <p class="rubric-category-description">
        This criterion shows the academic standing of the candidate for the whole duration of their
        leadership. This shows that the student has managed its time efficiently to balance academics
        and extracurricular activities.
    </p>

    @if(!$category || $category->sections->isEmpty())
        <p class="text-muted text-center">No sections found for this category.</p>
    @else
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
                    @foreach($category->sections as $section)
                        @php
                            $subsections = $section->subsections;
                            $rowCount = max($subsections->count(), 1);
                            $sectionEvidence = $section->evidence ?? '';
                        @endphp

                        @foreach($subsections as $index => $sub)
                            @php
                                // 1) Base numeric values
                                $points = $sub->max_points ?? $sub->cap_points;

                                // 2) If still null and there are options, use the max option points (e.g. GWA = 20)
                                if (is_null($points) && $sub->relationLoaded('options')) {
                                    $maxOption = $sub->options->max('points');
                                    if (!is_null($maxOption)) {
                                        $points = $maxOption;
                                    }
                                }

                                $pointsDisplay = !is_null($points)
                                    ? rtrim(rtrim(number_format($points, 2), '0'), '.')
                                    : null;

                                // 3) Evidence/notes fallback: subsection → section
                                $evidenceSource = $sub->evidence_needed ?: $sectionEvidence;
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>


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
                $sectionPrinted = false;
            @endphp

            <div class="table-wrap">
                <table class="manage-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Subsection</th>
                            <th>Max Points</th>
                            <th>Evidence Needed</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section->subsections as $sub)
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
                                $notesSource = $sub->notes ?: $section->notes;
                            @endphp

                            <tr>
                                @if(!$sectionPrinted)
                                    <td rowspan="{{ $rowCount }}"><strong>{{ $section->title }}</strong></td>
                                    @php $sectionPrinted = true; @endphp
                                @endif

                                <td>{{ $sub->sub_section }}</td>
                                <td>{{ $pointsDisplay !== null ? $pointsDisplay : '—' }}</td>

                                {{-- Evidence --}}
                                <td>
                                    @if(!empty($evidenceSource))
                                        <div class="evidence-notes-content">
                                            @foreach(explode("\n", $evidenceSource) as $index => $line)
                                                @if(trim($line) !== '')
                                                    @if($index > 0)
                                                        <br><br>
                                                    @endif
                                                    {{ $line }}
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Notes --}}
                                <td>
                                    @if(!empty($notesSource))
                                        <div class="evidence-notes-content">
                                            @foreach(explode("\n", $notesSource) as $index => $line)
                                                @if(trim($line) !== '')
                                                    @if($index > 0)
                                                        <br><br>
                                                    @endif
                                                    {{ $line }}
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>




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
                    @foreach($category->sections as $section)
                        @php
                            $subsections = $section->subsections;
                            $rowCount = max($subsections->count(), 1);
                            $sectionPrinted = false;
                        @endphp

                        @foreach($subsections as $sub)
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>




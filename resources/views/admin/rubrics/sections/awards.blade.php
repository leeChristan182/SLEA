@php
    $categoryKey = 'awards';
    $category = App\Models\RubricCategory::with(['sections.subsections'])
        ->where('key', $categoryKey)
        ->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">III. AWARDS/RECOGNITION RECEIVED</h4>

    <p class="rubric-category-description">
        <strong>Certificate of Grades (Portal Generated)</strong><br>
        From first year to 1st Sem of this A.Y<br><br>
        This criterion shows co-curricular and extracurricular activities the student has participated
        throughout their stay in the university.
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section->subsections as $sub)
                            @php
                                // Show max_points if present, else cap_points
                                $points = $sub->max_points ?? $sub->cap_points;
                                $pointsDisplay = !is_null($points)
                                    ? rtrim(rtrim(number_format($points, 2), '0'), '.')
                                    : null;

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

                                <td>
                                    <div class="action-buttons-group">
                                        <button class="btn-edit" title="Edit" onclick="openEditSubsectionModal(
                                                            {{ $sub->sub_section_id }},
                                                            {{ $sub->section_id }},
                                                            '{{ addslashes($sub->sub_section) }}',
                                                            {{ $sub->max_points ?? '' }},
                                                            '{{ addslashes($sub->evidence_needed ?? '') }}',
                                                            '{{ addslashes($sub->notes ?? '') }}',
                                                            {{ $sub->order_no ?? '' }}
                                                        )">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn-delete" title="Delete" onclick="openDeleteSubsectionModal(
                                                            {{ $sub->sub_section_id }},
                                                            '{{ addslashes($sub->sub_section) }}'
                                                        )">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
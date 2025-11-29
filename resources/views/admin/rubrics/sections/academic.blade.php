@php
$categoryKey = 'academic';
$category = App\Models\RubricCategory::with(['sections.subsections'])->where('key', $categoryKey)->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">II. ACADEMIC EXCELLENCE</h4>

    <p class="rubric-category-description">
        This criterion shows the academic standing of the candidate for the whole duration of their
        leadership. This shows that the student has managed its time efficiently to balance academics
        and extracurricular activities.
    </p>

    @if($category->sections->isEmpty())
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
                    <th>Actions</th>
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
                <tr>
                    @if(!$sectionPrinted)
                    <td rowspan="{{ $rowCount }}"><strong>{{ $section->title }}</strong></td>
                    @php $sectionPrinted = true; @endphp
                    @endif

                    <td>{{ $sub->sub_section }}</td>
                    <td>{{ $sub->max_points ?? '—' }}</td>

                    <td>
                        @if(!empty($sub->evidence_needed))
                        <div class="evidence-notes-content">
                            @foreach(explode("\n", $sub->evidence_needed) as $index => $line)
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
                        @if(!empty($sub->notes))
                        <div class="evidence-notes-content">
                            @foreach(explode("\n", $sub->notes) as $index => $line)
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
                            <button
                                class="btn-edit"
                                title="Edit"
                                onclick="openEditSubsectionModal(
                                    {{ $sub->sub_section_id }},
                                    {{ $sub->section_id }},
                                    '{{ addslashes($sub->sub_section) }}',
                                    {{ $sub->max_points ?? '' }},
                                    '{{ addslashes($sub->evidence_needed ?? '') }}',
                                    '{{ addslashes($sub->notes ?? '') }}',
                                    {{ $sub->order_no ?? '' }}
                                )"
                            >
                                <i class="fas fa-edit"></i>
                            </button>

                            <button
                                class="btn-delete"
                                title="Delete"
                                onclick="openDeleteSubsectionModal(
                                    {{ $sub->sub_section_id }},
                                    '{{ addslashes($sub->sub_section) }}'
                                )"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

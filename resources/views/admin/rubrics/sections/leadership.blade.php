@php
    $leadershipCategory = $categories->firstWhere('key', 'leadership');

    // If leadershipSections is null, show all sections (for single page display)
    // Otherwise, show only the specified sections (for chunked display)
    if ($leadershipSections === null) {
        $sections = $leadershipCategory?->sections ?? collect();
    } else {
        $sections = $leadershipSections;
    }
@endphp

<div class="rubric-section" data-category="leadership">
    <h4 class="rubric-heading">I. LEADERSHIP EXCELLENCE</h4>

    <p class="rubric-category-description">
        This criterion covers the leadership experience of the applicant for having assumed
        elective/appointive positions in the campus/community organizations and having participated in
        co-curricular and extracurricular activities such as but not limited to competitions, conferences,
        seminar, workshops, etc.
    </p>

    @if($sections->isEmpty())
        <p class="text-muted text-center">No leadership sections found.</p>
    @else
        @foreach ($sections as $section)
            @php
                $subsections = $section->subsections ?? collect();
            @endphp

            @if($subsections->isNotEmpty())
                <div class="subsection mb-4">
                    <h5 class="subsection-title"><strong>{{ $section->title }}</strong></h5>

                    @php
                        // For section D (Training), combine all subsections into one table
                        $isTrainingSection = strpos($section->title, 'Training') !== false || strpos($section->title, 'Seminars') !== false || strpos($section->title, 'Conferences') !== false;
                    @endphp

                    @if($isTrainingSection)
                        {{-- Special handling for D. Training section - show all subsections in one table --}}
                        <div class="table-wrap">
                            <table class="manage-table training-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Position / Title</th>
                                        <th>Points</th>
                                        <th>Evidence Needed</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subsections as $subsection)
                                        @php
                                            // score_params is already cast to array in the model
                                            $scoreParams = $subsection->score_params ?? [];
                                            $rate = $scoreParams['rate'] ?? null;
                                            $capPoints = $subsection->cap_points ?? null;
                                            $evidenceLines = preg_split("/\r\n|\n|\r/", $subsection->evidence_needed ?? '');
                                            $notesLines = preg_split("/\r\n|\n|\r/", $subsection->notes ?? '');
                                        @endphp
                                        <tr>
                                            <td class="training-category"><strong>{{ $subsection->sub_section }}</strong></td>
                                            <td class="training-position">{{ $subsection->sub_section }}</td>
                                            <td class="training-points">
                                                @if($rate)
                                                    <span class="points-line">{{ rtrim(rtrim(number_format($rate, 1), '0'), '.') }}/day</span>
                                                    @if($capPoints)
                                                        <br><span class="points-line">(max {{ $capPoints }} points)</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="training-evidence">
                                                @if(!empty($subsection->evidence_needed))
                                                    <div class="evidence-notes-content">
                                                        @foreach ($evidenceLines as $index => $line)
                                                            @if(trim($line) !== '')
                                                                @if($index > 0)
                                                                    <br><br>
                                                                @endif
                                                                {{ $line }}
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    Certificate of Attendance/Appreciation/Participation
                                                @endif
                                            </td>
                                            <td class="training-notes">
                                                @if(!empty($subsection->notes))
                                                    <div class="evidence-notes-content">
                                                        @foreach ($notesLines as $index => $line)
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
                                                                                {{ $subsection->sub_section_id }},
                                                                                {{ $subsection->section_id }},
                                                                                '{{ addslashes($subsection->sub_section) }}',
                                                                                {{ $subsection->max_points ?? '' }},
                                                                                '{{ addslashes($subsection->evidence_needed ?? '') }}',
                                                                                '{{ addslashes($subsection->notes ?? '') }}',
                                                                                {{ $subsection->order_no ?? '' }}
                                                                            )">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button class="btn-delete" title="Delete" onclick="openDeleteSubsectionModal(
                                                                                {{ $subsection->sub_section_id }},
                                                                                '{{ addslashes($subsection->sub_section) }}'
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
                    @else
                        {{-- Regular sections (A, B, C) - show each subsection in its own table --}}
                        @foreach ($subsections as $subsection)
                            @php
                                $positions = $subsection->options ?? collect();
                                $evidenceLines = preg_split("/\r\n|\n|\r/", $subsection->evidence_needed ?? '');
                                $notesLines = preg_split("/\r\n|\n|\r/", $subsection->notes ?? '');

                                // For rate-based subsections (like D. Training), show the subsection itself
                                $isRateBased = $subsection->scoring_method === 'rate';
                                // score_params is already cast to array in the model
                                $scoreParams = $subsection->score_params ?? [];
                                $rate = $scoreParams['rate'] ?? null;
                                $capPoints = $subsection->cap_points ?? null;

                                // If rate-based and no options, create a display row
                                if ($isRateBased && $positions->isEmpty()) {
                                    $positions = collect([
                                        (object) [
                                            'id' => null,
                                            'label' => $subsection->sub_section,
                                            'points' => $rate,
                                            'order_no' => null
                                        ]
                                    ]);
                                }

                                $rowCount = max($positions->count(), 1);
                            @endphp

                            @if($positions->isNotEmpty() || $isRateBased)
                                <div class="table-wrap">
                                    <table class="manage-table">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Position / Title</th>
                                                <th>Points</th>
                                                <th>Evidence Needed</th>
                                                <th>Notes</th>
                                                @if(!$isRateBased)
                                                    <th>Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($positions as $index => $pos)
                                                <tr>
                                                    @if($index === 0)
                                                        <td rowspan="{{ $rowCount }}"><strong>{{ $subsection->sub_section }}</strong></td>
                                                    @endif
                                                    <td>
                                                        @if($isRateBased && $index === 0)
                                                            {{ $subsection->sub_section }}
                                                        @else
                                                            {{ $pos->label }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($isRateBased && $index === 0)
                                                            @if($rate)
                                                                {{ rtrim(rtrim(number_format($rate, 1), '0'), '.') }}/day
                                                                @if($capPoints)
                                                                    (max {{ $capPoints }} points)
                                                                @endif
                                                            @endif
                                                        @else
                                                            {{ rtrim(rtrim(number_format($pos->points, 2), '0'), '.') }}
                                                        @endif
                                                    </td>
                                                    @if($index === 0)
                                                        <td rowspan="{{ $rowCount }}">
                                                            @if(!empty($subsection->evidence_needed))
                                                                <div class="evidence-notes-content">
                                                                    @foreach ($evidenceLines as $idx => $line)
                                                                        @if(trim($line) !== '')
                                                                            @if($idx > 0)
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
                                                        <td rowspan="{{ $rowCount }}">
                                                            @if(!empty($subsection->notes))
                                                                <div class="evidence-notes-content">
                                                                    @foreach ($notesLines as $idx => $line)
                                                                        @if(trim($line) !== '')
                                                                            @if($idx > 0)
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
                                                    @endif
                                                    @if(!$isRateBased)
                                                        <td>
                                                            <div class="action-buttons-group">
                                                                <button class="btn-edit" title="Edit" onclick="openEditRubricModal(
                                                                                                {{ $pos->id }},
                                                                                                {{ $subsection->sub_section_id }},
                                                                                                '{{ addslashes($pos->label) }}',
                                                                                                {{ $pos->points ?? 0 }},
                                                                                                {{ $pos->order_no ?? '' }},
                                                                                                '{{ addslashes($subsection->notes ?? '') }}'
                                                                                            )">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>

                                                                <button class="btn-delete" title="Delete" onclick="openDeleteRubricModal(
                                                                                                {{ $pos->id }},
                                                                                                'Leadership',
                                                                                                '{{ addslashes($pos->label) }}'
                                                                                            )">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            @endif
        @endforeach
    @endif
</div>
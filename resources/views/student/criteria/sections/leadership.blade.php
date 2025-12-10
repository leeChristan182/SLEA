@php
    /** @var \Illuminate\Support\Collection|\App\Models\RubricCategory[] $categories */

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
                        $isTrainingSection =
                            strpos($section->title, 'Training') !== false ||
                            strpos($section->title, 'Seminars') !== false ||
                            strpos($section->title, 'Conferences') !== false;
                    @endphp

                    @if($isTrainingSection)
                        {{-- Special handling for D. Training section - show all subsections in one table --}}
                        @php
                            // Get section-level evidence (same for all subsections)
                            $sectionEvidence = $section->evidence ?? '';
                            $evidenceLines = preg_split("/\r\n|\n|\r/", $sectionEvidence ?? '');
                            $totalRows = $subsections->count();
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
                                    @php
                                        $evidenceRowPrinted = false;
                                    @endphp
                                    @foreach ($subsections as $subsection)
                                        @php
                                            // score_params is already cast to array in the model
                                            $scoreParams = $subsection->score_params ?? [];
                                            $rate = $scoreParams['rate'] ?? null;
                                            $capPoints = $subsection->cap_points ?? null;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $subsection->sub_section }}</strong></td>
                                            <td class="points-cell">
                                                @if($rate)
                                                    {{ rtrim(rtrim(number_format($rate, 1), '0'), '.') }}/day
                                                    @if($capPoints)
                                                        <br>
                                                        <small>(max {{ rtrim(rtrim(number_format($capPoints, 2), '0'), '.') }} points)</small>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            @if(!$evidenceRowPrinted)
                                                <td rowspan="{{ $totalRows }}" class="merged-evidence-cell" style="vertical-align: top !important; border-bottom: none !important;">
                                                    @if(!empty($sectionEvidence))
                                                        <div class="evidence-notes-content">
                                                            @foreach ($evidenceLines as $index => $line)
                                                                @if(trim($line) !== '')
                                                                    @if($index > 0)
                                                                        <br>
                                                                    @endif
                                                                    - {{ $line }}
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        Certificate of Attendance/Appreciation/Participation
                                                    @endif
                                                </td>
                                                @php $evidenceRowPrinted = true; @endphp
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- Regular sections (A, B, C) - show all subsections in one table with Subsection column --}}
                        @php
                            // Check if this is section A (Campus-Based) or section C (Community-Based)
                            $isSectionA = strpos($section->title, 'Campus-Based') !== false || strpos($section->title, 'Campus Based') !== false;
                            $isSectionC = strpos($section->title, 'Community-Based') !== false || strpos($section->title, 'Community Based') !== false;
                            $showSubsectionColumn = $isSectionA || $isSectionC;
                            
                            // Calculate total rows for evidence column
                            $totalRows = 0;
                            foreach ($subsections as $subsection) {
                                $positions = $subsection->options ?? collect();
                                $isRateBased = $subsection->scoring_method === 'rate';
                                if ($isRateBased && $positions->isEmpty()) {
                                    $totalRows += 1;
                                } else {
                                    $totalRows += max($positions->count(), 1);
                                }
                            }
                            
                            // Get section-level evidence (same for all subsections in a section)
                            $sectionEvidence = $section->evidence ?? '';
                            $evidenceLines = preg_split("/\r\n|\n|\r/", $sectionEvidence ?? '');
                        @endphp

                        <div class="table-wrap">
                            <table class="guide-table {{ $showSubsectionColumn ? 'has-subsection-column' : '' }}">
                                <thead>
                                    <tr>
                                        @if($showSubsectionColumn)
                                            <th>SUBSECTION</th>
                                        @endif
                                        <th>POSITION/TITLE</th>
                                        <th>MAX 20 POINTS</th>
                                        <th>EVIDENCE NEEDED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $evidenceRowPrinted = false;
                                    @endphp
                                    @foreach ($subsections as $subsection)
                                        @php
                                            $positions = $subsection->options ?? collect();
                                            $isRateBased = $subsection->scoring_method === 'rate';
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
                                                        'order_no' => null,
                                                    ],
                                                ]);
                                            }

                                            $rowCount = max($positions->count(), 1);
                                        @endphp

                                        @if($positions->isNotEmpty() || $isRateBased)
                                            @foreach ($positions as $index => $pos)
                                                <tr>
                                                    @if($showSubsectionColumn)
                                                        @if($index === 0)
                                                            <td rowspan="{{ $rowCount }}" class="subsection-cell">
                                                                <strong>{{ $subsection->sub_section }}</strong>
                                                            </td>
                                                        @endif
                                                    @endif
                                                    <td>
                                                        @if($isRateBased && $index === 0)
                                                            {{ $subsection->sub_section }}
                                                        @else
                                                            {{ $pos->label }}
                                                        @endif
                                                    </td>
                                                    <td class="points-cell">
                                                        @if($isRateBased && $index === 0)
                                                            @if($rate)
                                                                {{ rtrim(rtrim(number_format($rate, 1), '0'), '.') }}/day
                                                                @if($capPoints)
                                                                    <br>
                                                                    <small>(max {{ rtrim(rtrim(number_format($capPoints, 2), '0'), '.') }} points)</small>
                                                                @endif
                                                            @endif
                                                        @else
                                                            {{ rtrim(rtrim(number_format($pos->points, 2), '0'), '.') }}
                                                        @endif
                                                    </td>
                                                    @if(!$evidenceRowPrinted)
                                                        <td rowspan="{{ $totalRows }}" class="merged-evidence-cell" style="vertical-align: top !important; border-bottom: none !important;">
                                                            @if(!empty($sectionEvidence))
                                                                <div class="evidence-notes-content">
                                                                    @foreach ($evidenceLines as $idx => $line)
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
                                                        @php $evidenceRowPrinted = true; @endphp
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    @endif
</div>


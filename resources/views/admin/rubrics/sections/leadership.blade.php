@php
    $leadershipCategory = $categories->firstWhere('key', 'leadership');

    $allSections = $leadershipCategory?->sections ?? collect();

    // For paginated leadership pages
    $sections = $leadershipSections ?? $allSections;
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">I. Leadership</h4>

    @if(!empty($leadershipCategory?->description))
        <p class="rubric-category-description">{{ $leadershipCategory->description }}</p>
    @endif

    @if($sections->isEmpty())
        <p class="text-muted text-center">No leadership sections found.</p>
    @else
        <table class="manage-table">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Subsection</th>
                    <th>Position / Role</th>
                    <th>Points</th>
                    <th>Evidence Needed</th>
                    <th>Notes</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($sections as $section)

                    @php
                        $subsections = $section->subsections ?? collect();

                        $sectionRowCount = $subsections->sum(fn($sub) =>
                            max(($sub->options ?? collect())->count(), 1)
                        );

                        $sectionPrinted = false;
                    @endphp

                    @foreach ($subsections as $subsection)

                        @php
                            $positions = $subsection->options ?? collect();
                            $rowCount  = max($positions->count(), 1);

                            $evidenceLines = preg_split("/\r\n|\n|\r/", $subsection->evidence_needed ?? '');
                            $notesLines    = preg_split("/\r\n|\n|\r/", $subsection->notes ?? '');
                        @endphp

                        @forelse ($positions as $index => $pos)
                        <tr>

                            {{-- SECTION --}}
                            @if(!$sectionPrinted)
                                <td rowspan="{{ $sectionRowCount }}">
                                    {{ $section->title }}
                                </td>
                                @php $sectionPrinted = true; @endphp
                            @endif

                            {{-- SUBSECTION --}}
                            @if($index === 0)
                                <td rowspan="{{ $rowCount }}">
                                    {{ $subsection->sub_section }}
                                </td>
                            @endif

                            {{-- POSITION / ROLE --}}
                            <td>{{ $pos->label }}</td>

                            {{-- POINTS --}}
                            <td>
                                {{ rtrim(rtrim(number_format($pos->points, 2), '0'), '.') }}
                            </td>

                            {{-- EVIDENCE --}}
                            @if($index === 0)
                                <td rowspan="{{ $rowCount }}">
                                    @if(!empty($subsection->evidence_needed))
                                        <ul class="mb-0">
                                            @foreach ($evidenceLines as $line)
                                                @if(trim($line) !== '')
                                                    <li>{{ $line }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- NOTES --}}
                                <td rowspan="{{ $rowCount }}">
                                    @if(!empty($subsection->notes))
                                        <ul class="mb-0">
                                            @foreach ($notesLines as $line)
                                                @if(trim($line) !== '')
                                                    <li>{{ $line }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif

                            {{-- ACTIONS --}}
                            <td>
                                <button
                                    class="btn btn-disable"
                                    onclick="openEditRubricModal(
                                        {{ $pos->id }},
                                        'Leadership',
                                        '{{ $pos->label }}',
                                        {{ $pos->points ?? 0 }},
                                        5,
                                        @json($subsection->evidence_needed)
                                    )"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button
                                    class="btn btn-delete"
                                    onclick="openDeleteRubricModal(
                                        {{ $pos->id }},
                                        'Leadership',
                                        '{{ $pos->label }}'
                                    )"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>

                        </tr>
                        @empty

                        {{-- SUBSECTION WITH NO POSITIONS --}}
                        <tr>
                            @if(!$sectionPrinted)
                                <td rowspan="{{ $sectionRowCount }}">{{ $section->title }}</td>
                                @php $sectionPrinted = true; @endphp
                            @endif

                            <td>{{ $subsection->sub_section }}</td>
                            <td colspan="4" class="text-center text-muted">
                                No leadership positions listed.
                            </td>
                            <td></td>
                        </tr>

                        @endforelse

                    @endforeach
                @endforeach

            </tbody>
        </table>
    @endif
</div>

<style>
.manage-table tbody tr:hover {
    background: transparent;
}
</style>

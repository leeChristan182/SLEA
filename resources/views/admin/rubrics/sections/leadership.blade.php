@php
$leadershipCategory = $categories->firstWhere('key', 'leadership');
$sections = $leadershipCategory?->sections ?? collect();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">I. Leadership</h4>

    {{-- Category description --}}
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
                <th>Max Points</th>
                <th>Evidence Needed</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sections as $section)
            @php
            $subsections = $section->subsections;
            $sectionRowCount = $subsections->sum(fn($sub) => max($sub->leadershipPositions->count(), 1));
            $sectionPrinted = false;
            @endphp

            @foreach($subsections as $subsection)
            @php
            $positions = $subsection->leadershipPositions;
            $rowCount = max($positions->count(), 1);
            @endphp

            @forelse($positions as $index => $pos)
            <tr>
                {{-- Section column rowspan --}}
                @if(!$sectionPrinted)
                <td rowspan="{{ $sectionRowCount }}">{{ $section->title }}</td>
                @php $sectionPrinted = true; @endphp
                @endif

                {{-- Subsection column rowspan --}}
                @if($index === 0)
                <td rowspan="{{ $rowCount }}">{{ $subsection->sub_section }}</td>
                @endif

                <td>{{ $pos->position }}</td>
                <td>{{ number_format($pos->points, 2) }}</td>
                <td>{{ $section->max_points ?? '5' }}</td>

                {{-- Evidence rowspan --}}
                @if($index === 0)
                <td rowspan="{{ $rowCount }}">
                    @if(!empty($subsection->evidence_needed))
                    <ul class="mb-0">
                        @foreach(explode("\n", $subsection->evidence_needed) as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @else
                    —
                    @endif
                </td>
                @endif

                {{-- Notes rowspan (section-level, show only once per section) --}}
                @if(!$section->notes_printed ?? false)
                <td rowspan="{{ $sectionRowCount }}">
                    @if(!empty($section->notes))
                    <ul class="mb-0">
                        @foreach(explode("\n", $section->notes) as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @else
                    —
                    @endif
                </td>
                @php $section->notes_printed = true; @endphp
                @endif

                <td>
                    <a href="{{ route('admin.rubrics.leadership.edit', $pos->id) }}"
                        class="btn btn-disable" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.rubrics.leadership.destroy', $pos->id) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this leadership position?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                @if(!$sectionPrinted)
                <td rowspan="{{ $sectionRowCount }}">{{ $section->title }}</td>
                @php $sectionPrinted = true; @endphp
                @endif
                <td>{{ $subsection->sub_section }}</td>
                <td colspan="6" class="text-muted text-center">No leadership positions listed</td>
            </tr>
            @endforelse
            @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- Add new leadership position forms per section --}}
    @foreach($sections as $section)
    <div class="mt-3">
        <h6>Add New Leadership Position ({{ $section->title }})</h6>
        <form action="{{ route('admin.rubrics.leadership.store') }}" method="POST">
            @csrf
            <input type="hidden" name="section_id" value="{{ $section->section_id }}">
            <div class="form-row">
                <select name="sub_section_id" class="form-control" required>
                    <option value="">Select Subsection</option>
                    @foreach ($section->subsections as $sub)
                    <option value="{{ $sub->sub_items }}">{{ $sub->sub_section }}</option>
                    @endforeach
                </select>

                <input type="text" name="position" placeholder="Position / Role" class="form-control" required>
                <input type="number" name="points" placeholder="Points" class="form-control" step="0.1" min="0" required>
                <input type="number" name="position_order" placeholder="Order No." class="form-control" min="1" required>
                <button type="submit" class="btn btn-disable" title="Add">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </form>
    </div>
    @endforeach
    @endif
</div>
<style>
    .manage-table tbody tr:hover {
        background-color: transparent;
    }
</style>
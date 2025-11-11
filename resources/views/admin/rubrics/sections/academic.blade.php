@php
$categoryKey = 'academic';
$category = App\Models\RubricCategory::with(['sections.subsections'])->where('key', $categoryKey)->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">{{ $category->order_no }}. {{ $category->title }}</h4>

    {{-- Category description --}}
    @if(!empty($category->description))
    <p class="rubric-category-description">{{ $category->description }}</p>
    @endif

    @if($category->sections->isEmpty())
    <p class="text-muted text-center">No sections found for this category.</p>
    @else
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
            $rowCount = max($subsections->count(), 1); // rowspan for section
            $sectionPrinted = false;
            @endphp

            @foreach($subsections as $sub)
            <tr>
                {{-- Section column rowspan --}}
                @if(!$sectionPrinted)
                <td rowspan="{{ $rowCount }}">{{ $section->title }}</td>
                @php $sectionPrinted = true; @endphp
                @endif

                <td>{{ $sub->sub_section }}</td>
                <td>{{ $sub->max_points ?? '—' }}</td>

                {{-- Evidence --}}
                <td>
                    @if(!empty($sub->evidence_needed))
                    <ul class="mb-0">
                        @foreach(explode("\n", $sub->evidence_needed) as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @else
                    —
                    @endif
                </td>

                {{-- Notes --}}
                <td>
                    @if(!empty($sub->notes))
                    <ul class="mb-0">
                        @foreach(explode("\n", $sub->notes) as $line)
                        <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @else
                    —
                    @endif
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<style>
    .manage-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }

    .manage-table th,
    .manage-table td {
        border: 1px solid #dee2e6;
        padding: 0.5rem;
        font-size: 0.9rem;
        vertical-align: top;
    }

    .manage-table th {
        background-color: #f8f9fa;
        text-align: left;
    }

    /* Optional: remove row hover highlight if needed */
    .manage-table tbody tr:hover {
        background-color: transparent;
    }
</style>
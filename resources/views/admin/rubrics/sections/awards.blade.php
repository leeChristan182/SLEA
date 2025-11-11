@php
$categoryKey = 'awards';
$category = App\Models\RubricCategory::with(['sections.subsections'])->where('key', $categoryKey)->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">{{ $category->order_no }}. {{ $category->name }}</h4>

    @foreach($category->sections as $section)
    @php
    $rowCount = max($section->subsections->count(), 1);
    $sectionPrinted = false;
    @endphp

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
            <tr>
                {{-- Section rowspan --}}
                @if(!$sectionPrinted)
                <td rowspan="{{ $rowCount }}">{{ $section->title }}</td>
                @php $sectionPrinted = true; @endphp
                @endif

                <td>{{ $sub->sub_section }}</td>
                <td>{{ $sub->max_points }}</td>

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
        </tbody>
    </table>
    @endforeach
</div>
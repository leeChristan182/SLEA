@php
    $categoryKey = 'conduct';
    $category = App\Models\RubricCategory::with(['sections.subsections.options'])
        ->where('key', $categoryKey)
        ->first();
@endphp

<div class="rubric-section">
    <h4 class="rubric-heading">V. GOOD CONDUCT</h4>

    <p class="rubric-category-description">
        Refers to the student's behavior during their stay in the university.
    </p>

    @if(!$category || $category->sections->isEmpty())
        <p class="text-muted text-center">No sections found for this category.</p>
    @else
        @foreach($category->sections as $section)

            <div class="table-wrap">
                <table class="manage-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Offense Type</th>
                            <th>Points</th>
                            <th>Evidence Needed</th>
                            <th>Notes</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($section->subsections as $sub)
                            @php
                                // fallback for evidence/notes
                                $evidenceSource = $sub->evidence_needed ?: $section->evidence;
                                $notesSource    = $sub->notes ?: $section->notes;

                                $options = $sub->options ?? collect();
                                $rowCount = max($options->count(), 1);
                                $printedSection = false;
                            @endphp

                            @if($options->isNotEmpty())
                                {{-- SHOW ALL OFFENSE OPTIONS --}}
                                @foreach($options as $opt)
                                    <tr>
                                        {{-- SECTION NAME, print once --}}
                                        @if(!$printedSection)
                                            <td rowspan="{{ $rowCount }}"><strong>{{ $section->title }}</strong></td>
                                            @php $printedSection = true; @endphp
                                        @endif

                                        {{-- OPTION LABEL (Minor / Major Infraction) --}}
                                        <td>{{ $opt->label }}</td>

                                        {{-- OPTION POINTS --}}
                                        <td>{{ rtrim(rtrim(number_format($opt->points, 2), '0'), '.') }}</td>

                                        {{-- Evidence --}}
                                        @if($loop->first)
                                            <td rowspan="{{ $rowCount }}">
                                                @if(!empty($evidenceSource))
                                                    <div class="evidence-notes-content">
                                                        @foreach(explode("\n", $evidenceSource) as $i => $line)
                                                            @if(trim($line) !== '')
                                                                @if($i > 0) <br><br> @endif
                                                                {{ $line }}
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else —
                                                @endif
                                            </td>

                                            {{-- Notes --}}
                                            <td rowspan="{{ $rowCount }}">
                                                @if(!empty($notesSource))
                                                    <div class="evidence-notes-content">
                                                        @foreach(explode("\n", $notesSource) as $i => $line)
                                                            @if(trim($line) !== '')
                                                                @if($i > 0) <br><br> @endif
                                                                {{ $line }}
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else —
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach

                            @else
                                {{-- NO OPTIONS, fallback single row --}}
                                <tr>
                                    <td><strong>{{ $section->title }}</strong></td>
                                    <td>{{ $sub->sub_section }}</td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td>—</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>

                </table>
            </div>

        @endforeach
    @endif
</div>




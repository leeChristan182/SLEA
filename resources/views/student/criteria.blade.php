@extends('layouts.app')
@section('title', 'Criteria & Point System')

@section('content')
<div class="Criteria">
    <div class="container">
        @include('partials.sidebar')

        <main class="main-content">


            <div class="toolbar">
                <div class="left-tools">
                    <button class="chip active" data-cat="all">All</button>
                    <button class="chip" data-cat="leadership">Leadership</button>
                    <button class="chip" data-cat="academic">Academic</button>
                    <button class="chip" data-cat="awards">Awards</button>
                    <button class="chip" data-cat="community">Community</button>
                    <button class="chip" data-cat="conduct">Conduct</button>
                    <button id="resetBtn" class="btn-flat" type="button">Reset</button>
                </div>
                <div class="right-tools">
                    <div class="search-wrap">
                        <input id="searchInput" type="text" placeholder="Search item, section, notes…">
                        <button id="searchBtn" class="btn-primary" title="Search">Search</button>
                        <button id="clearSearchBtn" class="btn-flat" title="Clear">Clear</button>
                    </div>
                </div>
            </div>

            <div class="summary" id="summaryText"></div>

            <div class="table-wrap">
                <table class="criteria" id="criteriaTable">
                    <thead>
                        <tr>
                            <th style="min-width:200px">Category</th>
                            <th style="min-width:260px">Subsection</th>
                            <th style="min-width:220px">Position</th>
                            <th style="min-width:90px">Points</th>
                            <th style="min-width:280px">Evidence Needed</th>
                            <th style="min-width:320px">Notes</th>
                        </tr>
                    </thead>


                    <tbody id="tableBody">
                        <tr>
                            <td colspan="5">Loading…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="pager" class="pager-centered">
                <button id="prevBtn" class="btn-page">Back</button>
                <div id="pageNums" class="page-numbers"></div>
                <button id="nextBtn" class="btn-page">Next</button>
            </div>

            <!-- Load the full dataset (pure JSON at public/js/criteria.js) -->
            <script src="{{ asset('js/criteria.js') }}" defer></script>

            <script>
                (function() {
                    let DATA = [],
                        READY = false;
                    const catMap = {
                        leadership: 'I. Leadership Excellence',
                        academic: 'II. Academic Excellence',
                        awards: 'III. Awards/Recognition',
                        community: 'IV. Community Involvement',
                        conduct: 'V. Good Conduct'
                    };

                    const chips = [...document.querySelectorAll('.chip')];

                    const searchInput = document.getElementById('searchInput');
                    const searchBtn = document.getElementById('searchBtn');
                    const clearSearchBtn = document.getElementById('clearSearchBtn');
                    const resetBtn = document.getElementById('resetBtn');

                    const tbody = document.getElementById('tableBody');
                    const pageNums = document.getElementById('pageNums');
                    const prevBtn = document.getElementById('prevBtn');
                    const nextBtn = document.getElementById('nextBtn');
                    const summaryText = document.getElementById('summaryText');

                    let state = {
                        cat: 'all',
                        sort: 'none', // stays fixed
                        q: '',
                        page: 1,
                        pageSize: 25, // fixed default
                        group: true
                    };


                    function loadData() {
                        return fetch("{{ asset('js/criteria.js') }}", {
                                cache: 'no-store'
                            })
                            .then(r => {
                                if (!r.ok) throw new Error('Failed to load criteria.js');
                                return r.json();
                            })
                            .then(arr => {
                                DATA = (arr || []).map((r, i) => ({
                                    ...r,
                                    __idx: i
                                }));
                                READY = true;
                            })
                            .catch(err => {
                                console.error(err);
                                DATA = [{
                                    cat: 'leadership',
                                    section: 'Campus Based – Student Government',
                                    item: 'Student Regent / President',
                                    points: 5,
                                    evidence: 'Oath of Office / Certification',
                                    notes: '(fallback)'
                                }].map((r, i) => ({
                                    ...r,
                                    __idx: i
                                }));
                                READY = true;
                            });
                    }

                    function esc(s) {
                        return String(s).replace(/[&<>]/g, c => ({
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;'
                        } [c]));
                    }

                    function highlight(text, q) {
                        if (!q) return esc(text || '');
                        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\$&') + ')', 'ig');
                        return esc(text || '').replace(re, '<mark>$1</mark>');
                    }

                    function apply() {
                        let rows = DATA.slice();
                        if (state.cat !== 'all') rows = rows.filter(r => r.cat === state.cat);
                        if (state.q.trim()) {
                            const q = state.q.trim().toLowerCase();
                            rows = rows.filter(r =>
                                (r.item || '').toLowerCase().includes(q) ||
                                (r.section || '').toLowerCase().includes(q) ||
                                (r.notes || '').toLowerCase().includes(q) ||
                                (r.evidence || '').toLowerCase().includes(q)
                            );
                        }
                        switch (state.sort) {
                            case 'pointsDesc':
                                rows.sort((a, b) => (b.points ?? 0) - (a.points ?? 0));
                                break;
                            case 'pointsAsc':
                                rows.sort((a, b) => (a.points ?? 0) - (b.points ?? 0));
                                break;
                            case 'alpha':
                                rows.sort((a, b) => (a.item || '').localeCompare(b.item || ''));
                                break;
                            case 'none':
                            default:
                                rows.sort((a, b) => a.__idx - b.__idx);
                        }
                        return rows;
                    }

                    function render() {
                        if (!READY) return;
                        const rowsAll = apply();
                        const total = rowsAll.length;
                        let pageSize = parseInt(state.pageSize, 10);
                        if (!pageSize) pageSize = total || 1;
                        const maxPage = Math.max(1, Math.ceil(total / pageSize));
                        if (state.page > maxPage) state.page = maxPage;
                        const start = (state.page - 1) * pageSize;
                        const pageRows = rowsAll.slice(start, start + pageSize);
                        const q = state.q.trim();
                        let html = '';
                        for (let i = 0; i < pageRows.length;) {
                            const r = pageRows[i];

                            // Group all rows for this category
                            let j = i;
                            while (j < pageRows.length && pageRows[j].cat === r.cat) j++;
                            const catRows = pageRows.slice(i, j);
                            const catRowCount = catRows.length;

                            // Category row
                            html += `<tr class="category-row"><td colspan="6"><strong>${highlight(catMap[r.cat] || r.cat, q)}</strong></td></tr>`;

                            // Process subsections in this category
                            let k = 0;
                            while (k < catRowCount) {
                                const sub = catRows[k];
                                let m = k;
                                while (m < catRowCount && catRows[m].section === sub.section) m++;
                                const sectionRows = catRows.slice(k, m);

                                // Subsection header row
                                html += `<tr class="subsection-row"><td></td><td colspan="5"><em>${highlight(sub.section, q)}</em></td></tr>`;

                                // Positions under subsection
                                for (let n = 0; n < sectionRows.length; n++) {
                                    const rr = sectionRows[n];
                                    html += `<tr>`;
                                    html += `<td></td>`;
                                    html += `<td></td>`;
                                    html += `<td>${highlight(rr.item, q)}</td>`;
                                    html += `<td class="points">${Number.isFinite(rr.points) ? rr.points : ''}</td>`;
                                    if (n === 0) {
                                        html += `<td rowspan="${sectionRows.length}">${highlight(rr.evidence, q)}</td>`;
                                        html += `<td rowspan="${sectionRows.length}">${highlight(rr.notes, q)}</td>`;
                                    }
                                    html += `</tr>`;
                                }

                                k = m;
                            }

                            i = j;
                        }

                        tbody.innerHTML = html || `<tr><td colspan="5">No results.</td></tr>`;

                        // Summary text
                        const shownTo = Math.min(start + pageRows.length, total);
                        summaryText.textContent = total ?
                            `Showing ${start+1}–${shownTo} of ${total} item(s)${state.cat!=='all' ? ' in '+(catMap[state.cat]||state.cat) : ''}${state.q ? ' • filtered by "'+state.q+'"' : ''}` :
                            'No results';

                        // Pager buttons
                        // Pager
                        pageNums.innerHTML = '';

                        // Back button enable/disable
                        prevBtn.disabled = state.page <= 1;

                        // Create page number buttons
                        for (let p = 1; p <= maxPage; p++) {
                            const b = document.createElement('button');
                            b.textContent = p;
                            b.className = 'btn-page';
                            if (p === state.page) b.classList.add('active');
                            b.addEventListener('click', () => {
                                state.page = p;
                                render();
                            });
                            pageNums.appendChild(b);
                        }

                        // Next button enable/disable
                        nextBtn.disabled = state.page >= maxPage;

                    }

                    // Events
                    chips.forEach(ch => ch.addEventListener('click', () => {
                        chips.forEach(x => x.classList.remove('active'));
                        ch.classList.add('active');
                        state.cat = ch.getAttribute('data-cat');
                        state.page = 1;
                        render();
                    }));

                    searchBtn.addEventListener('click', () => {
                        state.q = searchInput.value;
                        state.page = 1;
                        render();
                    });
                    clearSearchBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        state.q = '';
                        state.page = 1;
                        render();
                    });
                    searchInput.addEventListener('keyup', e => {
                        if (e.key === 'Enter') {
                            state.q = searchInput.value;
                            state.page = 1;
                            render();
                        }
                    });
                    prevBtn.addEventListener('click', () => {
                        state.page = Math.max(1, state.page - 1);
                        render();
                    });
                    nextBtn.addEventListener('click', () => {
                        state.page = state.page + 1;
                        render();
                    });
                    resetBtn.addEventListener('click', () => {
                        state = {
                            cat: 'all',
                            sort: 'none',
                            q: '',
                            page: 1,
                            pageSize: 25,
                            group: true
                        };
                        chips.forEach(x => x.classList.remove('active'));
                        document.querySelector('.chip[data-cat="all"]').classList.add('active');

                        searchInput.value = '';
                        render();
                    });

                    loadData().then(render);
                })();
            </script>


        </main>
    </div>
</div>
@endsection

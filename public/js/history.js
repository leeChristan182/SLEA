(() => {
  // Tooltips for action buttons
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover' }));

  const table = document.getElementById('historyTable');
  const rows  = Array.from(table.querySelectorAll('tbody tr'));
  const filterSel = document.getElementById('historyFilter');
  const searchInp = document.getElementById('historySearch');
  const clearBtn  = document.getElementById('clearSearch');

  function matches(row, term) {
    if (!term) return true;
    const text = row.innerText.toLowerCase();
    return text.includes(term.toLowerCase());
  }

  function statusOK(row, status) {
    if (!status) return true;
    return (row.dataset.status || '').toLowerCase() === status.toLowerCase();
  }

  function apply() {
    const term   = searchInp.value.trim();
    const status = filterSel.value.trim();0

    let visible = 0;
    rows.forEach(r => {
      const show = matches(r, term) && statusOK(r, status);
      r.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    // Show "No records" row if needed
    const emptyRow = table.querySelector('tbody tr[data-empty]');
    if (emptyRow) emptyRow.remove();
    if (visible === 0) {
      const tr = document.createElement('tr');
      tr.setAttribute('data-empty', '1');
      tr.innerHTML = `<td colspan="6" class="text-center text-muted py-4">No matching records.</td>`;
      table.querySelector('tbody').appendChild(tr);
    }
  }

  filterSel.addEventListener('change', apply);
  searchInp.addEventListener('input', apply);
  clearBtn.addEventListener('click', () => {
    searchInp.value = '';
    apply();
    searchInp.focus();
  });

  apply();

})();

(() => {
  const table = document.getElementById('historyTable');
  const rows  = Array.from(table.querySelectorAll('tbody tr[data-status]'));
  const empty = table.querySelector('.history-empty');
  const skel  = table.querySelector('.history-skeleton');

  function setState() {
    const visible = rows.filter(r => r.style.display !== 'none').length;
    if (skel) skel.style.display = 'none';
    if (empty) empty.style.display = visible ? 'none' : '';
  }

  // call after your existing apply() runs:
  const applyOrig = window.__historyApply;
  window.__historyApply = function() { if (applyOrig) applyOrig(); setState(); };
  // if you don’t have a named function, just call setState() at the end of your script.

  // run once on load
  setState();
})();


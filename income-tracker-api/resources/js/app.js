import './bootstrap';

const $ = (id) => document.getElementById(id);

function setMsg(text, kind = 'info') {
  const el = $('expense-msg');
  if (!el) return;
  el.textContent = text || '';
  el.className =
    kind === 'error'
      ? 'text-sm text-red-600'
      : kind === 'success'
        ? 'text-sm text-emerald-700'
        : 'text-sm text-zinc-600';
}

function money(v) {
  const n = Number(v || 0);
  return n.toLocaleString(undefined, { style: 'currency', currency: 'USD' });
}

function randomClientId() {
  // deterministic-enough for demo; server enforces uniqueness per user.
  return `${Date.now()}-${Math.random().toString(16).slice(2)}`.slice(0, 64);
}

async function api(path, opts = {}) {
  const res = await fetch(`/api${path}`, {
    headers: {
      ...(opts.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
      ...(opts.headers || {}),
    },
    ...opts,
  });

  if (res.status === 204) return null;

  const isJson = (res.headers.get('content-type') || '').includes('application/json');
  const payload = isJson ? await res.json() : await res.text();
  if (!res.ok) {
    const msg = typeof payload === 'string' ? payload : payload?.error || payload?.message || 'Request failed';
    throw new Error(msg);
  }
  return payload;
}

function fillSelect(el, items, { placeholder } = {}) {
  el.innerHTML = '';
  if (placeholder) {
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = placeholder;
    el.appendChild(opt);
  }
  for (const item of items) {
    const opt = document.createElement('option');
    opt.value = item.value;
    opt.textContent = item.label;
    el.appendChild(opt);
  }
}

function monthsList() {
  return [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];
}

function getMonthNameFromDateInput(dateStr) {
  if (!dateStr) return null;
  const d = new Date(`${dateStr}T00:00:00`);
  if (Number.isNaN(d.getTime())) return null;
  return monthsList()[d.getMonth()] || null;
}

function setActiveTab(tab) {
  for (const btn of document.querySelectorAll('.tab-btn')) {
    const isActive = btn.dataset.tab === tab;
    btn.classList.toggle('bg-zinc-900', isActive);
    btn.classList.toggle('text-white', isActive);
    btn.classList.toggle('hover:bg-zinc-800', isActive);
    btn.classList.toggle('hover:bg-zinc-50', !isActive);
  }
  $('tab-expenses')?.classList.toggle('hidden', tab !== 'expenses');
  $('tab-other')?.classList.toggle('hidden', tab !== 'other');
}

function resetExpenseForm() {
  $('expense-form')?.reset();
  $('expense-client-id').value = '';
  $('expense-save').textContent = 'Save Expense';
  $('receipt-hint').textContent = '';
  setMsg('');
}

async function loadBootstrap() {
  const data = await api('/settings/bootstrap');

  const now = new Date();
  const currentYear = now.getFullYear();
  const years = [];
  for (let y = currentYear - 2; y <= currentYear + 1; y++) years.push(y);

  fillSelect(
    $('expense-month'),
    monthsList().map((m) => ({ value: m, label: m })),
  );
  fillSelect(
    $('expense-year'),
    years.map((y) => ({ value: String(y), label: String(y) })),
  );

  const categories = (data.expenseCategories || []).map((c) => ({ value: c.name, label: c.name }));
  fillSelect($('expense-category'), categories, { placeholder: 'Select category' });

  const platforms = (data.platforms || []).map((p) => ({ value: p.name, label: p.name }));
  fillSelect($('expense-platform'), platforms, { placeholder: 'All / none' });

  // defaults
  $('expense-year').value = String(currentYear);
  $('expense-month').value = monthsList()[now.getMonth()];
}

function renderExpensesTable(rows) {
  const tbody = $('expenses-tbody');
  tbody.innerHTML = '';

  if (!rows.length) {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="7" class="py-4 text-sm text-zinc-500">No expenses yet.</td>`;
    tbody.appendChild(tr);
    return;
  }

  for (const r of rows) {
    const tr = document.createElement('tr');
    tr.className = 'border-b border-zinc-100 align-top';

    const receiptHtml = r.receipt?.downloadUrl
      ? `<a class="text-zinc-900 underline underline-offset-4 hover:text-zinc-700" href="${r.receipt.downloadUrl}" target="_blank" rel="noreferrer">Download</a>`
      : `<span class="text-zinc-400">—</span>`;

    tr.innerHTML = `
      <td class="py-2 pr-3 whitespace-nowrap">${r.date || '—'}</td>
      <td class="py-2 pr-3 whitespace-nowrap">${r.month} ${r.year}</td>
      <td class="py-2 pr-3">${r.category}</td>
      <td class="py-2 pr-3">${r.platform || '—'}</td>
      <td class="py-2 pr-3 text-right whitespace-nowrap">${money(r.amount)}</td>
      <td class="py-2 pr-3">${receiptHtml}</td>
      <td class="py-2 whitespace-nowrap">
        <button data-action="edit" data-client-id="${r.clientId}" class="mr-2 rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium hover:bg-zinc-200">Edit</button>
        <button data-action="delete" data-client-id="${r.clientId}" class="rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-100">Delete</button>
      </td>
    `;
    tbody.appendChild(tr);
  }
}

async function refreshExpenses() {
  const rows = await api('/expenses');
  renderExpensesTable(rows || []);
  return rows || [];
}

async function onSubmitExpense(e) {
  e.preventDefault();
  setMsg('');

  const clientId = $('expense-client-id').value || randomClientId();
  $('expense-client-id').value = clientId;

  const date = $('expense-date').value || '';
  const month = $('expense-month').value || getMonthNameFromDateInput(date) || '';
  const year = $('expense-year').value;
  const category = $('expense-category').value;
  const platform = $('expense-platform').value;
  const amount = $('expense-amount').value;
  const notes = $('expense-notes').value;
  const receiptFile = $('expense-receipt').files?.[0] || null;

  const fd = new FormData();
  fd.set('clientId', clientId);
  if (date) fd.set('date', date);
  fd.set('month', month);
  fd.set('year', year);
  fd.set('category', category);
  if (platform) fd.set('platform', platform);
  fd.set('amount', amount);
  if (notes) fd.set('notes', notes);
  if (receiptFile) fd.set('receipt', receiptFile);

  $('expense-save').disabled = true;
  $('expense-save').textContent = 'Saving...';
  try {
    await api('/expenses', { method: 'POST', body: fd });
    setMsg('Expense saved.', 'success');
    resetExpenseForm();
    await refreshExpenses();
  } catch (err) {
    setMsg(err?.message || 'Failed to save expense.', 'error');
  } finally {
    $('expense-save').disabled = false;
    $('expense-save').textContent = 'Save Expense';
  }
}

function attachTableHandlers(state) {
  $('expenses-tbody').addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    const clientId = btn.dataset.clientId;
    const row = state.rows.find((r) => r.clientId === clientId);
    if (!row) return;

    if (action === 'edit') {
      $('expense-client-id').value = row.clientId;
      $('expense-date').value = row.date || '';
      $('expense-month').value = row.month;
      $('expense-year').value = String(row.year);
      $('expense-category').value = row.category;
      $('expense-platform').value = row.platform || '';
      $('expense-amount').value = row.amount;
      $('expense-notes').value = row.notes || '';
      $('expense-receipt').value = '';
      $('receipt-hint').textContent = row.receipt?.name ? `Existing receipt: ${row.receipt.name}` : '';
      $('expense-save').textContent = 'Update Expense';
      setMsg('');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    if (action === 'delete') {
      if (!confirm('Delete this expense?')) return;
      try {
        await api(`/expenses?clientId=${encodeURIComponent(clientId)}`, { method: 'DELETE' });
        setMsg('Expense deleted.', 'success');
        resetExpenseForm();
        state.rows = await refreshExpenses();
      } catch (err) {
        setMsg(err?.message || 'Failed to delete expense.', 'error');
      }
    }
  });
}

function wireTabs() {
  for (const btn of document.querySelectorAll('.tab-btn')) {
    btn.addEventListener('click', () => setActiveTab(btn.dataset.tab));
  }
  setActiveTab('expenses');
}

document.addEventListener('DOMContentLoaded', async () => {
  wireTabs();

  const state = { rows: [] };
  $('expense-form')?.addEventListener('submit', onSubmitExpense);
  $('expense-clear')?.addEventListener('click', resetExpenseForm);
  $('expenses-refresh')?.addEventListener('click', async () => {
    try {
      state.rows = await refreshExpenses();
      setMsg('Refreshed.', 'success');
      setTimeout(() => setMsg(''), 800);
    } catch (err) {
      setMsg(err?.message || 'Failed to refresh.', 'error');
    }
  });
  $('expense-receipt')?.addEventListener('change', () => {
    const f = $('expense-receipt').files?.[0];
    $('receipt-hint').textContent = f ? `Selected: ${f.name} (${Math.round(f.size / 1024)} KB)` : '';
  });

  attachTableHandlers(state);

  try {
    await loadBootstrap();
    state.rows = await refreshExpenses();
  } catch (err) {
    setMsg(err?.message || 'Failed to load app data.', 'error');
  }
});

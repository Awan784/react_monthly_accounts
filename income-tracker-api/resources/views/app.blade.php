<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DynamiteOS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen bg-zinc-50 text-zinc-900">
    <div id="app" class="mx-auto max-w-5xl px-4 py-8">
      <div class="mb-6 flex flex-col gap-2">
        <div class="flex items-center justify-between gap-3">
          <h1 class="text-2xl font-semibold tracking-tight">{{ config('app.name') }}</h1>
          <a class="text-sm text-zinc-600 underline underline-offset-4 hover:text-zinc-900" href="/">Home</a>
        </div>
        <p class="text-sm text-zinc-600">Expense Entry is now wired to the API (with optional receipt upload).</p>
      </div>

      <div class="mb-6 flex gap-2 rounded-xl bg-white p-2 shadow-sm ring-1 ring-zinc-200">
        <button data-tab="expenses" class="tab-btn flex-1 rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-transparent hover:bg-zinc-50">
          Expenses
        </button>
        <button data-tab="other" class="tab-btn flex-1 rounded-lg px-3 py-2 text-sm font-medium ring-1 ring-transparent hover:bg-zinc-50">
          Other
        </button>
      </div>

      <div id="tab-expenses" class="tab-panel">
        <div class="grid gap-6 lg:grid-cols-2">
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-zinc-200">
            <div class="mb-4">
              <h2 class="text-lg font-semibold">Expense Entry</h2>
              <p class="text-sm text-zinc-600">Saves to <code class="rounded bg-zinc-100 px-1">/api/expenses</code>.</p>
            </div>

            <form id="expense-form" class="space-y-4">
              <input type="hidden" id="expense-client-id" />

              <div class="grid grid-cols-2 gap-3">
                <label class="block">
                  <div class="mb-1 text-xs font-medium text-zinc-700">Date</div>
                  <input id="expense-date" type="date" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400" />
                </label>
                <label class="block">
                  <div class="mb-1 text-xs font-medium text-zinc-700">Amount</div>
                  <input id="expense-amount" type="number" step="0.01" min="0" required class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400" placeholder="0.00" />
                </label>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <label class="block">
                  <div class="mb-1 text-xs font-medium text-zinc-700">Month</div>
                  <select id="expense-month" required class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400"></select>
                </label>
                <label class="block">
                  <div class="mb-1 text-xs font-medium text-zinc-700">Year</div>
                  <select id="expense-year" required class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400"></select>
                </label>
              </div>

              <label class="block">
                <div class="mb-1 text-xs font-medium text-zinc-700">Category</div>
                <select id="expense-category" required class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400"></select>
              </label>

              <label class="block">
                <div class="mb-1 text-xs font-medium text-zinc-700">Platform (optional)</div>
                <select id="expense-platform" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400"></select>
              </label>

              <label class="block">
                <div class="mb-1 text-xs font-medium text-zinc-700">Receipt (optional)</div>
                <input id="expense-receipt" type="file" accept="image/*,.heic,.heif,.pdf,.png,.jpg,.jpeg,.webp" class="block w-full text-sm text-zinc-700 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-zinc-800" />
                <div id="receipt-hint" class="mt-1 text-xs text-zinc-500"></div>
              </label>

              <label class="block">
                <div class="mb-1 text-xs font-medium text-zinc-700">Notes</div>
                <textarea id="expense-notes" rows="3" class="w-full resize-none rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-400" placeholder="Optional notes"></textarea>
              </label>

              <div class="flex gap-2">
                <button id="expense-save" type="submit" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800">
                  Save Expense
                </button>
                <button id="expense-clear" type="button" class="inline-flex items-center justify-center rounded-lg bg-zinc-100 px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-zinc-200">
                  Clear
                </button>
              </div>

              <div id="expense-msg" class="text-sm"></div>
            </form>
          </div>

          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-zinc-200">
            <div class="mb-4 flex items-end justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold">Expenses</h2>
                <p class="text-sm text-zinc-600">Latest entries for the demo user.</p>
              </div>
              <button id="expenses-refresh" class="rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium hover:bg-zinc-200">Refresh</button>
            </div>

            <div class="overflow-auto">
              <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-zinc-500">
                  <tr class="border-b border-zinc-200">
                    <th class="py-2 pr-3">Date</th>
                    <th class="py-2 pr-3">Month</th>
                    <th class="py-2 pr-3">Category</th>
                    <th class="py-2 pr-3">Platform</th>
                    <th class="py-2 pr-3 text-right">Amount</th>
                    <th class="py-2 pr-3">Receipt</th>
                    <th class="py-2">Actions</th>
                  </tr>
                </thead>
                <tbody id="expenses-tbody"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div id="tab-other" class="tab-panel hidden">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
          <div class="text-sm text-zinc-700">
            This build focuses on the requested <strong>Expense Entry</strong> tab end-to-end.
          </div>
        </div>
      </div>
    </div>
  </body>
  </html>


@extends('layouts.app')
@section('title', 'Create Project Estimation')

@php
    // Re-populate after a failed validation so no typed component is lost
    $oldItems = [];
    if (filled(old('items_json'))) {
        $decoded = json_decode(old('items_json'), true);
        if (is_array($decoded)) {
            $oldItems = $decoded;
        }
    }

    $oldTotals = [
        'plants_total' => old('plants_total'),
        'materials_total' => old('materials_total'),
        'labour_total' => old('labour_total'),
        'transport_total' => old('transport_total'),
        'other_total' => old('other_total'),
    ];

    // Serialised for the form script — HEX flags keep "</script>" out of the payload
    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    $componentsJson = json_encode($components, $jsonFlags);
    $oldItemsJson = json_encode($oldItems, $jsonFlags);
    $oldTotalsJson = json_encode($oldTotals, $jsonFlags);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator-fill text-success me-2"></i>New Estimation</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('estimations.index') }}" class="text-success text-decoration-none">Estimations</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('estimations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('estimations.store') }}" id="estimationForm">
    @csrf
    <input type="hidden" name="items_json" id="h-items-json" value="[]">

    @if ($errors->any())
        <div class="alert alert-danger py-2 small mb-4">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Estimation Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Estimation Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Front Garden Landscaping & Lawns" value="{{ old('title') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Cost components ─────────────────────────────────────── --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-list-check text-info me-2"></i>Cost Components</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-success btn-sm" data-add-row="Plant"><i class="bi bi-flower1 me-1"></i>Plant</button>
                        <button type="button" class="btn btn-outline-info btn-sm" data-add-row="Material"><i class="bi bi-box me-1"></i>Material</button>
                        <button type="button" class="btn btn-outline-warning btn-sm" data-add-row="Labour"><i class="bi bi-person-gear me-1"></i>Labour</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-add-row="Transport"><i class="bi bi-truck me-1"></i>Transport</button>
                        <button type="button" class="btn btn-outline-dark btn-sm" data-add-row="Other"><i class="bi bi-gear me-1"></i>Other</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle" id="comp-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:14%">Category</th>
                                    <th style="width:31%">Component Name</th>
                                    <th style="width:9%">Qty</th>
                                    <th style="width:9%">Unit</th>
                                    <th style="width:13%">Rate (₹)</th>
                                    <th style="width:15%" class="text-end">Amount (₹)</th>
                                    <th style="width:9%"></th>
                                </tr>
                            </thead>
                            <tbody id="comp-body"></tbody>
                        </table>
                    </div>
                    <div class="p-4 text-center text-muted small" id="comp-empty">
                        <i class="bi bi-plus-circle d-block mb-1" style="font-size: 1.7rem;"></i>
                        Click a category above to add a component.<br>
                        Start typing the name — components you have used before appear with their last price, but you can type any name and any price you like.
                    </div>
                </div>
            </div>

            {{-- ── Category totals (auto-filled) ────────────────────────── --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-wallet2 text-info me-2"></i>Category Totals</h6>
                    <span class="badge bg-success-subtle text-success" id="auto-badge">Filled from components</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="plants_total" class="form-label small fw-semibold text-secondary mb-1">🌿 Plants Total (₹)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none cat-reset" data-reset="plants_total" hidden title="Re-calculate from components">↺ auto</button>
                            </div>
                            <input type="number" step="0.01" min="0" name="plants_total" id="plants_total" class="form-control cat-total calc-input" value="{{ $oldTotals['plants_total'] ?? '0' }}">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="materials_total" class="form-label small fw-semibold text-secondary mb-1">🧱 Materials Total (₹)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none cat-reset" data-reset="materials_total" hidden title="Re-calculate from components">↺ auto</button>
                            </div>
                            <input type="number" step="0.01" min="0" name="materials_total" id="materials_total" class="form-control cat-total calc-input" value="{{ $oldTotals['materials_total'] ?? '0' }}">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="labour_total" class="form-label small fw-semibold text-secondary mb-1">👷 Labour Total (₹)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none cat-reset" data-reset="labour_total" hidden title="Re-calculate from components">↺ auto</button>
                            </div>
                            <input type="number" step="0.01" min="0" name="labour_total" id="labour_total" class="form-control cat-total calc-input" value="{{ $oldTotals['labour_total'] ?? '0' }}">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="transport_total" class="form-label small fw-semibold text-secondary mb-1">🚚 Transportation (₹)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none cat-reset" data-reset="transport_total" hidden title="Re-calculate from components">↺ auto</button>
                            </div>
                            <input type="number" step="0.01" min="0" name="transport_total" id="transport_total" class="form-control cat-total calc-input" value="{{ $oldTotals['transport_total'] ?? '0' }}">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="other_total" class="form-label small fw-semibold text-secondary mb-1">⚙️ Other / Equipment (₹)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none cat-reset" data-reset="other_total" hidden title="Re-calculate from components">↺ auto</button>
                            </div>
                            <input type="number" step="0.01" min="0" name="other_total" id="other_total" class="form-control cat-total calc-input" value="{{ $oldTotals['other_total'] ?? '0' }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle me-1"></i>These fill themselves in from your components above.
                                Type over one to set it manually — <strong>↺ auto</strong> puts it back.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-card-text text-secondary me-2"></i>Notes</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" class="form-control" rows="3" placeholder="Estimation assumptions, scope notes…">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Summary & Profit Margin</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted d-block">Components</label>
                        <div class="fw-semibold text-dark" id="disp-count">0 items</div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Cost Subtotal</label>
                        <h4 class="fw-bold mb-0 text-dark" id="disp-subtotal">₹0.00</h4>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Profit Margin (%)</label>
                        <input type="number" step="0.01" name="profit_margin_percent" id="profit_margin_percent" class="form-control form-control-sm calc-input" value="{{ old('profit_margin_percent', 20) }}">
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block">Profit Amount</label>
                        <div class="fw-semibold text-success" id="disp-profit">₹0.00</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Discount (₹)</label>
                        <input type="number" step="0.01" name="discount_amount" id="discount_amount" class="form-control form-control-sm calc-input" value="{{ old('discount_amount', 0) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">GST Tax (%)</label>
                        <input type="number" step="0.01" name="tax_percent" id="tax_percent" class="form-control form-control-sm calc-input" value="{{ old('tax_percent', 18) }}">
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="small text-muted d-block">Estimated Grand Total</label>
                        <h3 class="fw-bold text-success mb-0" id="disp-grand-total">₹0.00</h3>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Save Estimation
                        </button>
                        <a href="{{ route('estimations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var TYPES = ['Plant', 'Material', 'Labour', 'Transport', 'Other'];
    var TYPE_TO_FIELD = {
        Plant: 'plants_total', Material: 'materials_total', Labour: 'labour_total',
        Transport: 'transport_total', Other: 'other_total'
    };
    var FIELD_TO_TYPE = {};
    Object.keys(TYPE_TO_FIELD).forEach(function (t) { FIELD_TO_TYPE[TYPE_TO_FIELD[t]] = t; });

    // Memory of components used before: [{name, type, rate, unit, last_used}]
    var COMPONENTS = {!! $componentsJson !!};

    // State preserved across a failed validation
    var OLD_ITEMS = {!! $oldItemsJson !!};
    var OLD_TOTALS = {!! $oldTotalsJson !!};

    var body = document.getElementById('comp-body');
    var emptyHint = document.getElementById('comp-empty');
    var table = document.getElementById('comp-table');
    var form = document.getElementById('estimationForm');
    var itemsJson = document.getElementById('h-items-json');

    function esc(value) {
        var d = document.createElement('div');
        d.textContent = (value === null || value === undefined) ? '' : String(value);
        return d.innerHTML;
    }

    function money(value) {
        var n = Number(value);
        if (!isFinite(n)) n = 0;
        return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function rowHtml(type) {
        var options = TYPES.map(function (t) {
            return '<option value="' + t + '"' + (t === type ? ' selected' : '') + '>' + t + '</option>';
        }).join('');

        return '' +
        '<tr class="comp-row">' +
            '<td><select class="form-select form-select-sm comp-type" aria-label="Category">' + options + '</select></td>' +
            '<td class="comp-name-cell position-relative">' +
                '<input type="text" class="form-control form-control-sm comp-name" autocomplete="off" ' +
                    'placeholder="e.g. Areca Palm 10 inch" aria-label="Component name">' +
                '<div class="comp-suggest" hidden></div>' +
            '</td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm comp-qty" value="1" aria-label="Quantity"></td>' +
            '<td><input type="text" class="form-control form-control-sm comp-unit" value="Nos" maxlength="20" aria-label="Unit"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm comp-rate" value="0" aria-label="Rate"></td>' +
            '<td class="text-end fw-semibold comp-amount">₹0.00</td>' +
            '<td class="text-center">' +
                '<button type="button" class="btn btn-sm text-muted comp-del" title="Remove component" aria-label="Remove component">' +
                    '<i class="bi bi-x-lg"></i>' +
                '</button>' +
            '</td>' +
        '</tr>';
    }

    function addRow(type) {
        body.insertAdjacentHTML('beforeend', rowHtml(type));
        recalcTotals();
        var last = body.querySelector('.comp-row:last-child .comp-name');
        if (last) last.focus();
    }

    /* ── Typeahead ─────────────────────────────────────────────────── */

    function closeSuggests() {
        Array.prototype.forEach.call(document.querySelectorAll('.comp-suggest'), function (p) {
            p.hidden = true;
            p.innerHTML = '';
            p._hits = null;
            p.dataset.active = '-1';
        });
    }

    function openSuggest(input) {
        var panel = input.parentElement.querySelector('.comp-suggest');
        if (!panel) return null;

        var q = input.value.trim().toLowerCase();
        if (!q) {
            panel.hidden = true; panel.innerHTML = ''; panel._hits = null; panel.dataset.active = '-1';
            return null;
        }

        var hits = COMPONENTS.filter(function (c) {
            return String(c.name).toLowerCase().indexOf(q) !== -1;
        }).slice(0, 7);

        if (!hits.length) {
            panel.hidden = true; panel.innerHTML = ''; panel._hits = null; panel.dataset.active = '-1';
            return null;
        }

        panel._hits = hits;
        panel.dataset.active = '-1';
        panel.innerHTML = hits.map(function (c, i) {
            var meta = c.type + ' · ' + money(c.rate) + ' · ' + (c.unit || 'Nos');
            if (c.last_used) meta += ' · used ' + String(c.last_used).slice(0, 10);
            return '<button type="button" class="comp-opt" data-i="' + i + '">' +
                   '<span class="comp-opt-name">' + esc(c.name) + '</span>' +
                   '<span class="comp-opt-meta">' + esc(meta) + '</span>' +
                   '</button>';
        }).join('');
        panel.hidden = false;

        // Fixed coordinates — .table-responsive clips an absolutely positioned panel
        var rect = input.getBoundingClientRect();
        var width = Math.max(rect.width, 300);
        var left = rect.left;
        if (left + width > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - width - 8);
        }
        panel.style.left = left + 'px';
        panel.style.top = (rect.bottom + 4) + 'px';
        panel.style.width = width + 'px';

        return panel;
    }

    function highlight(panel, index) {
        var opts = Array.prototype.slice.call(panel.querySelectorAll('.comp-opt'));
        if (!opts.length) return;
        var i = ((index % opts.length) + opts.length) % opts.length;
        opts.forEach(function (o, k) { o.classList.toggle('is-active', k === i); });
        panel.dataset.active = String(i);
        if (opts[i].scrollIntoView) opts[i].scrollIntoView({ block: 'nearest' });
    }

    function pickSuggestion(input, panel, index) {
        var hit = (panel._hits || [])[index];
        if (!hit) return;

        var row = input.closest('.comp-row');
        input.value = hit.name;
        row.querySelector('.comp-rate').value = Number(hit.rate || 0).toFixed(2);
        row.querySelector('.comp-unit').value = hit.unit || 'Nos';
        row.querySelector('.comp-type').value = TYPES.indexOf(hit.type) !== -1 ? hit.type : 'Other';
        input.classList.remove('is-invalid');

        closeSuggests();
        recalcTotals();
        input.focus();
    }

    /* ── Totals ────────────────────────────────────────────────────── */

    function computeSums() {
        var sums = { Plant: 0, Material: 0, Labour: 0, Transport: 0, Other: 0 };

        Array.prototype.forEach.call(body.querySelectorAll('.comp-row'), function (row) {
            var type = row.querySelector('.comp-type').value;
            var qty = parseFloat(row.querySelector('.comp-qty').value) || 0;
            var rate = parseFloat(row.querySelector('.comp-rate').value) || 0;
            var amount = qty * rate;
            if (sums[type] !== undefined) sums[type] += amount;
            row.querySelector('.comp-amount').textContent = money(amount);
        });

        return sums;
    }

    function recalcTotals() {
        var sums = computeSums();
        var rows = body.querySelectorAll('.comp-row').length;

        Object.keys(TYPE_TO_FIELD).forEach(function (type) {
            var input = document.getElementById(TYPE_TO_FIELD[type]);
            if (!input || input.dataset.manual === '1') return;
            input.value = sums[type].toFixed(2);
        });

        if (emptyHint) emptyHint.hidden = rows > 0;
        if (table) table.hidden = rows === 0;

        var count = document.getElementById('disp-count');
        if (count) count.textContent = rows + (rows === 1 ? ' item' : ' items');

        recalculate();
    }

    /* ── Manual override on the 5 boxes ────────────────────────────── */

    function setManual(input, on) {
        input.dataset.manual = on ? '1' : '0';
        var btn = document.querySelector('[data-reset="' + input.id + '"]');
        if (btn) btn.hidden = !on;
    }

    /* ── Events ────────────────────────────────────────────────────── */

    Array.prototype.forEach.call(document.querySelectorAll('[data-add-row]'), function (btn) {
        btn.addEventListener('click', function () { addRow(btn.dataset.addRow); });
    });

    body.addEventListener('click', function (event) {
        var del = event.target.closest('.comp-del');
        if (del) {
            del.closest('.comp-row').remove();
            recalcTotals();
            return;
        }

        var opt = event.target.closest('.comp-opt');
        if (opt) {
            var panel = opt.closest('.comp-suggest');
            pickSuggestion(panel.parentElement.querySelector('.comp-name'), panel, parseInt(opt.dataset.i, 10));
        }
    });

    body.addEventListener('input', function (event) {
        var t = event.target;
        if (t.classList.contains('comp-name')) { openSuggest(t); return; }
        if (t.classList.contains('comp-qty') || t.classList.contains('comp-rate')) {
            t.classList.remove('is-invalid');
            recalcTotals();
        }
    });

    body.addEventListener('change', function (event) {
        if (event.target.classList.contains('comp-type') || event.target.classList.contains('comp-unit')) {
            recalcTotals();
        }
    });

    body.addEventListener('keydown', function (event) {
        var input = event.target.closest('.comp-name');
        if (!input) return;

        var cell = input.parentElement;
        var existing = cell.querySelector('.comp-suggest');
        var isActive = existing && !existing.hidden;
        var current = isActive ? parseInt(existing.dataset.active, 10) : NaN;

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            var panel = openSuggest(input);
            if (!panel || !panel._hits) return;
            var next;
            if (event.key === 'ArrowDown') next = isNaN(current) ? 0 : current + 1;
            else next = isNaN(current) ? panel._hits.length - 1 : current - 1;
            highlight(panel, next);
            return;
        }

        if (event.key === 'Enter') {
            // Never let Enter inside a row submit the whole form
            event.preventDefault();
            if (isActive) {
                var idx = parseInt(existing.dataset.active, 10);
                if (idx >= 0) pickSuggestion(input, existing, idx);
                else closeSuggests();
            }
            return;
        }

        if (event.key === 'Escape' && isActive) {
            event.stopPropagation();
            closeSuggests();
        }
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.comp-name-cell')) closeSuggests();
    });

    // A fixed-position panel must not survive a scroll — it would detach from its row
    window.addEventListener('scroll', closeSuggests, true);
    window.addEventListener('resize', closeSuggests);

    Array.prototype.forEach.call(document.querySelectorAll('.cat-total'), function (input) {
        input.addEventListener('input', function () {
            setManual(input, true);
            recalculate();
        });
    });

    Array.prototype.forEach.call(document.querySelectorAll('.cat-reset'), function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.reset);
            if (!input) return;
            setManual(input, false);
            recalcTotals();
        });
    });

    /* ── Existing summary calculator (unchanged maths) ─────────────── */

    function recalculate() {
        var p = parseFloat(document.getElementById('plants_total').value) || 0;
        var m = parseFloat(document.getElementById('materials_total').value) || 0;
        var l = parseFloat(document.getElementById('labour_total').value) || 0;
        var t = parseFloat(document.getElementById('transport_total').value) || 0;
        var o = parseFloat(document.getElementById('other_total').value) || 0;

        var sub = p + m + l + t + o;
        var marginPct = parseFloat(document.getElementById('profit_margin_percent').value) || 0;
        var profit = (sub * marginPct) / 100;
        var subWithProfit = sub + profit;

        var disc = parseFloat(document.getElementById('discount_amount').value) || 0;
        var taxPct = parseFloat(document.getElementById('tax_percent').value) || 0;

        var afterDisc = Math.max(0, subWithProfit - disc);
        var tax = (afterDisc * taxPct) / 100;
        var grand = afterDisc + tax;

        document.getElementById('disp-subtotal').textContent = money(sub);
        document.getElementById('disp-profit').textContent = money(profit);
        document.getElementById('disp-grand-total').textContent = money(grand);
    }

    Array.prototype.forEach.call(document.querySelectorAll('.calc-input'), function (el) {
        el.addEventListener('input', recalculate);
    });

    /* ── Submit: serialise rows, validate them first ───────────────── */

    form.addEventListener('submit', function (event) {
        var rows = Array.prototype.slice.call(body.querySelectorAll('.comp-row'));
        var items = [];
        var valid = true;

        rows.forEach(function (row) {
            var name = row.querySelector('.comp-name');
            var qty = row.querySelector('.comp-qty');
            var rate = row.querySelector('.comp-rate');

            [name, qty, rate].forEach(function (f) { f.classList.remove('is-invalid'); });

            if (!name.value.trim()) { name.classList.add('is-invalid'); valid = false; }
            if (!(parseFloat(qty.value) > 0)) { qty.classList.add('is-invalid'); valid = false; }
            if (rate.value === '' || isNaN(parseFloat(rate.value)) || parseFloat(rate.value) < 0) {
                rate.classList.add('is-invalid');
                valid = false;
            }

            items.push({
                item_type: row.querySelector('.comp-type').value,
                item_name: name.value.trim(),
                quantity: parseFloat(qty.value) || 0,
                unit: row.querySelector('.comp-unit').value.trim() || 'Nos',
                rate: parseFloat(rate.value) || 0,
                notes: null
            });
        });

        if (!valid) {
            event.preventDefault();
            showToast('Complete the highlighted component rows before saving.', 'danger');
            var bad = body.querySelector('.is-invalid');
            if (bad) { bad.focus(); if (bad.scrollIntoView) bad.scrollIntoView({ block: 'center' }); }
            return;
        }

        itemsJson.value = JSON.stringify(items);
    });

    /* ── Restore state after a failed validation ───────────────────── */

    (function restore() {
        OLD_ITEMS.forEach(function (it) {
            if (!it || !it.item_name) return;
            body.insertAdjacentHTML('beforeend', rowHtml(TYPES.indexOf(it.item_type) !== -1 ? it.item_type : 'Other'));
            var row = body.querySelector('.comp-row:last-child');
            row.querySelector('.comp-name').value = it.item_name;
            row.querySelector('.comp-qty').value = it.quantity || 1;
            row.querySelector('.comp-unit').value = it.unit || 'Nos';
            row.querySelector('.comp-rate').value = it.rate || 0;
        });

        var sums = computeSums();

        // A box whose saved value differs from the component sum was a manual
        // override — restore it as such so recalcTotals() doesn't wipe it.
        Object.keys(FIELD_TO_TYPE).forEach(function (fieldId) {
            var input = document.getElementById(fieldId);
            var oldVal = OLD_TOTALS[fieldId];
            if (!input || oldVal === null || oldVal === undefined || oldVal === '') return;

            if (Math.abs(parseFloat(oldVal || 0) - (sums[FIELD_TO_TYPE[fieldId]] || 0)) > 0.005) {
                setManual(input, true);
            }
        });

        recalcTotals();
    })();
})();
</script>
@endpush

/**
 * quotations.js — Dynamic line item management + real-time totals
 * Used by: quotations/create.blade.php, quotations/edit.blade.php
 */

let rowCount = 0;

function addRow(category = 'Other') {
    rowCount++;
    const tbody = document.getElementById('items-body');
    const hint  = document.getElementById('empty-hint');
    if (hint) hint.style.display = 'none';

    const units = ['Nos','Pcs','Kg','Litre','Bag','Box','Sqft','Rft','Hours','Days','Lumpsum'];
    const unitOpts = units.map(u => `<option value="${u}">${u}</option>`).join('');

    const tr = document.createElement('tr');
    tr.id = `row-${rowCount}`;
    tr.setAttribute('data-row', rowCount);
    tr.innerHTML = `
        <td>
            <input type="text" name="items[${rowCount}][item_name]" class="form-control form-control-sm"
                   placeholder="Item description…" required>
        </td>
        <td>
            <select name="items[${rowCount}][item_type]" class="form-select form-select-sm">
                ${['Plant','Material','Labour','Transportation','Equipment','Other']
                    .map(c => `<option value="${c}"${c===category?' selected':''}>${c}</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][quantity]" class="form-control form-control-sm qty-input"
                   data-row="${rowCount}" value="1" min="0.01" step="0.01" required>
        </td>
        <td>
            <select name="items[${rowCount}][unit]" class="form-select form-select-sm">${unitOpts}</select>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][unit_price]" class="form-control form-control-sm rate-input"
                   data-row="${rowCount}" value="0" min="0" step="0.01" required>
        </td>
        <td>
            <input type="number" name="items[${rowCount}][discount_percent]" class="form-control form-control-sm disc-input"
                   data-row="${rowCount}" value="0" min="0" max="100" step="0.01">
        </td>
        <td>
            <input type="number" name="items[${rowCount}][tax_percent]" class="form-control form-control-sm tax-input"
                   data-row="${rowCount}" value="0" min="0" max="28" step="0.01">
        </td>
        <td class="text-end">
            <span class="fw-semibold row-amount" id="row-amount-${rowCount}">₹0.00</span>
            <input type="hidden" name="items[${rowCount}][amount]" id="h-row-amount-${rowCount}" value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeRow(${rowCount})" title="Remove">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    // Attach listeners
    tr.querySelectorAll('.qty-input,.rate-input,.disc-input,.tax-input').forEach(el => {
        el.addEventListener('input', () => recalcRow(rowCount));
    });
}

function removeRow(id) {
    const row = document.getElementById(`row-${id}`);
    if (row) { row.remove(); recalcTotals(); }
    if (document.querySelectorAll('#items-body tr').length === 0) {
        const hint = document.getElementById('empty-hint');
        if (hint) hint.style.display = '';
    }
}

function recalcRow(id) {
    const qty   = parseFloat(document.querySelector(`[name="items[${id}][quantity]"]`)?.value) || 0;
    const rate  = parseFloat(document.querySelector(`[name="items[${id}][unit_price]"]`)?.value) || 0;
    const disc  = parseFloat(document.querySelector(`[name="items[${id}][discount_percent]"]`)?.value) || 0;
    const tax   = parseFloat(document.querySelector(`[name="items[${id}][tax_percent]"]`)?.value) || 0;

    const base      = qty * rate;
    const discAmt   = base * disc / 100;
    const afterDisc = base - discAmt;
    const taxAmt    = afterDisc * tax / 100;
    const total     = afterDisc + taxAmt;

    const displayEl = document.getElementById(`row-amount-${id}`);
    const hiddenEl  = document.getElementById(`h-row-amount-${id}`);

    if (displayEl) displayEl.textContent = '₹' + total.toFixed(2);
    if (hiddenEl)  hiddenEl.value = total.toFixed(2);

    recalcTotals();
}

function recalcTotals() {
    let subtotal = 0;
    document.querySelectorAll('[id^="h-row-amount-"]').forEach(el => {
        subtotal += parseFloat(el.value) || 0;
    });

    const discountVal  = parseFloat(document.getElementById('discount-amount')?.value) || 0;
    const discountType = document.getElementById('discount-type')?.value || 'fixed';
    const taxPct       = parseFloat(document.getElementById('tax-percent')?.value) || 0;

    const discAmt  = discountType === 'percent' ? subtotal * discountVal / 100 : discountVal;
    const afterDisc = Math.max(0, subtotal - discAmt);
    const taxAmt    = afterDisc * taxPct / 100;
    const grandTotal = afterDisc + taxAmt;

    const fmt = v => '₹' + v.toFixed(2);
    setEl('display-subtotal', fmt(subtotal));
    setEl('display-after-disc', fmt(afterDisc));
    setEl('display-tax', fmt(taxAmt));
    setEl('display-grand-total', fmt(grandTotal));

    setVal('h-subtotal', subtotal.toFixed(2));
    setVal('h-tax', taxAmt.toFixed(2));
    setVal('h-grand-total', grandTotal.toFixed(2));
}

function setEl(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}
function setVal(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val;
}

function serializeItems() {
    // Collect all rows and populate hidden items_json as fallback
    const rows = document.querySelectorAll('#items-body tr');
    const items = [];
    rows.forEach(tr => {
        const rowId = tr.getAttribute('data-row');
        const nameInput = tr.querySelector(`[name="items[${rowId}][item_name]"]`);
        const typeInput = tr.querySelector(`[name="items[${rowId}][item_type]"]`);
        const qtyInput  = tr.querySelector(`[name="items[${rowId}][quantity]"]`);
        const unitInput = tr.querySelector(`[name="items[${rowId}][unit]"]`);
        const rateInput = tr.querySelector(`[name="items[${rowId}][unit_price]"]`);
        const discInput = tr.querySelector(`[name="items[${rowId}][discount_percent]"]`);
        const taxInput  = tr.querySelector(`[name="items[${rowId}][tax_percent]"]`);

        if (nameInput && nameInput.value.trim()) {
            items.push({
                item_name: nameInput.value.trim(),
                item_type: typeInput ? typeInput.value : 'Plant',
                description: nameInput.value.trim(),
                quantity: parseFloat(qtyInput ? qtyInput.value : 1) || 1,
                unit: unitInput ? unitInput.value : 'Nos',
                unit_price: parseFloat(rateInput ? rateInput.value : 0) || 0,
                discount: parseFloat(discInput ? discInput.value : 0) || 0,
                tax_percent: parseFloat(taxInput ? taxInput.value : 18) || 18,
            });
        }
    });

    const jsonEl = document.getElementById('h-items-json');
    if (jsonEl) jsonEl.value = JSON.stringify(items);

    recalcTotals();
}

// Attach listeners to discount/tax fields
document.addEventListener('DOMContentLoaded', function() {
    ['discount-amount','discount-type','tax-percent'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', recalcTotals);
    });

    // Auto add initial item row if table is empty
    if (document.querySelectorAll('#items-body tr').length === 0) {
        addRow('Plant');
    }
});

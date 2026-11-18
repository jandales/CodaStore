/*
 * Admin > Inventory: the Adjust stock panel (Stock In, Stock Out, mark as damaged, repaired,
 * write off) with a live preview. The server checks every quantity again.
 * Standalone (the compiled admin bundle can't be rebuilt here). Source copy: resources/js/admin/adjust-stock.js.
 */
(function () {
    'use strict';

    var overlay = document.querySelector('[data-adjust-panel]');
    if (!overlay) return;

    var form = overlay.querySelector('form');
    var qtyInput = form.querySelector('[name="qty"]');
    var noteInput = form.querySelector('[name="note"]');
    var errorBox = form.querySelector('[data-adj-error]');
    var saveBtn = form.querySelector('[data-adj-save]');
    var toast = document.querySelector('[data-adj-toast]');
    var token = (document.querySelector('meta[name="csrf-token"]') || {}).content;
    var current = null; // { button, row, available, damaged }

    function action() {
        return form.querySelector('[name="action"]:checked').value;
    }

    /** [available, damaged] after the change, or an error message. */
    function result() {
        var n = parseInt(qtyInput.value, 10);
        var a = current.available, d = current.damaged;
        if (isNaN(n) || n < 1) return { error: 'Enter a quantity of at least 1.' };
        switch (action()) {
            case 'stock_in': return { a: a + n, d: d };
            case 'stock_out': return n > a ? { error: 'Only ' + a + ' available.' } : { a: a - n, d: d };
            case 'damage': return n > a ? { error: 'Only ' + a + ' available to mark as damaged.' } : { a: a - n, d: d + n };
            case 'repaired': return n > d ? { error: 'Only ' + d + ' damaged.' } : { a: a + n, d: d - n };
            case 'write_off': return n > d ? { error: 'Only ' + d + ' damaged to write off.' } : { a: a, d: d - n };
        }
        return { error: 'Choose what happened to the stock.' };
    }

    function preview() {
        var r = result();
        errorBox.textContent = r.error || '';
        form.querySelector('[data-adj-available]').textContent = current.available + ' → ' + (r.error ? '…' : r.a);
        form.querySelector('[data-adj-damaged]').textContent = current.damaged + ' → ' + (r.error ? '…' : r.d);
        return r;
    }

    function open(button) {
        current = {
            button: button,
            row: button.closest('[data-stock-row]'),
            available: parseInt(button.getAttribute('data-available'), 10) || 0,
            damaged: parseInt(button.getAttribute('data-damaged'), 10) || 0
        };
        form.querySelector('[data-adj-label]').textContent = button.getAttribute('data-label');
        form.querySelector('[name="action"][value="stock_in"]').checked = true;
        qtyInput.value = 1;
        noteInput.value = '';
        // Damaged-only actions make no sense with nothing damaged.
        ['repaired', 'write_off'].forEach(function (v) {
            form.querySelector('[name="action"][value="' + v + '"]').disabled = current.damaged === 0;
        });
        overlay.hidden = false;
        preview();
        qtyInput.focus();
        qtyInput.select();
    }

    function close() {
        overlay.hidden = true;
        if (current) current.button.focus();
        current = null;
    }

    function showToast(text) {
        if (!toast) return;
        toast.textContent = text;
        toast.hidden = false;
        clearTimeout(showToast.t);
        showToast.t = setTimeout(function () { toast.hidden = true; }, 3000);
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('.btn-adjust');
        if (b) open(b);
        if (e.target.closest('[data-adj-close]') || e.target === overlay) close();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !overlay.hidden) close();
    });
    form.addEventListener('input', preview);
    form.addEventListener('change', preview);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var r = preview();
        if (r.error) { qtyInput.focus(); return; }

        var body = new URLSearchParams();
        body.append('_method', 'PUT');
        body.append('action', action());
        body.append('qty', qtyInput.value);
        body.append('note', noteInput.value);
        if (current.button.getAttribute('data-sku')) body.append('sku_id', current.button.getAttribute('data-sku'));

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span> Saving…';

        fetch(current.button.getAttribute('data-url'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        }).then(function (res) {
            return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        }).then(function (res) {
            if (!res.ok) {
                var errors = res.data.errors || {};
                var first = Object.keys(errors).map(function (k) { return errors[k][0]; })[0];
                errorBox.textContent = first || res.data.message || 'Couldn\'t save. Try again.';
                return;
            }
            var s = res.data.stock;
            var row = current.row;
            row.querySelector('[data-available]').textContent = s.available;
            row.querySelector('[data-total]').textContent = s.on_hand;
            var dmg = row.querySelector('[data-damaged]');
            dmg.textContent = s.damaged;
            dmg.classList.toggle('has-damaged', s.damaged > 0);
            row.classList.toggle('is-out', s.available <= 0);
            current.button.setAttribute('data-available', s.available);
            current.button.setAttribute('data-damaged', s.damaged);
            showToast(current.button.getAttribute('data-label') + ': ' + res.data.message.replace(': saved.', '') + ' saved. Available ' + s.available + ', damaged ' + s.damaged + '.');
            close();
        }).catch(function () {
            errorBox.textContent = 'Couldn\'t save. Check your connection and try again.';
        }).then(function () {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        });
    });
})();

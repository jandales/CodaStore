/*
 * Variants card on the admin product form (create and edit).
 *
 * Options (e.g. Size: S, M, L) with an Edit button and "+ Add another option", then one row per
 * variant with photo, price, available quantity and SKU, and the total inventory underneath.
 * Everything the server needs is written as hidden inputs inside the product form:
 *   hasVariant=1, attributes[]={"id":1,"variants":["S","M"]}, skus[i][options|price|qty|image|sku]
 * so the existing save (new FormData(form)) sends it. Source copy: resources/js/admin/variants-card.js.
 */
(function () {
    'use strict';

    var card = document.getElementById('variants-card');
    if (!card) return;

    function json(attr, fallback) {
        try { return JSON.parse(card.getAttribute(attr) || ''); } catch (e) { return fallback; }
    }

    var form = card.closest('form');
    var attributes = json('data-attributes', []);            // [{id, name}]
    var maxOptions = parseInt(card.getAttribute('data-max-options'), 10) || 2;
    var maxVariants = parseInt(card.getAttribute('data-max-variants'), 10) || 100;
    var list = card.querySelector('[data-options-list]');
    var addButton = card.querySelector('[data-add-option]');
    var noAttributes = card.querySelector('[data-no-attributes]');
    var warning = card.querySelector('.variant-table__warning');
    var hidden = card.querySelector('[data-hidden-inputs]');
    var tableWrap = card.querySelector('[data-table]');
    var tbody = tableWrap.querySelector('tbody');
    var totalEl = tableWrap.querySelector('[data-total]');
    var qtyInput = form ? form.querySelector('input[name="qty"]') : null;
    var qtyBlock = qtyInput ? qtyInput.closest('.form-block') : null;
    var priceInput = form ? form.querySelector('input[name="regular_price"]') : null;

    var options = json('data-options', []).map(function (o) {
        return { id: parseInt(o.id, 10), name: String(o.name || ''), values: (o.values || []).map(String), editing: false };
    });

    // What has been typed in the table, by combination key, so re-rendering never loses it.
    var values = {};
    json('data-skus', []).forEach(function (sku) {
        values[sku.key] = {
            price: sku.price === null || sku.price === undefined ? '' : String(sku.price),
            qty: String(sku.qty === null || sku.qty === undefined ? 0 : sku.qty),
            image: sku.image || '',
            sku: sku.sku || ''
        };
    });

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }

    function unusedAttributes(exceptIndex) {
        var used = options.filter(function (o, i) { return i !== exceptIndex; }).map(function (o) { return o.id; });
        return attributes.filter(function (a) { return used.indexOf(a.id) === -1; });
    }

    // ------------------------------------------------------------------ options
    function optionView(o, i) {
        return '<div class="variant-option">'
            + '<div class="variant-option__main">'
            +   '<strong class="variant-option__name">' + esc(cap(o.name)) + '</strong>'
            +   '<ul class="variant-chips" role="list">' + o.values.map(function (v) { return '<li>' + esc(v) + '</li>'; }).join('') + '</ul>'
            + '</div>'
            + '<button type="button" class="btn btn-light" data-edit="' + i + '">Edit</button>'
            + '</div>';
    }

    function optionEditor(o, i) {
        var choices = unusedAttributes(i).map(function (a) {
            return '<option value="' + a.id + '"' + (a.id === o.id ? ' selected' : '') + '>' + esc(cap(a.name)) + '</option>';
        }).join('');
        return '<div class="variant-option is-editing">'
            + '<div class="form-block">'
            +   '<label for="option-name-' + i + '">Option name</label>'
            +   '<select id="option-name-' + i + '" data-attr="' + i + '">' + choices + '</select>'
            + '</div>'
            + '<div class="form-block">'
            +   '<label for="option-value-' + i + '">Option values</label>'
            +   '<div class="variant-values">'
            +     o.values.map(function (v, j) {
                    return '<span class="variant-chip-edit">' + esc(v)
                        + '<button type="button" data-remove-value="' + i + ':' + j + '" aria-label="Remove ' + esc(v) + '"><i class="fas fa-times" aria-hidden="true"></i></button></span>';
                }).join('')
            +     '<input id="option-value-' + i + '" type="text" data-value-input="' + i + '" placeholder="' + (o.values.length ? 'Add another value' : 'e.g. S, M, L') + '" autocomplete="off">'
            +   '</div>'
            +   '<p class="form-help">Type a value and press Enter (or a comma).</p>'
            + '</div>'
            + '<div class="variant-option__actions">'
            +   '<button type="button" class="btn btn-light variant-option__delete" data-delete="' + i + '">Delete</button>'
            +   '<button type="button" class="btn btn-primary" data-done="' + i + '">Done</button>'
            + '</div>'
            + '</div>';
    }

    function renderOptions() {
        list.innerHTML = options.map(function (o, i) { return o.editing ? optionEditor(o, i) : optionView(o, i); }).join('');
        var canAdd = options.length < maxOptions && unusedAttributes(-1).length > 0;
        addButton.hidden = !canAdd;
        addButton.querySelector('span').textContent = options.length ? 'Add another option' : 'Add options like size or colour';
        noAttributes.hidden = canAdd || options.length >= maxOptions;
    }

    function addValue(i, raw) {
        var o = options[i];
        String(raw).split(',').forEach(function (part) {
            var v = part.trim();
            if (!v) return;
            var exists = o.values.some(function (x) { return x.toLowerCase() === v.toLowerCase(); });
            if (!exists) o.values.push(v);
        });
    }

    // ------------------------------------------------------------------ variant table
    function activeOptions() {
        return options.filter(function (o) { return o.values.length; });
    }

    function combinations(opts) {
        var combos = [{}];
        opts.forEach(function (o) {
            var next = [];
            combos.forEach(function (c) {
                o.values.forEach(function (v) {
                    var n = {};
                    Object.keys(c).forEach(function (k) { n[k] = c[k]; });
                    n[o.name.toLowerCase()] = v;
                    next.push(n);
                });
            });
            combos = next;
        });
        return combos;
    }

    /** Same as ProductSku::keyFor(): "color:black|size:m". */
    function keyFor(combo) {
        return Object.keys(combo).map(function (k) { return [k.trim().toLowerCase(), String(combo[k]).trim().toLowerCase()]; })
            .sort(function (a, b) { return a[0] < b[0] ? -1 : a[0] > b[0] ? 1 : 0; })
            .map(function (p) { return p[0] + ':' + p[1]; }).join('|');
    }

    function photoChoices() {
        var seen = [];
        document.querySelectorAll('.product-image, .image-gallery img').forEach(function (img) {
            var src = img.getAttribute('src');
            if (src && seen.indexOf(src) === -1) seen.push(src);
        });
        return seen;
    }

    function remember() {
        tbody.querySelectorAll('tr[data-key]').forEach(function (tr) {
            values[tr.getAttribute('data-key')] = {
                price: tr.querySelector('[data-field="price"]').value,
                qty: tr.querySelector('[data-field="qty"]').value,
                image: tr.querySelector('[data-field="image"]').value,
                sku: tr.querySelector('[data-field="sku"]').value
            };
        });
    }

    function updateTotal() {
        var total = 0;
        tbody.querySelectorAll('[data-field="qty"]').forEach(function (input) {
            var n = parseInt(input.value, 10);
            if (!isNaN(n) && n > 0) total += n;
        });
        totalEl.textContent = total;
    }

    function thumb(src) {
        return src ? '<img src="' + esc(src) + '" alt="">' : '<i class="far fa-image" aria-hidden="true"></i>';
    }

    function renderTable() {
        remember();
        var opts = activeOptions();
        warning.hidden = true;

        if (!opts.length) {
            tableWrap.hidden = true;
            tbody.innerHTML = '';
            if (qtyBlock) { qtyBlock.hidden = false; qtyInput.disabled = false; }
            return;
        }
        if (qtyBlock) { qtyBlock.hidden = true; qtyInput.disabled = true; }

        var combos = combinations(opts);
        if (combos.length > maxVariants) {
            warning.textContent = 'That makes ' + combos.length + ' variants. Use fewer values (at most ' + maxVariants + ').';
            warning.hidden = false;
            tableWrap.hidden = true;
            tbody.innerHTML = '';
            return;
        }

        tableWrap.hidden = false;
        var photos = photoChoices();
        var placeholder = priceInput && priceInput.value ? priceInput.value : '';
        tbody.innerHTML = combos.map(function (combo, i) {
            var key = keyFor(combo);
            var v = values[key] || { price: '', qty: '0', image: '', sku: '' };
            var label = Object.keys(combo).map(function (k) { return combo[k]; }).join(' / ');
            var photoList = photos.concat(v.image && photos.indexOf(v.image) === -1 ? [v.image] : []);
            var photoOptions = '<option value="">Product photo</option>' + photoList.map(function (src, n) {
                return '<option value="' + esc(src) + '"' + (src === v.image ? ' selected' : '') + '>Photo ' + (n + 1) + '</option>';
            }).join('');
            return '<tr data-key="' + esc(key) + '">'
                + '<td class="variant-table__variant">'
                +   '<label class="variant-thumb" title="Choose a photo">' + thumb(v.image)
                +     '<select name="skus[' + i + '][image]" data-field="image" aria-label="Photo for ' + esc(label) + '">' + photoOptions + '</select>'
                +   '</label>'
                +   '<span class="variant-table__label">' + esc(label) + '</span>'
                +   '<input type="hidden" name="skus[' + i + '][options]" value="' + esc(JSON.stringify(combo)) + '">'
                + '</td>'
                + '<td><div class="money-input"><span>₱</span><input type="number" min="1" step="0.01" name="skus[' + i + '][price]" data-field="price" value="' + esc(v.price) + '" placeholder="' + esc(placeholder) + '" aria-label="Price for ' + esc(label) + '"></div></td>'
                + '<td><input type="number" min="0" step="1" name="skus[' + i + '][qty]" data-field="qty" value="' + esc(v.qty) + '" required aria-label="Available for ' + esc(label) + '"></td>'
                + '<td><input type="text" maxlength="100" name="skus[' + i + '][sku]" data-field="sku" value="' + esc(v.sku) + '" aria-label="SKU for ' + esc(label) + '"></td>'
                + '</tr>';
        }).join('');
        updateTotal();
    }

    // ------------------------------------------------------------------ hidden inputs for the save
    function renderHidden() {
        var opts = activeOptions();
        var html = '';
        if (opts.length) {
            html += '<input type="hidden" name="hasVariant" value="1">';
            opts.forEach(function (o) {
                html += '<input type="hidden" name="attributes[]" value="' + esc(JSON.stringify({ id: o.id, variants: o.values })) + '">';
            });
        }
        hidden.innerHTML = html;
    }

    function render() {
        renderOptions();
        renderTable();
        renderHidden();
    }

    // ------------------------------------------------------------------ events
    card.addEventListener('click', function (e) {
        var t = e.target.closest('button');
        if (!t || !card.contains(t)) return;

        if (t.hasAttribute('data-add-option')) {
            var free = unusedAttributes(-1)[0];
            if (!free || options.length >= maxOptions) return;
            options.forEach(function (o) { if (o.values.length) o.editing = false; });
            options.push({ id: free.id, name: free.name, values: [], editing: true });
            render();
            var input = list.querySelector('[data-value-input="' + (options.length - 1) + '"]');
            if (input) input.focus();
        } else if (t.hasAttribute('data-edit')) {
            options[parseInt(t.getAttribute('data-edit'), 10)].editing = true;
            render();
        } else if (t.hasAttribute('data-done')) {
            var i = parseInt(t.getAttribute('data-done'), 10);
            var pending = list.querySelector('[data-value-input="' + i + '"]');
            if (pending && pending.value.trim()) addValue(i, pending.value);
            if (!options[i].values.length) {
                warning.textContent = 'Add at least one value, or delete the option.';
                warning.hidden = false;
                return;
            }
            options[i].editing = false;
            render();
        } else if (t.hasAttribute('data-delete')) {
            options.splice(parseInt(t.getAttribute('data-delete'), 10), 1);
            render();
        } else if (t.hasAttribute('data-remove-value')) {
            var parts = t.getAttribute('data-remove-value').split(':');
            options[parseInt(parts[0], 10)].values.splice(parseInt(parts[1], 10), 1);
            render();
            var again = list.querySelector('[data-value-input="' + parts[0] + '"]');
            if (again) again.focus();
        }
    });

    card.addEventListener('keydown', function (e) {
        if (!e.target.hasAttribute('data-value-input')) return;
        if (e.key === 'Enter' || e.key === ',') {
            // Enter would otherwise submit the whole product form.
            e.preventDefault();
            var i = parseInt(e.target.getAttribute('data-value-input'), 10);
            if (!e.target.value.trim()) return;
            addValue(i, e.target.value);
            render();
            var input = list.querySelector('[data-value-input="' + i + '"]');
            if (input) input.focus();
        }
    });

    card.addEventListener('change', function (e) {
        if (e.target.hasAttribute('data-attr')) {
            var i = parseInt(e.target.getAttribute('data-attr'), 10);
            var a = attributes.filter(function (x) { return x.id === parseInt(e.target.value, 10); })[0];
            if (a) { options[i].id = a.id; options[i].name = a.name; }
            render();
        } else if (e.target.getAttribute('data-field') === 'image') {
            // renderTable() remembers the new choice and redraws the thumbnail.
            renderTable();
        }
    });

    card.addEventListener('input', function (e) {
        if (e.target.getAttribute('data-field') === 'qty') updateTotal();
    });

    // Enter in the table's inputs must not submit the form either.
    tbody.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') e.preventDefault();
    });

    if (priceInput) priceInput.addEventListener('input', function () {
        tbody.querySelectorAll('[data-field="price"]').forEach(function (input) { input.placeholder = priceInput.value; });
    });

    // New gallery photos become choices.
    var gallery = document.querySelector('.image-gallery');
    if (gallery && window.MutationObserver) {
        new MutationObserver(function () { renderTable(); }).observe(gallery, { childList: true, subtree: true });
    }

    render();
})();

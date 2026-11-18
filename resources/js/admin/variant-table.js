/*
 * Variant table on the admin product form (create and edit).
 *
 * Reads the options the existing form builds (".options" blocks with an ".option-attribute"
 * name and ".variant" chips), makes one row per combination, and keeps what the admin typed
 * when options change. Inputs are named skus[i][...] inside the product form, so the existing
 * save (new FormData(form)) sends them. Source copy: resources/js/admin/variant-table.js.
 */
(function () {
    'use strict';

    var root = document.getElementById('variant-table');
    if (!root) return;

    var form = root.closest('form');
    var optionsWrapper = document.querySelector('.options-wrapper');
    var hasVariant = document.querySelector('.has-variant');
    var tbody = root.querySelector('tbody');
    var totalEl = root.querySelector('[data-total]');
    var warning = root.querySelector('.variant-table__warning');
    var maxOptions = parseInt(root.getAttribute('data-max-options'), 10) || 2;
    var maxVariants = parseInt(root.getAttribute('data-max-variants'), 10) || 100;
    var qtyInput = form ? form.querySelector('input[name="qty"]') : null;
    var qtyBlock = qtyInput ? qtyInput.closest('.form-block') : null;
    var priceInput = form ? form.querySelector('input[name="regular_price"]') : null;

    // What the admin has typed, by combination key, so rebuilding the table never loses it.
    var values = {};
    try {
        JSON.parse(root.getAttribute('data-skus') || '[]').forEach(function (sku) {
            values[sku.key] = {
                price: sku.price === null || sku.price === undefined ? '' : String(sku.price),
                qty: String(sku.qty === null || sku.qty === undefined ? 0 : sku.qty),
                image: sku.image || '',
                sku: sku.sku || ''
            };
        });
    } catch (e) { /* no saved variants */ }

    function text(el) {
        return (el.textContent || '').replace(/\s+/g, ' ').trim();
    }

    /** [{name: 'size', values: ['S','M']}, ...] from the options on the page. */
    function readOptions() {
        if (!optionsWrapper) return [];
        var result = [];
        optionsWrapper.querySelectorAll('.options').forEach(function (block) {
            var nameEl = block.querySelector('.option-attribute');
            if (!nameEl) return;
            var vals = [];
            block.querySelectorAll('.variants-list-wrapper .variant').forEach(function (chip) {
                var v = text(chip);
                if (v && vals.indexOf(v) === -1) vals.push(v);
            });
            result.push({ name: text(nameEl).toLowerCase(), values: vals });
        });
        return result;
    }

    function combinations(options) {
        var combos = [{}];
        options.forEach(function (option) {
            var next = [];
            combos.forEach(function (combo) {
                option.values.forEach(function (value) {
                    var c = {};
                    Object.keys(combo).forEach(function (k) { c[k] = combo[k]; });
                    c[option.name] = value;
                    next.push(c);
                });
            });
            combos = next;
        });
        return combos;
    }

    /** Same as ProductSku::keyFor(): "color:black|size:m". */
    function keyFor(combo) {
        return Object.keys(combo).map(function (k) { return k.trim().toLowerCase(); }).sort().map(function (k) {
            var original = Object.keys(combo).filter(function (o) { return o.trim().toLowerCase() === k; })[0];
            return k + ':' + String(combo[original]).trim().toLowerCase();
        }).join('|');
    }

    function photoChoices() {
        var seen = [];
        document.querySelectorAll('.product-image, .image-gallery img').forEach(function (img) {
            var src = img.getAttribute('src');
            if (src && seen.indexOf(src) === -1) seen.push(src);
        });
        return seen;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function remember() {
        tbody.querySelectorAll('tr[data-key]').forEach(function (tr) {
            var key = tr.getAttribute('data-key');
            values[key] = {
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

    function setSingleQuantityVisible(visible) {
        if (!qtyBlock) return;
        qtyBlock.hidden = !visible;
        qtyInput.disabled = !visible;
    }

    function showWarning(message) {
        warning.textContent = message || '';
        warning.hidden = !message;
    }

    function render() {
        remember();
        var on = hasVariant && hasVariant.checked;
        var options = on ? readOptions().filter(function (o) { return o.values.length; }) : [];

        if (!on || !options.length) {
            root.hidden = true;
            tbody.innerHTML = '';
            setSingleQuantityVisible(true);
            return;
        }

        root.hidden = false;
        setSingleQuantityVisible(false);

        if (readOptions().length > maxOptions) {
            showWarning('A product can have at most ' + maxOptions + ' options (for example Size and Colour). Remove one to continue.');
            tbody.innerHTML = '';
            updateTotal();
            return;
        }
        var combos = combinations(options);
        if (combos.length > maxVariants) {
            showWarning('That makes ' + combos.length + ' variants. Use fewer option values (at most ' + maxVariants + ').');
            tbody.innerHTML = '';
            updateTotal();
            return;
        }
        showWarning('');

        var photos = photoChoices();
        var placeholder = priceInput && priceInput.value ? priceInput.value : '';
        tbody.innerHTML = combos.map(function (combo, i) {
            var key = keyFor(combo);
            var v = values[key] || { price: '', qty: '0', image: '', sku: '' };
            var label = Object.keys(combo).map(function (k) { return combo[k]; }).join(' / ');
            var photoOptions = '<option value="">Product photo</option>' + photos.concat(v.image && photos.indexOf(v.image) === -1 ? [v.image] : []).map(function (src, n) {
                return '<option value="' + escapeHtml(src) + '"' + (src === v.image ? ' selected' : '') + '>Photo ' + (n + 1) + '</option>';
            }).join('');
            return '<tr data-key="' + escapeHtml(key) + '">'
                + '<td class="variant-table__photo">'
                +   '<span class="variant-thumb">' + (v.image ? '<img src="' + escapeHtml(v.image) + '" alt="">' : '<i class="far fa-image" aria-hidden="true"></i>') + '</span>'
                +   '<label class="sr-only" for="sku-image-' + i + '">Photo for ' + escapeHtml(label) + '</label>'
                +   '<select id="sku-image-' + i + '" name="skus[' + i + '][image]" data-field="image">' + photoOptions + '</select>'
                + '</td>'
                + '<td class="variant-table__label">' + escapeHtml(label)
                +   '<input type="hidden" name="skus[' + i + '][options]" value="' + escapeHtml(JSON.stringify(combo)) + '">'
                + '</td>'
                + '<td><label class="sr-only" for="sku-price-' + i + '">Price for ' + escapeHtml(label) + '</label>'
                +   '<input id="sku-price-' + i + '" type="number" min="1" step="0.01" name="skus[' + i + '][price]" data-field="price" value="' + escapeHtml(v.price) + '" placeholder="' + escapeHtml(placeholder) + '"></td>'
                + '<td><label class="sr-only" for="sku-qty-' + i + '">Available for ' + escapeHtml(label) + '</label>'
                +   '<input id="sku-qty-' + i + '" type="number" min="0" step="1" name="skus[' + i + '][qty]" data-field="qty" value="' + escapeHtml(v.qty) + '" required></td>'
                + '<td><label class="sr-only" for="sku-code-' + i + '">SKU for ' + escapeHtml(label) + '</label>'
                +   '<input id="sku-code-' + i + '" type="text" maxlength="100" name="skus[' + i + '][sku]" data-field="sku" value="' + escapeHtml(v.sku) + '"></td>'
                + '</tr>';
        }).join('');
        updateTotal();
    }

    tbody.addEventListener('input', function (e) {
        if (e.target.getAttribute('data-field') === 'qty') updateTotal();
    });
    tbody.addEventListener('change', function (e) {
        if (e.target.getAttribute('data-field') !== 'image') return;
        var thumb = e.target.closest('td').querySelector('.variant-thumb');
        thumb.innerHTML = e.target.value ? '<img src="' + escapeHtml(e.target.value) + '" alt="">' : '<i class="far fa-image" aria-hidden="true"></i>';
    });
    if (priceInput) priceInput.addEventListener('input', function () {
        tbody.querySelectorAll('[data-field="price"]').forEach(function (input) { input.placeholder = priceInput.value; });
    });

    // Rebuild when options or values are added or removed, or the checkbox changes.
    var pending = null;
    function schedule() {
        clearTimeout(pending);
        pending = setTimeout(render, 30);
    }
    if (optionsWrapper && window.MutationObserver) {
        new MutationObserver(schedule).observe(optionsWrapper, { childList: true, subtree: true, characterData: true });
    }
    if (hasVariant) hasVariant.addEventListener('change', schedule);
    var gallery = document.querySelector('.image-gallery');
    if (gallery && window.MutationObserver) {
        new MutationObserver(schedule).observe(gallery, { childList: true, subtree: true });
    }

    render();
})();

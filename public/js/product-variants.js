/*
 * Product page: per-variant price, stock and photo.
 *
 * Works next to the existing option buttons in app.js (".variant-options", which toggles a
 * "selected" class). When the shopper's choices match a variant it shows that variant's price,
 * "Only N left", caps the quantity and switches the photo. Choices with no stock left are
 * struck through and can't be picked. Data comes from <script id="variant-data">.
 */
(function () {
    'use strict';

    var dataEl = document.getElementById('variant-data');
    if (!dataEl) return;

    var data;
    try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }

    function norm(v) { return String(v == null ? '' : v).trim().toLowerCase(); }

    var skus = (data.skus || []).map(function (s) {
        var opts = {};
        Object.keys(s.options || {}).forEach(function (k) { opts[norm(k)] = norm(s.options[k]); });
        return { opts: opts, qty: parseInt(s.qty, 10) || 0, image: s.image || '', priceHtml: s.priceHtml };
    });

    var wrapper = document.querySelector('.pdp-variants');
    var buttons = Array.prototype.slice.call(document.querySelectorAll('.pdp-variants .variant-options'));
    var priceEl = document.querySelector('.pdp-price');
    var stockEl = document.getElementById('pdp-variant-stock');
    var qtyInput = document.getElementById('quantity-input');
    var image = document.getElementById('fullImage');
    var names = [];
    buttons.forEach(function (b) {
        var n = norm(b.getAttribute('name'));
        if (names.indexOf(n) === -1) names.push(n);
    });
    var imageChangedTo = null;

    function selection() {
        var sel = {};
        buttons.forEach(function (b) {
            if (b.classList.contains('selected')) sel[norm(b.getAttribute('name'))] = norm(b.getAttribute('value'));
        });
        return sel;
    }

    function matches(sku, sel) {
        return Object.keys(sel).every(function (k) { return sku.opts[k] === sel[k]; });
    }

    function anyInStock(sel) {
        return skus.some(function (s) { return matches(s, sel) && s.qty > 0; });
    }

    function update() {
        var sel = selection();

        // Strike through choices that can't be bought with the other choices made so far.
        buttons.forEach(function (b) {
            var test = {};
            Object.keys(sel).forEach(function (k) { test[k] = sel[k]; });
            test[norm(b.getAttribute('name'))] = norm(b.getAttribute('value'));
            var ok = anyInStock(test);
            b.classList.toggle('is-soldout', !ok);
            b.setAttribute('aria-disabled', ok ? 'false' : 'true');
            if (ok) { b.removeAttribute('title'); } else { b.setAttribute('title', 'Sold out'); }
        });

        var complete = names.length && names.every(function (n) { return n in sel; });
        var sku = complete ? skus.filter(function (s) { return matches(s, sel); })[0] : null;

        if (sku) {
            if (priceEl && sku.priceHtml) priceEl.innerHTML = sku.priceHtml;
            if (stockEl) {
                if (sku.qty <= 0) {
                    stockEl.textContent = 'Sold out';
                    stockEl.className = 'pdp-stock pdp-stock--out';
                } else if (sku.qty <= (data.threshold || 0)) {
                    stockEl.textContent = 'Only ' + sku.qty + ' left';
                    stockEl.className = 'pdp-stock';
                } else {
                    stockEl.textContent = 'In stock';
                    stockEl.className = 'pdp-stock pdp-stock--ok';
                }
                stockEl.hidden = false;
            }
            if (qtyInput) {
                qtyInput.max = Math.max(1, sku.qty);
                if (parseInt(qtyInput.value, 10) > sku.qty && sku.qty > 0) qtyInput.value = sku.qty;
            }
            if (image && sku.image) {
                image.src = sku.image;
                imageChangedTo = sku.image;
            }
        } else {
            if (priceEl && data.basePriceHtml) priceEl.innerHTML = data.basePriceHtml;
            if (stockEl) stockEl.hidden = true;
            if (qtyInput) qtyInput.removeAttribute('max');
            if (image && imageChangedTo && image.getAttribute('src') === imageChangedTo) {
                image.src = data.baseImage;
                imageChangedTo = null;
            }
        }
    }

    // Stop sold-out choices before app.js selects them.
    document.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('.pdp-variants .variant-options.is-soldout') : null;
        if (b) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    // app.js changes the "selected" classes (choosing, and clearing after add to bag).
    if (wrapper && window.MutationObserver) {
        new MutationObserver(update).observe(wrapper, { attributes: true, attributeFilter: ['class'], subtree: true });
    }

    // Quantity "+" button: don't go past the variant's stock.
    if (qtyInput) {
        qtyInput.addEventListener('change', function () {
            var max = parseInt(qtyInput.max, 10);
            if (max && parseInt(qtyInput.value, 10) > max) qtyInput.value = max;
        });
    }

    update();
})();

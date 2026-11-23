/*
 * Product page: per-variant price, stock and photo.
 *
 * Works next to the existing option buttons in app.js (".variant-options", which toggles a
 * "selected" class). When the shopper's choices match a variant it shows that variant's price,
 * "Only N left", caps the quantity and switches the photo. Choices with no stock left are
 * struck through and can't be picked. Data comes from <script id="variant-data">.
 *
 * The photo switches as soon as a choice has one (e.g. picking a colour before a size), and
 * the main image is looked up each time: the gallery script (image-slider.js) replaces the
 * #fullImage element whenever a thumbnail or arrow is clicked.
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

    /** The photo for the choices made so far: the chosen variant's own, or one that fits. */
    function photoFor(sel, sku) {
        if (sku && sku.image) return sku.image;
        if (!Object.keys(sel).length) return '';
        var exact = firstPhoto(sel);
        if (exact) return exact;
        // No photo for this exact choice (e.g. Black / M): use one that matches one of the
        // choices, in the order the options are shown (so Black / S's photo for Black).
        for (var i = 0; i < names.length; i++) {
            if (!(names[i] in sel)) continue;
            var single = {};
            single[names[i]] = sel[names[i]];
            var found = firstPhoto(single);
            if (found) return found;
        }
        return '';
    }

    /** A photo of a variant matching these choices, preferring one in stock. */
    function firstPhoto(sel) {
        var fitting = skus.filter(function (s) { return s.image && matches(s, sel); });
        var inStock = fitting.filter(function (s) { return s.qty > 0; });
        return (inStock[0] || fitting[0] || {}).image || '';
    }

    function showPhoto(src) {
        var image = document.getElementById('fullImage');
        if (!image) return;
        if (src) {
            if (image.getAttribute('src') !== src) image.src = src;
            imageChangedTo = src;
        } else if (imageChangedTo && image.getAttribute('src') === imageChangedTo) {
            // Back to the product photo, unless the shopper has since picked another one.
            image.src = data.baseImage;
            imageChangedTo = null;
        }
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
        } else {
            if (priceEl && data.basePriceHtml) priceEl.innerHTML = data.basePriceHtml;
            if (stockEl) stockEl.hidden = true;
            if (qtyInput) qtyInput.removeAttribute('max');
        }

        showPhoto(photoFor(sel, sku));
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

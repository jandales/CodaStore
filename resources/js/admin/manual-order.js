/*
 * Admin > Orders > Create order.
 * Customer search, product search with a variant picker, item lines (capped at stock), and a
 * live summary. The server recalculates every price and total; this only helps staff.
 * Source copy: resources/js/admin/manual-order.js.
 */
(function () {
    'use strict';

    var form = document.getElementById('manual-order');
    if (!form) return;

    var peso = function (n) {
        return '₱' + (Math.round(n * 100) / 100).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function debounce(fn, ms) {
        var t; return function () { var a = arguments; clearTimeout(t); t = setTimeout(function () { fn.apply(null, a); }, ms); };
    }
    function getJSON(url, cb) {
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) { if (d) cb(d); })
            .catch(function () {});
    }

    // ------------------------------------------------------------------ customer
    var modeInputs = form.querySelectorAll('input[name="customer_mode"]');
    var userIdInput = form.querySelector('input[name="user_id"]');
    var customerSearch = document.getElementById('customer-search');
    var customerResults = form.querySelector('[data-customer-results]');
    var customerChosen = form.querySelector('[data-customer-chosen]');
    var foundCustomers = [];

    function syncMode() {
        var mode = form.querySelector('input[name="customer_mode"]:checked').value;
        form.querySelectorAll('[data-mode]').forEach(function (el) { el.hidden = el.getAttribute('data-mode') !== mode; });
        if (mode === 'new') userIdInput.value = '';
    }
    modeInputs.forEach(function (i) { i.addEventListener('change', syncMode); });

    function fillAddress(a) {
        if (!a) return;
        ['firstname', 'lastname', 'phone', 'street', 'city', 'region', 'zipcode', 'country'].forEach(function (f) {
            var input = form.querySelector('[name="' + f + '"]');
            if (input && a[f]) input.value = a[f];
        });
    }

    function chooseCustomer(c) {
        userIdInput.value = c.id;
        customerResults.hidden = true;
        customerSearch.value = '';
        customerChosen.hidden = false;
        customerChosen.innerHTML = '<div><strong>' + esc(c.name) + '</strong><span>' + esc([c.email, c.phone].filter(Boolean).join(' · ')) + '</span></div>'
            + '<button type="button" class="link-button" data-clear-customer>Change</button>';
        if (c.address) {
            fillAddress(c.address);
        } else {
            var parts = String(c.name || '').split(' ');
            fillAddress({ firstname: parts.shift(), lastname: parts.join(' '), phone: c.phone });
        }
    }

    customerSearch.addEventListener('input', debounce(function () {
        var q = customerSearch.value.trim();
        if (q.length < 2) { customerResults.hidden = true; return; }
        getJSON(form.getAttribute('data-customers-url') + '?q=' + encodeURIComponent(q), function (d) {
            foundCustomers = d.customers;
            customerResults.innerHTML = d.customers.length
                ? d.customers.map(function (c, i) {
                    return '<li><button type="button" data-customer="' + i + '"><strong>' + esc(c.name) + '</strong><span>' + esc([c.email, c.phone].filter(Boolean).join(' · ')) + '</span></button></li>';
                }).join('')
                : '<li class="mo-empty">No customer found. Switch to "New customer".</li>';
            customerResults.hidden = false;
        });
    }, 250));

    form.addEventListener('click', function (e) {
        var b = e.target.closest('[data-customer]');
        if (b) chooseCustomer(foundCustomers[parseInt(b.getAttribute('data-customer'), 10)]);
        if (e.target.closest('[data-clear-customer]')) {
            userIdInput.value = '';
            customerChosen.hidden = true;
            customerSearch.focus();
        }
    });

    // ------------------------------------------------------------------ items
    var productSearch = document.getElementById('product-search');
    var productResults = form.querySelector('[data-product-results]');
    var linesTable = form.querySelector('[data-lines]');
    var linesBody = linesTable.querySelector('tbody');
    var noLines = form.querySelector('[data-no-lines]');
    var foundProducts = [];
    var lines = [];
    try { lines = JSON.parse(form.getAttribute('data-old-lines') || '[]'); } catch (e) { lines = []; }

    function addLine(product, variant) {
        var stock = variant ? variant.qty : product.stock;
        if (stock <= 0) return;
        var existing = lines.filter(function (l) { return l.product_id === product.id && (l.sku_id || null) === (variant ? variant.id : null); })[0];
        if (existing) {
            existing.qty = Math.min(existing.qty + 1, existing.stock);
        } else {
            lines.push({
                product_id: product.id, name: product.name,
                image: (variant && variant.image) || product.image,
                sku_id: variant ? variant.id : null, variant: variant ? variant.label : null,
                price: variant ? variant.price : product.price, stock: stock, qty: 1
            });
        }
        productResults.hidden = true;
        productSearch.value = '';
        renderLines();
    }

    function renderProducts(list) {
        foundProducts = list;
        if (!list.length) {
            productResults.innerHTML = '<p class="mo-empty">No products found.</p>';
            productResults.hidden = false;
            return;
        }
        productResults.innerHTML = list.map(function (p, i) {
            var head = '<div class="mo-product"><img src="' + esc(p.image) + '" alt=""><div><strong class="capitalize">' + esc(p.name) + '</strong>'
                + '<span>' + esc(p.sku || '') + (p.published ? '' : ' · Draft') + '</span></div>';
            if (!p.variants.length) {
                return head + '<button type="button" class="btn btn-light" data-add="' + i + '"' + (p.stock > 0 ? '' : ' disabled') + '>'
                    + (p.stock > 0 ? 'Add · ' + peso(p.price) + ' · ' + p.stock + ' left' : 'Sold out') + '</button></div>';
            }
            return head + '<div class="mo-variants">' + p.variants.map(function (v, j) {
                return '<button type="button" class="mo-variant" data-add="' + i + '" data-variant="' + j + '"' + (v.qty > 0 ? '' : ' disabled') + '>'
                    + '<strong>' + esc(v.label) + '</strong><span>' + (v.qty > 0 ? peso(v.price) + ' · ' + v.qty + ' left' : 'Sold out') + '</span></button>';
            }).join('') + '</div></div>';
        }).join('');
        productResults.hidden = false;
    }

    productSearch.addEventListener('input', debounce(function () {
        var q = productSearch.value.trim();
        if (!q) { productResults.hidden = true; return; }
        getJSON(form.getAttribute('data-products-url') + '?q=' + encodeURIComponent(q), function (d) { renderProducts(d.products); });
    }, 250));

    productResults.addEventListener('click', function (e) {
        var b = e.target.closest('[data-add]');
        if (!b || b.disabled) return;
        var p = foundProducts[parseInt(b.getAttribute('data-add'), 10)];
        var v = b.hasAttribute('data-variant') ? p.variants[parseInt(b.getAttribute('data-variant'), 10)] : null;
        addLine(p, v);
    });

    function renderLines() {
        linesTable.hidden = !lines.length;
        noLines.hidden = !!lines.length;
        linesBody.innerHTML = lines.map(function (l, i) {
            return '<tr>'
                + '<td><div class="mo-line-item"><img src="' + esc(l.image) + '" alt=""><div><strong class="capitalize">' + esc(l.name) + '</strong>'
                +   (l.variant ? '<span class="inventory-variant__label">' + esc(l.variant) + '</span>' : '') + '<small>' + l.stock + ' in stock</small></div></div>'
                +   '<input type="hidden" name="lines[' + i + '][product_id]" value="' + l.product_id + '">'
                +   (l.sku_id ? '<input type="hidden" name="lines[' + i + '][sku_id]" value="' + l.sku_id + '">' : '')
                + '</td>'
                + '<td>' + peso(l.price) + '</td>'
                + '<td><input type="number" class="mo-qty" name="lines[' + i + '][qty]" min="1" max="' + l.stock + '" value="' + l.qty + '" data-qty="' + i + '" aria-label="Quantity for ' + esc(l.name) + '"></td>'
                + '<td class="text-right">' + peso(l.price * l.qty) + '</td>'
                + '<td><button type="button" class="link-button" data-remove-line="' + i + '" aria-label="Remove ' + esc(l.name) + '">Remove</button></td>'
                + '</tr>';
        }).join('');
        summary();
    }

    linesBody.addEventListener('change', function (e) {
        if (!e.target.hasAttribute('data-qty')) return;
        var l = lines[parseInt(e.target.getAttribute('data-qty'), 10)];
        var n = parseInt(e.target.value, 10);
        l.qty = Math.max(1, Math.min(isNaN(n) ? 1 : n, l.stock));
        renderLines();
    });
    linesBody.addEventListener('click', function (e) {
        var b = e.target.closest('[data-remove-line]');
        if (!b) return;
        lines.splice(parseInt(b.getAttribute('data-remove-line'), 10), 1);
        renderLines();
    });

    // ------------------------------------------------------------------ shipping, discount, payment
    var methodSelect = document.getElementById('shipping_method_id');
    var shippingInput = document.getElementById('shipping_charge');
    var discountValue = document.getElementById('discount_value');
    var discountType = form.querySelector('select[name="discount_type"]');
    var paymentDetails = form.querySelector('[data-payment-details]');

    methodSelect.addEventListener('change', function () {
        shippingInput.value = methodSelect.selectedOptions[0].getAttribute('data-amount');
        summary();
    });
    [shippingInput, discountValue, discountType].forEach(function (el) { el.addEventListener('input', summary); el.addEventListener('change', summary); });

    function syncPayment() {
        var p = form.querySelector('input[name="payment"]:checked').value;
        paymentDetails.hidden = p === 'cod';
        document.getElementById('payment_method').required = p === 'paid';
    }
    form.querySelectorAll('input[name="payment"]').forEach(function (i) { i.addEventListener('change', syncPayment); });

    function summary() {
        var items = lines.reduce(function (t, l) { return t + l.price * l.qty; }, 0);
        var count = lines.reduce(function (t, l) { return t + l.qty; }, 0);
        var shipping = parseFloat(shippingInput.value) || 0;
        var dv = parseFloat(discountValue.value) || 0;
        var discount = discountType.value === 'percent' ? items * Math.min(dv, 100) / 100 : dv;
        discount = Math.min(Math.max(discount, 0), items);
        form.querySelector('[data-sum-count]').textContent = count;
        form.querySelector('[data-sum-items]').textContent = peso(items);
        form.querySelector('[data-sum-shipping]').textContent = peso(shipping);
        form.querySelector('[data-sum-discount]').textContent = '−' + peso(discount);
        form.querySelector('[data-sum-total]').textContent = peso(items + shipping - discount);
    }

    // Enter in a search box must not submit the order.
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && (e.target === productSearch || e.target === customerSearch)) e.preventDefault();
    });

    form.addEventListener('submit', function (e) {
        if (!lines.length) {
            e.preventDefault();
            productSearch.focus();
            alert('Add at least one item.');
        }
    });

    syncMode();
    syncPayment();
    renderLines();
})();

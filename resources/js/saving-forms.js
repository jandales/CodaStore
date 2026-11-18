/*
 * Every page (admin, store and checkout): while something is being saved, show it on the button
 * (spinner + "Please wait…") and stop double clicks, so a slow save (emails, stock checks,
 * payment) never looks frozen.
 *  - Normal forms that save (POST/PUT/DELETE). Search and filter forms (GET) are left alone.
 *  - Admin background saves made with jQuery after a button click (product add/edit, inventory).
 * Opt out with data-no-loading on the form or button. Source copy: resources/js/saving-forms.js.
 */
(function () {
    'use strict';

    function setLoading(button) {
        if (!button || button.classList.contains('is-loading')) return;
        button.style.minWidth = button.offsetWidth + 'px';
        button.setAttribute('data-label', button.tagName === 'INPUT' ? button.value : button.innerHTML);
        if (button.tagName === 'INPUT') {
            button.value = 'Please wait…';
        } else {
            button.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span> Please wait…';
        }
        button.disabled = true;
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');
    }

    function restore(button) {
        if (!button || !button.classList.contains('is-loading')) return;
        if (button.tagName === 'INPUT') button.value = button.getAttribute('data-label');
        else button.innerHTML = button.getAttribute('data-label');
        button.disabled = false;
        button.classList.remove('is-loading');
        button.removeAttribute('aria-busy');
        button.style.minWidth = '';
    }

    // ------------------------------------------------------------------ normal forms

    document.addEventListener('submit', function (e) {
        var form = e.target;
        // Runs after the form's own handlers: skip when one of them stopped it (e.g. a cancelled confirm()).
        if (e.defaultPrevented || !(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading')) return;
        if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return; // searching, not saving

        if (form.getAttribute('data-submitting') === '1') {
            e.preventDefault(); // already on its way
            return;
        }
        form.setAttribute('data-submitting', '1');
        form.setAttribute('aria-busy', 'true');

        var button = e.submitter || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
        if (!button || button.hasAttribute('data-no-loading')) return;

        // A disabled button isn't sent, so keep its name/value (e.g. "action=approve") as a hidden field.
        if (button.name) {
            var keep = document.createElement('input');
            keep.type = 'hidden';
            keep.name = button.name;
            keep.value = button.value;
            form.appendChild(keep);
        }

        // Disable after the browser has read the form, so the submission still goes through.
        setTimeout(function () { setLoading(button); }, 0);
    });

    // ------------------------------------------------------------------ background (jQuery) saves

    var lastClick = { button: null, at: 0 };
    var leaving = false;

    document.addEventListener('click', function (e) {
        var button = e.target.closest && e.target.closest('button, input[type="submit"], .btn');
        if (button && !button.hasAttribute('data-no-loading')) lastClick = { button: button, at: Date.now() };
    }, true);
    window.addEventListener('beforeunload', function () { leaving = true; });

    // Admin only: on the store these are small icon buttons (wishlist, cart +/−) with their own feedback.
    if (window.jQuery && document.body.classList.contains('admin-body')) {
        jQuery(document).ajaxSend(function (event, xhr, settings) {
            var type = String(settings.type || settings.method || 'GET').toUpperCase();
            // Only saves the person just asked for with a button.
            if (type === 'GET' || !lastClick.button || Date.now() - lastClick.at > 1000) return;
            xhr._savingButton = lastClick.button;
            setLoading(lastClick.button);
        });
        jQuery(document).ajaxError(function (event, xhr) {
            restore(xhr._savingButton); // show the button again with the errors
        });
        jQuery(document).ajaxSuccess(function (event, xhr) {
            var button = xhr._savingButton;
            // A save that moves on to the next page keeps the spinner until it loads.
            if (button) setTimeout(function () { if (!leaving) restore(button); }, 600);
        });
    }

    // ------------------------------------------------------------------ back button

    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        leaving = false;
        document.querySelectorAll('form[data-submitting]').forEach(function (form) {
            form.removeAttribute('data-submitting');
            form.removeAttribute('aria-busy');
        });
        document.querySelectorAll('.is-loading[data-label]').forEach(restore);
    });
})();

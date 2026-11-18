/*
 * Every page (admin, store and checkout): while a form is being saved, show it on the button
 * (spinner + "Please wait…") and stop double clicks, so a slow save (emails, stock checks,
 * payment) never looks frozen. Search and filter forms (GET) are left alone.
 * Opt out with data-no-loading on the form. Source copy: resources/js/saving-forms.js.
 */
(function () {
    'use strict';

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
        if (!button) return;

        // A disabled button isn't sent, so keep its name/value (e.g. "action=approve") as a hidden field.
        if (button.name) {
            var keep = document.createElement('input');
            keep.type = 'hidden';
            keep.name = button.name;
            keep.value = button.value;
            form.appendChild(keep);
        }

        // Disable after the browser has read the form, so the submission still goes through.
        setTimeout(function () {
            button.style.minWidth = button.offsetWidth + 'px';
            button.setAttribute('data-label', button.tagName === 'INPUT' ? button.value : button.innerHTML);
            if (button.tagName === 'INPUT') {
                button.value = 'Please wait…';
            } else {
                button.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span> Please wait…';
            }
            button.disabled = true;
            button.classList.add('is-loading');
        }, 0);
    });

    // Coming back with the browser's Back button: put the buttons back as they were.
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('form[data-submitting]').forEach(function (form) {
            form.removeAttribute('data-submitting');
            form.removeAttribute('aria-busy');
        });
        document.querySelectorAll('.is-loading[data-label]').forEach(function (button) {
            if (button.tagName === 'INPUT') button.value = button.getAttribute('data-label');
            else button.innerHTML = button.getAttribute('data-label');
            button.disabled = false;
            button.classList.remove('is-loading');
            button.style.minWidth = '';
        });
    });
})();

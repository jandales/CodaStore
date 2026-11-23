/**
 * Media card on the product form (resources/views/admin/products/_media.blade.php).
 *
 * One ordered list of photos: the first one is the main image, the rest are the gallery.
 *  - Drop files on the card (or "choose files") to upload them; each gets its own progress bar.
 *  - Drag photos to reorder them; Make main / Move earlier / Move later do the same from the keyboard.
 *  - Remove: a saved photo is sent as deleted, so it is removed when the product is saved.
 *
 * Writes media=1 and images[]={"id","path","deleted"} (in display order) inside #form, so the
 * normal product save sends them. Also exposes window.CodaMedia for the variant photo picker
 * and fires "media:change" on document whenever the photos change.
 *
 * Standalone (no build step): the compiled admin script can't be rebuilt on this machine's Node
 * version. Source: resources/js/admin/media.js, copied to public/js/admin-media.js.
 */
(function () {
    'use strict';

    var card = document.getElementById('media-card');
    if (!card) return;

    var list = card.querySelector('[data-media-list]');
    var drop = card.querySelector('[data-media-drop]');
    var fileInput = card.querySelector('[data-media-file]');
    var chooseBtn = card.querySelector('[data-media-choose]');
    var errorsBox = card.querySelector('[data-media-errors]');
    var statusBox = card.querySelector('[data-media-status]');
    var inputsBox = card.querySelector('[data-media-inputs]');
    var uploadUrl = card.getAttribute('data-upload-url');
    var maxBytes = (parseInt(card.getAttribute('data-max-kb'), 10) || 5120) * 1024;
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
    var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    // { key, id, path, deleted, uploading, progress, preview }
    var items = [];
    var nextKey = 1;
    var dragKey = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function visible() {
        return items.filter(function (item) { return !item.deleted; });
    }

    function find(key) {
        for (var i = 0; i < items.length; i++) if (items[i].key === key) return items[i];
        return null;
    }

    function announce(text) {
        if (!statusBox) return;
        statusBox.textContent = '';
        setTimeout(function () { statusBox.textContent = text; }, 50);
    }

    function showErrors(messages) {
        if (!messages.length) {
            errorsBox.hidden = true;
            errorsBox.innerHTML = '';
            return;
        }
        errorsBox.innerHTML = messages.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('');
        errorsBox.hidden = false;
    }

    // ---- State -> page --------------------------------------------------------------

    function photos() {
        return visible().filter(function (item) { return !item.uploading; }).map(function (item) { return item.path; });
    }

    function writeInputs() {
        var html = '<input type="hidden" name="media" value="1">';
        items.forEach(function (item) {
            if (item.uploading) return;
            if (item.deleted && !item.id) return; // never saved: nothing to remove
            var value = JSON.stringify({ id: item.id || null, path: item.path, deleted: item.deleted ? 1 : 0 });
            html += '<input type="hidden" name="images[]" value="' + esc(value) + '">';
        });
        inputsBox.innerHTML = html;
    }

    function tileHtml(item, index, count) {
        var label = index === 0 ? 'Main image' : 'Photo ' + (index + 1);
        if (item.uploading) {
            return '<li class="media-tile media-tile--uploading" data-key="' + item.key + '">'
                + '<img src="' + esc(item.preview) + '" alt="">'
                + '<div class="media-tile__progress" role="progressbar" aria-label="Uploading" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + item.progress + '">'
                +   '<span style="width:' + item.progress + '%"></span>'
                + '</div>'
                + '</li>';
        }
        return '<li class="media-tile' + (index === 0 ? ' media-tile--main' : '') + '" data-key="' + item.key + '" draggable="true">'
            + '<img src="' + esc(item.path) + '" alt="' + esc(label) + '" draggable="false">'
            + (index === 0 ? '<span class="media-tile__badge"><i class="fas fa-star" aria-hidden="true"></i> Main</span>' : '')
            + '<div class="media-tile__actions">'
            +   (index > 0 ? '<button type="button" class="media-tile__btn" data-action="main" aria-label="Make ' + esc(label.toLowerCase()) + ' the main image" title="Make main"><i class="fas fa-star" aria-hidden="true"></i></button>' : '')
            +   (index > 0 ? '<button type="button" class="media-tile__btn" data-action="left" aria-label="Move ' + esc(label.toLowerCase()) + ' earlier" title="Move earlier"><i class="fas fa-arrow-left" aria-hidden="true"></i></button>' : '')
            +   (index < count - 1 ? '<button type="button" class="media-tile__btn" data-action="right" aria-label="Move ' + esc(label.toLowerCase()) + ' later" title="Move later"><i class="fas fa-arrow-right" aria-hidden="true"></i></button>' : '')
            +   '<button type="button" class="media-tile__btn media-tile__btn--remove" data-action="remove" aria-label="Remove ' + esc(label.toLowerCase()) + '" title="Remove"><i class="fas fa-times" aria-hidden="true"></i></button>'
            + '</div>'
            + '</li>';
    }

    function render(focus) {
        var shown = visible();
        list.innerHTML = shown.map(function (item, i) { return tileHtml(item, i, shown.length); }).join('');
        list.hidden = shown.length === 0;
        card.classList.toggle('media-card--empty', shown.length === 0);

        if (focus) {
            var tile = list.querySelector('[data-key="' + focus.key + '"]');
            var target = tile && (tile.querySelector('[data-action="' + focus.action + '"]') || tile.querySelector('[data-action]'));
            if (target) target.focus();
            else if (chooseBtn) chooseBtn.focus();
        }
    }

    function changed(focus) {
        render(focus);
        writeInputs();
        document.dispatchEvent(new CustomEvent('media:change', { detail: { photos: photos() } }));
    }

    // ---- Reordering -----------------------------------------------------------------

    /** Moves the item with this key to a position among the visible items. */
    function moveTo(key, position) {
        var item = find(key);
        if (!item) return;
        var shown = visible().filter(function (other) { return other !== item; });
        position = Math.max(0, Math.min(position, shown.length));
        shown.splice(position, 0, item);
        // Deleted items keep their place at the end; only their ids matter.
        items = shown.concat(items.filter(function (other) { return other.deleted; }));
    }

    function indexOf(key) {
        var shown = visible();
        for (var i = 0; i < shown.length; i++) if (shown[i].key === key) return i;
        return -1;
    }

    list.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;
        var key = parseInt(btn.closest('[data-key]').getAttribute('data-key'), 10);
        var index = indexOf(key);
        var action = btn.getAttribute('data-action');

        if (action === 'main') {
            moveTo(key, 0);
            changed({ key: key, action: 'right' });
            announce('Main image changed.');
        } else if (action === 'left') {
            moveTo(key, index - 1);
            changed({ key: key, action: index - 1 === 0 ? 'right' : 'left' });
            announce('Moved to position ' + index + '.');
        } else if (action === 'right') {
            moveTo(key, index + 1);
            changed({ key: key, action: 'right' });
            announce('Moved to position ' + (index + 2) + '.');
        } else if (action === 'remove') {
            var item = find(key);
            if (item.id) item.deleted = true;
            else items = items.filter(function (other) { return other !== item; });
            var next = visible()[Math.min(index, visible().length - 1)];
            changed(next ? { key: next.key, action: 'remove' } : { key: -1 });
            announce('Photo removed. It is deleted when you save the product.');
        }
    });

    function clearMarkers() {
        list.querySelectorAll('.media-tile--before, .media-tile--after').forEach(function (el) {
            el.classList.remove('media-tile--before', 'media-tile--after');
        });
    }

    function isFileDrag(e) {
        var types = e.dataTransfer && e.dataTransfer.types;
        return !!types && Array.prototype.indexOf.call(types, 'Files') !== -1;
    }

    list.addEventListener('dragstart', function (e) {
        var tile = e.target.closest('.media-tile[draggable="true"]');
        if (!tile) return;
        dragKey = parseInt(tile.getAttribute('data-key'), 10);
        tile.classList.add('media-tile--dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(dragKey));
    });

    list.addEventListener('dragover', function (e) {
        if (dragKey === null) return;
        var tile = e.target.closest('.media-tile');
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        clearMarkers();
        if (!tile || parseInt(tile.getAttribute('data-key'), 10) === dragKey) return;
        var rect = tile.getBoundingClientRect();
        tile.classList.add(e.clientX < rect.left + rect.width / 2 ? 'media-tile--before' : 'media-tile--after');
    });

    list.addEventListener('drop', function (e) {
        if (dragKey === null) return;
        e.preventDefault();
        var tile = e.target.closest('.media-tile');
        var key = dragKey;
        if (tile && parseInt(tile.getAttribute('data-key'), 10) !== key) {
            var target = indexOf(parseInt(tile.getAttribute('data-key'), 10));
            var rect = tile.getBoundingClientRect();
            var after = e.clientX >= rect.left + rect.width / 2;
            var from = indexOf(key);
            var position = target + (after ? 1 : 0);
            if (from < position) position -= 1;
            moveTo(key, position);
            changed();
            announce(position === 0 ? 'Main image changed.' : 'Moved to position ' + (position + 1) + '.');
        }
        dragKey = null;
        clearMarkers();
    });

    list.addEventListener('dragend', function () {
        dragKey = null;
        clearMarkers();
        list.querySelectorAll('.media-tile--dragging').forEach(function (el) { el.classList.remove('media-tile--dragging'); });
    });

    // ---- Uploading ------------------------------------------------------------------

    function validate(files) {
        var ok = [];
        var problems = [];
        Array.prototype.forEach.call(files, function (file) {
            if (allowedTypes.indexOf(file.type) === -1) {
                problems.push(file.name + ': only JPG, PNG or WebP photos can be uploaded.');
            } else if (file.size > maxBytes) {
                problems.push(file.name + ': each photo must be ' + Math.round(maxBytes / 1048576) + ' MB or smaller.');
            } else {
                ok.push(file);
            }
        });
        return { ok: ok, problems: problems };
    }

    function errorFrom(xhr, file) {
        try {
            var json = JSON.parse(xhr.responseText);
            if (json.errors) {
                var first = Object.keys(json.errors)[0];
                if (first && json.errors[first][0]) return file.name + ': ' + json.errors[first][0];
            }
            if (json.message && xhr.status !== 500) return file.name + ': ' + json.message;
        } catch (ignore) { /* not JSON */ }
        if (xhr.status === 413) return file.name + ': the photo is too large.';
        return file.name + ': the upload failed. Please try again.';
    }

    /** Uploads one file; resolves with the saved photo path, or null when it failed. */
    function uploadOne(file, problems) {
        var item = { key: nextKey++, id: null, path: '', deleted: false, uploading: true, progress: 0, preview: URL.createObjectURL(file) };
        items.push(item);

        return new Promise(function (resolve) {
            var data = new FormData();
            data.append('_token', token);
            data.append('images[]', file);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', function (e) {
                if (!e.lengthComputable) return;
                item.progress = Math.round((e.loaded / e.total) * 100);
                var bar = list.querySelector('[data-key="' + item.key + '"] .media-tile__progress');
                if (bar) {
                    bar.setAttribute('aria-valuenow', item.progress);
                    bar.firstElementChild.style.width = item.progress + '%';
                }
            });

            xhr.addEventListener('load', function () {
                URL.revokeObjectURL(item.preview);
                var photo = null;
                if (xhr.status >= 200 && xhr.status < 300) {
                    try { photo = (JSON.parse(xhr.responseText).images || [])[0] || null; } catch (ignore) { photo = null; }
                }
                if (photo) {
                    item.id = photo.id;
                    item.path = photo.path;
                    item.uploading = false;
                    resolve(photo.path);
                } else {
                    problems.push(errorFrom(xhr, file));
                    items = items.filter(function (other) { return other !== item; });
                    resolve(null);
                }
            });

            xhr.addEventListener('error', function () {
                URL.revokeObjectURL(item.preview);
                problems.push(file.name + ': the upload failed. Check your connection and try again.');
                items = items.filter(function (other) { return other !== item; });
                resolve(null);
            });

            xhr.send(data);
        });
    }

    /** Uploads files and adds them to the end of Media. Resolves with the new photo paths. */
    function upload(files) {
        var checked = validate(files);
        var problems = checked.problems.slice();
        showErrors(problems);
        if (!checked.ok.length) return Promise.resolve([]);

        var pending = checked.ok.map(function (file) { return uploadOne(file, problems); });
        render();
        announce('Uploading ' + pending.length + (pending.length === 1 ? ' photo…' : ' photos…'));

        return Promise.all(pending).then(function (paths) {
            var added = paths.filter(Boolean);
            showErrors(problems);
            changed();
            if (added.length) announce(added.length + (added.length === 1 ? ' photo added.' : ' photos added.'));
            return added;
        });
    }

    chooseBtn.addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () {
        if (fileInput.files.length) upload(fileInput.files);
        fileInput.value = '';
    });

    // The whole card accepts dropped files.
    var depth = 0;
    card.addEventListener('dragenter', function (e) {
        if (!isFileDrag(e)) return;
        e.preventDefault();
        depth++;
        drop.classList.add('media-drop--active');
    });
    card.addEventListener('dragover', function (e) {
        if (!isFileDrag(e)) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    card.addEventListener('dragleave', function (e) {
        if (!isFileDrag(e)) return;
        depth = Math.max(0, depth - 1);
        if (depth === 0) drop.classList.remove('media-drop--active');
    });
    card.addEventListener('drop', function (e) {
        if (!isFileDrag(e)) return;
        e.preventDefault();
        depth = 0;
        drop.classList.remove('media-drop--active');
        if (e.dataTransfer.files.length) upload(e.dataTransfer.files);
    });

    // A file dropped next to the card must not open in the browser and leave the form.
    ['dragover', 'drop'].forEach(function (type) {
        window.addEventListener(type, function (e) {
            if (isFileDrag(e) && !card.contains(e.target)) e.preventDefault();
        });
    });

    // ---- Start ----------------------------------------------------------------------

    var initial = [];
    try { initial = JSON.parse(card.getAttribute('data-media') || '[]'); } catch (ignore) { initial = []; }
    items = initial.map(function (photo) {
        return { key: nextKey++, id: photo.id || null, path: photo.path, deleted: false, uploading: false, progress: 100, preview: '' };
    });

    window.CodaMedia = { photos: photos, upload: upload };

    render();
    writeInputs();
    document.dispatchEvent(new CustomEvent('media:change', { detail: { photos: photos() } }));
})();

{{--
    Media card on the product form (create and edit). One list of photos: the first one is the
    main image (shop cards), the rest are the gallery. Drop files to upload them, drag photos to
    reorder. Built by public/js/admin-media.js, which writes the hidden images[] inputs (in
    display order) and media=1 inside the product form, so the normal save sends them.
--}}
@php
    $editing = isset($product) && $product->exists;

    $mediaItems = $editing
        ? $product->photos->map(fn ($photo) => ['id' => $photo->id, 'path' => $photo->path])->values()
        : collect();

    // Products from before the Media card: the main image was never a gallery photo. Show it
    // first so it stays the main image; saving turns it into a photo.
    if ($editing && \App\Models\Product::isUsableImage($product->imagePath)
        && !$mediaItems->contains('path', $product->imagePath)) {
        $mediaItems->prepend(['id' => null, 'path' => $product->imagePath]);
    } elseif ($editing && $mediaItems->isNotEmpty()) {
        // Put the current main image first, as the shop shows it.
        $main = $mediaItems->firstWhere('path', $product->imagePath);
        if ($main) {
            $mediaItems = $mediaItems->reject(fn ($item) => $item['path'] === $product->imagePath)->prepend($main)->values();
        }
    }
@endphp
<div class="panel media-card" id="media-card"
     data-media="{{ $mediaItems->values()->toJson() }}"
     data-upload-url="{{ url('/admin/image/uploads') }}"
     data-max-kb="{{ \App\Http\Controllers\Admin\ImageController::MAX_KB }}">
    <div class="panel-heading">
        <label class="panel-title" id="media-title">Media</label>
    </div>
    <div class="panel-body">
        <div class="media-drop" data-media-drop>
            <input type="file" id="media-file" accept="image/jpeg,image/png,image/webp" multiple hidden data-media-file>
            <i class="fas fa-cloud-upload-alt media-drop__icon" aria-hidden="true"></i>
            <p class="media-drop__text">
                Drag photos here or
                <button type="button" class="media-drop__choose" data-media-choose>choose files</button>
            </p>
            <p class="media-drop__hint">JPG, PNG or WebP, up to 5 MB each.</p>
        </div>

        <ul class="media-errors" data-media-errors role="alert" hidden></ul>

        <ul class="media-grid" data-media-list aria-labelledby="media-title"></ul>

        <p class="form-help media-help" data-media-help>The first photo is the main image, shown on shop pages. Drag photos to change the order.</p>

        <p class="sr-only" data-media-status role="status" aria-live="polite"></p>

        <div data-media-inputs hidden></div>
    </div>
</div>

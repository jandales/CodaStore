{{-- Category form, shared by the Add and Edit pages. --}}
@php $editing = isset($category) && $category->exists; @endphp
<form action="{{ $editing ? route('admin.categories.update', [$category->slug]) : route('categories.store') }}" method="post" enctype="multipart/form-data" class="panel category-form">
    @csrf
    @if ($editing) @method('put') @endif
    <div class="panel-body">
        <div class="form-block">
            <label for="category-name">Name</label>
            <input type="text" id="category-name" name="name" maxlength="100" required value="{{ old('name', $category->name ?? '') }}" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')<div id="name-error" class="alert alert-danger mt10">{{ $message }}</div>@enderror
        </div>

        <div class="form-block">
            <label for="category-description">Description <span class="label-hint">(optional)</span></label>
            <textarea id="category-description" name="description" rows="3" maxlength="500">{{ old('description', $category->description ?? '') }}</textarea>
            @error('description')<div class="alert alert-danger mt10">{{ $message }}</div>@enderror
        </div>

        <div class="form-block">
            <label>Image <span class="label-hint">(shown on the home page and shop)</span></label>
            @php $image = $category->image ?? null; @endphp
            <div class="image-holder category-image">
                <img id="image-to-upload" class="{{ $image ? '' : 'hidden' }}" src="{{ $image }}" alt="">
                <span id="remove-image" class="{{ $image ? '' : 'hidden' }}" role="button" aria-label="Remove chosen image"><i class="fa fa-times" aria-hidden="true"></i></span>
            </div>
            <label class="file-uploader mt-1">
                {{ $image ? 'Change image' : 'Choose image' }}
                <input type="file" id="file-input" name="image" accept="image/*" style="display:none;">
            </label>
            @error('image')<div class="alert alert-danger mt10">{{ $message }}</div>@enderror
        </div>

        <div class="flex gap10 mt20">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add category' }}</button>
            <a href="{{ route('admin.categories') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</form>

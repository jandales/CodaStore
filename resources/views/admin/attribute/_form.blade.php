{{-- Attribute form, shared by the Add and Edit pages. --}}
@php $editing = isset($attribute) && $attribute->exists; @endphp
<form action="{{ $editing ? route('admin.attributes.update', [$attribute->slug]) : route('admin.attributes.store') }}" method="post" class="panel category-form">
    @csrf
    @if ($editing) @method('put') @endif
    <div class="panel-body">
        <div class="form-block">
            <label for="attribute-name">Name</label>
            <input type="text" id="attribute-name" name="name" maxlength="100" required placeholder="e.g. Size, Colour" value="{{ old('name', $attribute->name ?? '') }}" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            <p class="form-help">Shown as an option in the product Variants card, for example Size or Colour.</p>
            @error('name')<div id="name-error" class="alert alert-danger mt10">{{ $message }}</div>@enderror
        </div>

        <div class="form-block">
            <label for="attribute-description">Description <span class="label-hint">(optional)</span></label>
            <textarea id="attribute-description" name="description" rows="3" maxlength="500">{{ old('description', $attribute->description ?? '') }}</textarea>
            @error('description')<div class="alert alert-danger mt10">{{ $message }}</div>@enderror
        </div>

        <div class="flex gap10 mt20">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add attribute' }}</button>
            <a href="{{ route('admin.attributes') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</form>

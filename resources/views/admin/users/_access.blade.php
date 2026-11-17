@php
    $currentRole = (int) old('role', $user->role ?? \App\Models\Admin::ROLE_STAFF);
    $currentPermissions = old('permissions', $user->permissions ?? ['orders']);
    $isSelf = isset($user) && $user->id === auth('admin')->id();
@endphp
<fieldset class="access-fieldset">
    <legend>Access</legend>
    <div class="access-roles">
        <label class="access-role">
            <input type="radio" name="role" value="{{ \App\Models\Admin::ROLE_STAFF }}" {{ $currentRole !== \App\Models\Admin::ROLE_ADMINISTRATOR ? 'checked' : '' }} {{ $isSelf ? 'disabled' : '' }}>
            <span><strong>Staff</strong><small>Only the areas ticked below</small></span>
        </label>
        <label class="access-role">
            <input type="radio" name="role" value="{{ \App\Models\Admin::ROLE_ADMINISTRATOR }}" {{ $currentRole === \App\Models\Admin::ROLE_ADMINISTRATOR ? 'checked' : '' }} {{ $isSelf ? 'disabled' : '' }}>
            <span><strong>Administrator</strong><small>Everything, including staff accounts and the activity log</small></span>
        </label>
    </div>
    @if ($isSelf)
        <p class="form-help">You can't change your own access. Ask another administrator.</p>
    @endif

    <div class="access-permissions" data-staff-only>
        @foreach (\App\Models\Admin::PERMISSIONS as $key => [$label, $help])
            <label class="access-permission">
                <input type="checkbox" name="permissions[]" value="{{ $key }}" {{ in_array($key, (array) $currentPermissions, true) ? 'checked' : '' }} {{ $isSelf ? 'disabled' : '' }}>
                <span><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
            </label>
        @endforeach
    </div>
    @error('permissions')<div class="alert alert-danger mt10">{{ $message }}</div>@enderror
    @error('permissions.*')<div class="alert alert-danger mt10">{{ $message }}</div>@enderror
</fieldset>
<script>
    (function () {
        var box = document.currentScript.previousElementSibling;
        var list = box.querySelector('[data-staff-only]');
        function sync() {
            var admin = box.querySelector('input[name="role"]:checked');
            list.hidden = admin && admin.value === '{{ \App\Models\Admin::ROLE_ADMINISTRATOR }}';
        }
        box.addEventListener('change', sync);
        sync();
    })();
</script>

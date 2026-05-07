@php $siteKey = config('services.turnstile.site'); @endphp

@if ($siteKey)
<div class="mb-2">
    <input type="hidden" name="cf-turnstile-response" id="cf-turnstile-token">
    <div class="cf-turnstile"
         data-sitekey="{{ $siteKey }}"
         data-execution="execute"
         data-appearance="interaction-only"
         data-response-field="false"
         data-callback="onTurnstileVerified"
         data-error-callback="onTurnstileError"></div>
    @error('cf-turnstile-response')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
@endif

@pushOnce('scripts')
<script>
var _turnstileForm = null;

function onTurnstileVerified(token) {
    var el = document.getElementById('cf-turnstile-token');
    if (el) el.value = token;
    if (_turnstileForm) {
        var form = _turnstileForm;
        _turnstileForm = null;
        form.submit();
    }
}

function onTurnstileError() {
    _turnstileForm = null;
}

function submitWithTurnstile(form) {
    if (!window.turnstile) {
        form.submit();
        return;
    }
    _turnstileForm = form;
    window.turnstile.reset();
    window.turnstile.execute('.cf-turnstile');
}
</script>
@if ($siteKey)
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
@endif
@endPushOnce

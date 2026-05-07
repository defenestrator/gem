@php $siteKey = config('services.turnstile.site'); @endphp

@if ($siteKey)
<div class="mb-4">
    {{-- We own this input so Turnstile widget reset (e.g. Safari password dialogs) can't clear the token --}}
    <input type="hidden" name="cf-turnstile-response" id="cf-turnstile-token">
    <div class="cf-turnstile"
         data-sitekey="{{ $siteKey }}"
         data-response-field="false"
         data-callback="onTurnstileVerified"
         data-error-callback="onTurnstileError"></div>
    @error('cf-turnstile-response')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@pushOnce('scripts')
<script>
function onTurnstileVerified(token) {
    var el = document.getElementById('cf-turnstile-token');
    if (el) el.value = token;
    document.dispatchEvent(new CustomEvent('turnstile:verified'));
}
function onTurnstileError() {
    // Widget may reset due to browser events (Safari password dialogs, visibility changes).
    // The issued token is still valid — don't block submission. Server validates.
    document.dispatchEvent(new CustomEvent('turnstile:error'));
}
</script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
@endPushOnce

@else
{{-- No site key (local dev): unblock submit immediately --}}
<script>requestAnimationFrame(() => document.dispatchEvent(new CustomEvent('turnstile:verified')));</script>
@endif

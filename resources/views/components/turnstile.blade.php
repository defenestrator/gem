@php $siteKey = config('services.turnstile.site'); @endphp

@if ($siteKey)
<div class="mb-4">
    <div class="cf-turnstile"
         data-sitekey="{{ $siteKey }}"
         data-callback="onTurnstileVerified"
         data-expired-callback="onTurnstileExpired"
         data-error-callback="onTurnstileError"></div>
    @error('cf-turnstile-response')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@pushOnce('scripts')
<script>
function onTurnstileVerified() {
    document.dispatchEvent(new CustomEvent('turnstile:verified'));
}
function onTurnstileExpired() {
    document.dispatchEvent(new CustomEvent('turnstile:expired'));
}
function onTurnstileError() {
    document.dispatchEvent(new CustomEvent('turnstile:error'));
}
</script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
@endPushOnce

@else
{{-- No site key configured (local dev): unblock submit immediately --}}
<script>requestAnimationFrame(() => document.dispatchEvent(new CustomEvent('turnstile:verified')));</script>
@endif

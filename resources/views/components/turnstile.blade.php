@php $siteKey = config('services.turnstile.site'); @endphp
@if ($siteKey)
<div class="mb-6">
    <div class="cf-turnstile" data-sitekey="{{ $siteKey }}"></div>
    @error('cf-turnstile-response')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@pushOnce('scripts')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endPushOnce
@endif

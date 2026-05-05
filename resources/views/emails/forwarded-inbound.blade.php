<x-mail::message>

**Forwarded Email**

**From:** {{ $from }}  
**To:** {{ $to }}

---

@if($html)
{!! $html !!}
@elseif($text)
{{ $text }}
@else
(No content)
@endif

</x-mail::message>

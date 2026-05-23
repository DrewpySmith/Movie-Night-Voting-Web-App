<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<span style="color:#ef4444">MOVIE</span> NIGHT
</x-mail::header>
</x-slot:header>

<table style="width:100%;margin-bottom:24px">
<tr>
<td style="text-align:center;padding:16px 0">
<span style="font-size:48px;line-height:1">{{ $emoji ?? '🔔' }}</span>
</td>
</tr>
</table>

<h1 style="color:#fafafa;font-size:22px;font-weight:800;letter-spacing:-0.02em;margin:0 0 8px 0;text-align:center">
{{ $actor_name ? $actor_name . '!' : 'Hey!' }}
</h1>

<p style="color:#d4d4d8;font-size:16px;line-height:1.6;margin:0 0 24px 0;text-align:center">
{{ $message }}
</p>

@if($movie_title)
<table style="width:100%;margin-bottom:12px">
<tr>
<td style="background-color:#18181b;border-left:4px solid #ef4444;border-radius:4px;padding:14px 18px">
<p style="color:#a1a1aa;font-size:13px;font-weight:700;margin:0 0 4px 0;text-transform:uppercase;letter-spacing:0.05em">Movie</p>
<p style="color:#fafafa;font-size:16px;font-weight:600;margin:0">{{ $movie_title }}</p>
</td>
</tr>
</table>
@endif

@if($room_title)
<table style="width:100%;margin-bottom:12px">
<tr>
<td style="background-color:#18181b;border-left:4px solid #6366f1;border-radius:4px;padding:14px 18px">
<p style="color:#a1a1aa;font-size:13px;font-weight:700;margin:0 0 4px 0;text-transform:uppercase;letter-spacing:0.05em">Room</p>
<p style="color:#fafafa;font-size:16px;font-weight:600;margin:0">{{ $room_title }}</p>
</td>
</tr>
</table>
@endif

@if($vote_type)
<table style="width:100%;margin-bottom:12px">
<tr>
<td style="background-color:#18181b;border-left:4px solid #22c55e;border-radius:4px;padding:14px 18px">
<p style="color:#fafafa;font-size:16px;font-weight:600;margin:0">
@if($vote_type === 'up') 👍 Upvoted @else 👎 Downvoted @endif
</p>
</td>
</tr>
</table>
@endif

@if($invitee_email)
<table style="width:100%;margin-bottom:12px">
<tr>
<td style="background-color:#18181b;border-left:4px solid #f59e0b;border-radius:4px;padding:14px 18px">
<p style="color:#a1a1aa;font-size:13px;font-weight:700;margin:0 0 4px 0;text-transform:uppercase;letter-spacing:0.05em">Sent To</p>
<p style="color:#fafafa;font-size:16px;font-weight:600;margin:0">{{ $invitee_email }}</p>
</td>
</tr>
</table>
@endif

<table style="width:100%;margin:32px 0">
<tr>
<td style="text-align:center">
<table style="display:inline-block">
<tr>
<td style="background-color:#ef4444;border-radius:9999px;padding:12px 32px;text-align:center">
<a href="{{ $actionUrl }}" style="color:#fff;font-size:15px;font-weight:700;text-decoration:none;display:inline-block">View Room</a>
</td>
</tr>
</table>
</td>
</tr>
</table>

<p style="color:#71717a;font-size:14px;line-height:1.5;margin:0;text-align:center">
Cheers,<br>
<strong style="color:#d4d4d8">The Movie Night Team</strong>
</p>

<x-slot:subcopy>
<x-mail::subcopy>
<span style="color:#71717a;font-size:13px">
If you're having trouble clicking the "View Room" button, copy and paste the URL below into your web browser:<br>
<span class="break-all" style="color:#ef4444">[{{ $actionUrl }}]({{ $actionUrl }})</span>
</span>
</x-mail::subcopy>
</x-slot:subcopy>

<x-slot:footer>
<x-mail::footer>
<span style="color:#52525b;font-size:12px">
&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
</span>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

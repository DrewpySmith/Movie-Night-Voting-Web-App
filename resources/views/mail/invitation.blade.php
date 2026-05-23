<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<span style="color:#ef4444">MOVIE</span> NIGHT
</x-mail::header>
</x-slot:header>

<table style="width:100%;margin-bottom:24px">
<tr>
<td style="text-align:center;padding:16px 0">
<span style="font-size:48px;line-height:1">🎬</span>
</td>
</tr>
</table>

<h1 style="color:#fafafa;font-size:22px;font-weight:800;letter-spacing:-0.02em;margin:0 0 16px 0;text-align:center">
You're Invited!
</h1>

<p style="color:#d4d4d8;font-size:16px;line-height:1.6;margin:0 0 24px 0;text-align:center">
<strong style="color:#fafafa">{{ $inviter->name }}</strong> invited you to join<br>
<strong style="color:#fafafa">{{ $room->title }}</strong> for a Movie Night!
</p>

<table style="width:100%;margin-bottom:16px">
<tr>
<td style="background-color:#18181b;border-left:4px solid #ef4444;border-radius:4px;padding:14px 18px;text-align:center">
<p style="color:#a1a1aa;font-size:12px;font-weight:700;margin:0 0 6px 0;text-transform:uppercase;letter-spacing:0.05em">Invite Code</p>
<p style="color:#fafafa;font-size:20px;font-weight:800;font-family:monospace;margin:0;letter-spacing:0.1em">{{ $inviteCode }}</p>
</td>
</tr>
</table>

<table style="width:100%;margin:32px 0">
<tr>
<td style="text-align:center">
<table style="display:inline-block">
<tr>
<td style="background-color:#ef4444;border-radius:9999px;padding:12px 32px;text-align:center">
<a href="{{ $acceptUrl }}" style="color:#fff;font-size:15px;font-weight:700;text-decoration:none;display:inline-block">Accept Invitation</a>
</td>
</tr>
</table>
</td>
</tr>
</table>

<p style="color:#71717a;font-size:14px;line-height:1.5;margin:0;text-align:center">
This invitation expires on <strong style="color:#d4d4d8">{{ $expiresAt }}</strong>.<br>
See you at the movies!
</p>

<x-slot:subcopy>
<x-mail::subcopy>
<span style="color:#71717a;font-size:13px">
If you're having trouble clicking the "Accept Invitation" button, copy and paste the URL below into your web browser:<br>
<span class="break-all" style="color:#ef4444">[{{ $acceptUrl }}]({{ $acceptUrl }})</span>
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

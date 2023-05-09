@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
<img src="{{asset('/images/logo.png')}}" class="logo" alt="One Finance Logo" style="height:auto; width:auto;">
@endif
</a>
</td>
</tr>

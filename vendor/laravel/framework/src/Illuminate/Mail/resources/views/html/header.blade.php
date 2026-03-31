@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'APKOI')
                {{-- <img src="https://apkoi.web.id/logo.png" class="logo" alt="APKOI Logo"> --}}
                <img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
            @else
                {!! $slot !!}
            @endif
        </a>
    </td>
</tr>

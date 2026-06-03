@php
    $primary = $identity['primary_color'] ?? '#f59e0b';
    $surface = $identity['surface_color'] ?? '#ffffff';
    $text = $identity['text_color'] ?? '#111827';
    $storeName = $identity['name'] ?? config('app.name', 'AlBaik Store');
    $logo = $identity['logo'] ?? null;
@endphp
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;color:{{ $text }};font-family:Arial,Tahoma,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:{{ $surface }};border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:24px;background:#111827;color:#ffffff;text-align:{{ $direction === 'rtl' ? 'right' : 'left' }};">
                            @if ($logo)
                                <img src="{{ asset('storage/'.$logo) }}" alt="{{ $storeName }}" style="max-height:52px;max-width:180px;display:block;margin-bottom:12px;">
                            @endif
                            <div style="font-size:22px;font-weight:800;">{{ $storeName }}</div>
                            <div style="margin-top:6px;color:#d1d5db;font-size:13px;">{{ $preheader }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;text-align:{{ $direction === 'rtl' ? 'right' : 'left' }};line-height:1.8;font-size:15px;">
                            {!! $content !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 28px;background:#f9fafb;text-align:center;color:#6b7280;font-size:12px;line-height:1.7;">
                            <div style="font-weight:700;color:#111827;">{{ $storeName }}</div>
                            @if (! empty($contact['email']))
                                <div>{{ $contact['email'] }}</div>
                            @endif
                            <div style="margin-top:12px;">
                                <a href="{{ $unsubscribeUrl }}" style="color:{{ $primary }};text-decoration:none;">{{ __('Unsubscribe') }}</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

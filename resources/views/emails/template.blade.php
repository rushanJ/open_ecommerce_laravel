<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;line-height:1.5;color:#0f172a;background:#f8fafc;">
    <div style="max-width:560px;margin:0 auto;padding:32px 20px;">
        <div style="background:#fff;border-radius:12px;padding:24px;border:1px solid #e2e8f0;">
            {!! $bodyHtml !!}
        </div>
    </div>
</body>
</html>

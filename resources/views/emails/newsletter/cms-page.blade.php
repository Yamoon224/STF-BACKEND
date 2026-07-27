<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $page->title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; max-width:600px; width:100%;">
                <tr>
                    <td align="center" style="padding:32px 24px 16px;">
                        <img src="{{ rtrim(config('app.frontend_url'), '/') }}/brand/logo.png" alt="Sciences &amp; Technologies au Féminin" width="140" style="display:block; border:0;">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0 24px 8px;">
                        <p style="margin:0; font-size:12px; letter-spacing:1px; text-transform:uppercase; color:#9ca3af; font-weight:bold;">
                            {{ $page->category ?: 'Actualités' }}
                        </p>
                    </td>
                </tr>
                @if ($page->image_url)
                <tr>
                    <td style="padding:8px 0 0;">
                        <img src="{{ $page->image_url }}" alt="" width="600" style="display:block; width:100%; height:auto; border:0;">
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding:24px 24px 0;">
                        <h1 style="margin:0; font-size:22px; line-height:1.3; color:#111827;">{{ $page->title }}</h1>
                    </td>
                </tr>
                @if ($page->excerpt)
                <tr>
                    <td style="padding:12px 24px 0;">
                        <p style="margin:0; font-size:15px; line-height:1.6; color:#4b5563;">{{ $page->excerpt }}</p>
                    </td>
                </tr>
                @endif
                <tr>
                    <td align="center" style="padding:24px 24px 32px;">
                        <a href="{{ $articleUrl }}" style="display:inline-block; font-size:14px; font-weight:bold; color:#111827; text-decoration:underline;">Lire la suite</a>
                    </td>
                </tr>
                <tr>
                    <td style="border-top:1px solid #e5e7eb; padding:24px;">
                        <p style="margin:0 0 12px; font-size:13px; line-height:1.6; color:#6b7280; text-align:center;">
                            Sciences &amp; Technologies au Féminin — Audace, Union, Intégrité, Résultat.
                        </p>
                        <p style="margin:0 0 12px; font-size:12px; line-height:1.6; color:#9ca3af; text-align:center;">
                            {{ $settings['address'] ?? '' }}
                            @if(!empty($settings['phone']))
                                &middot; {{ $settings['phone'] }}
                            @endif
                        </p>
                        <p style="margin:0 0 12px; font-size:12px; text-align:center;">
                            @php
                                $socialLabels = [
                                    'social_linkedin' => 'LinkedIn',
                                    'social_facebook' => 'Facebook',
                                    'social_instagram' => 'Instagram',
                                    'social_x' => 'X',
                                    'social_youtube' => 'YouTube',
                                ];
                                $socialLinks = collect($socialLabels)
                                    ->filter(fn ($label, $key) => !empty($settings[$key]) && $settings[$key] !== '#');
                            @endphp
                            @foreach ($socialLinks as $key => $label)
                                <a href="{{ $settings[$key] }}" style="color:#6b7280; text-decoration:underline; margin:0 6px;">{{ $label }}</a>@if (!$loop->last)&middot;@endif
                            @endforeach
                        </p>
                        <p style="margin:0 0 12px; font-size:11px; color:#9ca3af; text-align:center;">
                            &copy; {{ now()->year }} Sciences &amp; Technologies au Féminin
                        </p>
                        <p style="margin:0; font-size:11px; text-align:center;">
                            <a href="{{ $unsubscribeUrl }}" style="color:#9ca3af; text-decoration:underline;">Cliquez ici pour vous désabonner</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

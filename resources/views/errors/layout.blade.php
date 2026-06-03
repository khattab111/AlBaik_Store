@php
    $locale = app()->getLocale();
    $direction = in_array($locale, ['ar', 'fa', 'ur'], true) ? 'rtl' : 'ltr';
    $status = $status ?? 500;
    $title = $title ?? __('Something went wrong');
    $message = $message ?? __('We could not complete your request right now.');
    $actionUrl = $actionUrl ?? route('home');
    $actionLabel = $actionLabel ?? __('Back to home');
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $status }} - {{ $title }}</title>
    <style>
        :root {
            color-scheme: light;
            --gold: #f59e0b;
            --gold-soft: #fef3c7;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --surface: #ffffff;
            --page: #f8fafc;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 80% 16%, rgba(245, 158, 11, 0.16), transparent 26rem),
                linear-gradient(180deg, #fff 0%, var(--page) 58%, #fff 100%);
            color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 1rem;
        }

        .error-card {
            width: min(100%, 48rem);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
        }

        .error-hero {
            display: grid;
            gap: 0.75rem;
            background:
                radial-gradient(circle at 18% 12%, rgba(245, 158, 11, 0.22), transparent 14rem),
                linear-gradient(135deg, #05070a, #111827 62%, #030405);
            padding: clamp(1.5rem, 5vw, 3rem);
            color: #fff;
        }

        .error-code {
            display: inline-flex;
            width: max-content;
            align-items: center;
            gap: 0.45rem;
            border: 1px solid rgba(251, 191, 36, 0.32);
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.12);
            padding: 0.45rem 0.75rem;
            color: #fef3c7;
            font-size: 0.78rem;
            font-weight: 900;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.9rem, 5vw, 3rem);
            line-height: 1.08;
        }

        .error-hero p {
            max-width: 42rem;
            margin: 0;
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.98rem;
            font-weight: 700;
            line-height: 1.8;
        }

        .error-body {
            display: grid;
            gap: 1rem;
            padding: clamp(1.25rem, 4vw, 2rem);
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        a {
            display: inline-flex;
            min-height: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.8rem;
            padding-inline: 1.1rem;
            font-size: 0.9rem;
            font-weight: 900;
            text-decoration: none;
        }

        .primary {
            background: linear-gradient(180deg, #fbbf24, var(--gold));
            color: #111827;
            box-shadow: 0 16px 34px rgba(245, 158, 11, 0.22);
        }

        .secondary {
            border: 1px solid var(--line);
            color: var(--muted);
        }

        .hint {
            margin: 0;
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.8;
        }
    </style>
</head>
<body>
<main>
    <section class="error-card" aria-labelledby="error-title">
        <div class="error-hero">
            <span class="error-code">{{ $status }}</span>
            <h1 id="error-title">{{ $title }}</h1>
            <p>{{ $message }}</p>
        </div>
        <div class="error-body">
            <div class="error-actions">
                <a class="primary" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
                <a class="secondary" href="javascript:history.back()">{{ __('Go back') }}</a>
            </div>
            <p class="hint">{{ __('If you believe this is a mistake, contact store support or try again later.') }}</p>
        </div>
    </section>
</main>
</body>
</html>

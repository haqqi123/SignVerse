<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SignTeach - Belajar Bahasa Isyarat')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Design tokens SignTeach — dipindahkan dari signlib/ui.py (light theme) */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #0ea5b7;
            --bg: #f7f6fd;
            --card-border: #e5e4f0;
            --text: #1e1b3a;
            --muted: #6b6a86;
            --radius: 14px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }
        button { font-family: inherit; }

        .app { display: flex; min-height: 100vh; }
        .sidebar {
            width: 250px; background: #ffffff;
            border-right: 1px solid var(--card-border);
            padding: 1.4rem 1rem; flex-shrink: 0;
            display: flex; flex-direction: column;
        }
        .sidebar .brand { font-weight: 800; font-size: 1.15rem; margin-bottom: 1.4rem; }
        .sidebar .brand span { color: var(--primary); }
        .sidebar nav { flex: 1; }
        .sidebar nav a {
            display: block; padding: 0.55rem 0.8rem; border-radius: 10px;
            color: var(--muted); font-weight: 600; font-size: 0.92rem; margin-bottom: 0.15rem;
        }
        .sidebar nav a.active, .sidebar nav a:hover { background: #eef0ff; color: var(--primary-dark); }
        .sidebar .user-box {
            margin-top: 1.4rem; padding: 0.8rem; border: 1px solid var(--card-border);
            border-radius: var(--radius); font-size: 0.85rem; color: var(--muted);
        }
        .sidebar .user-box b { color: var(--text); display: block; }
        .content { flex: 1; padding: 1.8rem 2.2rem; max-width: 1200px; }

        h1, h2, h3 { font-family: 'Outfit', sans-serif; color: var(--text); }
        .section-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.2rem; }
        .section-sub { color: var(--muted); margin-bottom: 1.4rem; }
        .section-mini-title { font-weight: 800; font-size: 1.05rem; }
        .section-mini-sub { color: var(--muted); font-size: 0.85rem; margin-bottom: 0.6rem; }

        .card {
            background: #ffffff; border: 1px solid var(--card-border);
            border-radius: var(--radius); padding: 1.2rem 1.4rem;
            box-shadow: 0 1px 3px rgba(30, 27, 58, 0.04);
        }
        .stat-card {
            background: #ffffff; border: 1px solid var(--card-border);
            border-radius: var(--radius); padding: 1rem 1.2rem;
            box-shadow: 0 1px 3px rgba(30, 27, 58, 0.04);
        }
        .stat-card .stat-label { font-size: 0.8rem; color: var(--muted); font-weight: 600; }
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text); margin-top: 0.2rem; }
        .stat-card .stat-delta { font-size: 0.75rem; color: var(--secondary); font-weight: 600; }

        .btn {
            display: inline-block; background: var(--primary); color: #ffffff;
            border: none; border-radius: 10px; padding: 0.55rem 1.4rem;
            font-weight: 600; font-size: 0.95rem; cursor: pointer;
            transition: all 0.15s;
        }
        .btn:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-secondary { background: #f1f1f5; color: var(--text); }
        .btn-secondary:hover { background: #e4e4ea; }
        .btn-full { width: 100%; }

        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%; border: 1px solid var(--card-border); border-radius: 10px;
            padding: 0.6rem 0.9rem; font-family: inherit; font-size: 0.95rem;
            background: #fff; color: var(--text);
        }
        input:focus { outline: 2px solid #c7d2fe; }
        label { font-weight: 600; font-size: 0.88rem; display: block; margin-bottom: 0.3rem; }
        .field { margin-bottom: 0.9rem; }

        .st-badge {
            display: inline-block; padding: 0.2rem 0.7rem; border-radius: 999px;
            font-size: 0.75rem; font-weight: 700;
        }
        .badge-indigo { background: #eef0ff; color: var(--primary-dark); }
        .badge-teal { background: #e6fbf7; color: #0f766e; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-gray { background: #f1f1f5; color: #52525b; }

        .alert { border-radius: 10px; padding: 0.8rem 1rem; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-success { background: #e6fbf7; color: #0f766e; }
        .alert-error { background: #fef2f2; color: #b91c1c; }
        .alert-info { background: #eef0ff; color: var(--primary-dark); }

        .xp-track { background: #eef0ff; border-radius: 999px; overflow: hidden; }
        .xp-fill { background: linear-gradient(90deg, var(--primary), var(--secondary)); border-radius: 999px; }
        .xp-label { font-size: 0.75rem; color: var(--muted); margin-bottom: 4px; }

        .empty-state { text-align: center; color: var(--muted); padding: 2rem 0; }
        .empty-state-emoji { font-size: 2rem; }

        .divider { border-top: 1px solid var(--card-border); margin: 1.2rem 0; }
        .caption { color: var(--muted); font-size: 0.85rem; }
        .table { width: 100%; border-collapse: collapse; font-size: 0.9rem; background: #fff;
                 border: 1px solid var(--card-border); border-radius: var(--radius); overflow: hidden; }
        .table th { background: #f7f6fd; text-align: left; padding: 0.6rem 0.8rem; font-size: 0.8rem;
                    color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .table td { padding: 0.6rem 0.8rem; border-top: 1px solid var(--card-border); }

        .hero { text-align: center; padding: 3rem 0.5rem; }
        .hero h1 { font-size: 3rem; font-weight: 800; letter-spacing: -0.02em; }
        .hero .grad {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero .sub { color: var(--muted); font-size: 1.15rem; margin-top: 0.4rem; }

        @media (max-width: 768px) {
            .app { flex-direction: column; }
            .sidebar { width: 100%; }
            .hero h1 { font-size: 2.1rem; }
            .stat-card .stat-value { font-size: 1.25rem; }
        }

        /* ── Auth Centered Layout ── */
        .app.app-auth {
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.08) 0%, rgba(247, 246, 253, 1) 45%),
                        radial-gradient(circle at 90% 80%, rgba(14, 165, 183, 0.08) 0%, rgba(247, 246, 253, 1) 45%);
            min-height: 100vh;
        }
        .content-auth {
            max-width: 460px;
            width: 100%;
            margin: 0 auto;
            padding: 2.4rem 1.4rem;
        }
        .auth-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 2.2rem 2rem;
            box-shadow: 0 10px 30px -5px rgba(30, 27, 58, 0.06), 0 4px 6px -2px rgba(30, 27, 58, 0.03);
            transition: box-shadow 0.2s ease;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }
        .auth-logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #eef0ff, #e6fbf7);
            border: 1px solid #dcdbe8;
            border-radius: 18px;
            font-size: 2rem;
            margin-bottom: 0.8rem;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.12);
        }
        .auth-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.01em;
        }
        .auth-subtitle {
            font-size: 0.9rem;
            color: var(--muted);
            margin-top: 0.3rem;
        }

        /* Form Controls with Icons & Polish */
        .form-label {
            display: block;
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--text);
            margin-bottom: 0.4rem;
        }
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-group-icon {
            position: absolute;
            left: 0.95rem;
            font-size: 1.1rem;
            color: var(--muted);
            pointer-events: none;
            user-select: none;
        }
        .input-control {
            width: 100%;
            border: 1.5px solid var(--card-border);
            border-radius: 12px;
            padding: 0.72rem 1rem 0.72rem 2.85rem !important;
            font-family: inherit;
            font-size: 0.95rem;
            background: #fff;
            color: var(--text);
            transition: all 0.2s ease;
        }
        .input-control:hover {
            border-color: #c7d2fe;
        }
        .input-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .input-control.is-invalid {
            border-color: #ef4444;
            background-color: #fffaf9;
        }
        .btn-toggle-password {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 1.15rem;
            cursor: pointer;
            padding: 0.35rem 0.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s, background 0.15s;
        }
        .btn-toggle-password:hover {
            color: var(--text);
            background: #f1f1f5;
        }

        /* Demo Box */
        .demo-box {
            margin-top: 1.6rem;
            padding: 1rem 1.2rem;
            background: #fbfbfe;
            border: 1px dashed #d5d4e6;
            border-radius: 14px;
        }
        .demo-box-header {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .demo-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }
        .demo-pill {
            flex: 1;
            min-width: 140px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.5rem 0.8rem;
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .demo-pill:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: #f4f5ff;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.1);
        }
    </style>
</head>
<body>
<div class="app @yield('app_class')">
    @if (!View::hasSection('no_sidebar'))
        <aside class="sidebar">
            <div class="brand">🤟 Sign<span>Teach</span></div>
            <nav>
                @foreach (\App\Support\Navigation::items(auth()->user()?->role) as $item)
                    <x-nav-link :route="$item['route']" :icon="$item['icon']" :title="$item['title']" />
                @endforeach
            </nav>
            @auth
                <div class="user-box">
                    <b>{{ auth()->user()->name }}</b>
                    {{ auth()->user()->username }}
                    <form method="POST" action="{{ route('logout') }}" style="margin-top:0.6rem">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-full">Keluar</button>
                    </form>
                </div>
            @endauth
        </aside>
    @endif
    <main class="content @yield('content_class')">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (!View::hasSection('custom_errors') && $errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>

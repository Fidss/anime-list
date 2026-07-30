<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Anime List') }}</title>

    <!-- Load Tailwind via CDN as fallback/simple dev setup -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', 'Noto Sans', 'Helvetica Neue', Arial; }
        body { font-family: var(--font-sans); }
        .img-card { width:100%; height:16rem; object-fit:cover; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="bg-white shadow">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-semibold">Anime List</a>
            <form action="{{ route('search') }}" method="get" class="w-1/3">
                <div class="flex">
                    <input type="text" name="q" value="{{ request('q', $query ?? '') }}" placeholder="Search anime..." class="w-full rounded-l-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <button type="submit" class="bg-indigo-600 text-white px-4 rounded-r-md">Search</button>
                </div>
            </form>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="text-center text-sm text-gray-500 py-8">
        Data from <a class="text-indigo-600" href="https://anilist.co/" target="_blank" rel="noopener">AniList</a>
    </footer>
</body>
</html>
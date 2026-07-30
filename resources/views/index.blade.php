@extends('layout')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Anime Search / Results</h1>
        <div class="text-sm text-gray-600">@if(isset($pagination)) Page: {{ $pagination['current_page'] }} / {{ $pagination['last_visible_page'] ?? '?' }} @endif</div>
    </div>

    @if(isset($query) && $query !== '')
        <p class="mb-4 text-sm text-gray-600">Search results for "{{ $query }}"</p>
    @endif

    @if(!empty($error))
        <div class="mb-4 text-red-600">{{ $error }}</div>
    @endif

    @if(empty($animes))
        <p class="text-gray-600">No anime found.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($animes as $item)
                @php
                    $id = $item['mal_id'] ?? ($item['id'] ?? null);
                    $title = $item['title'] ?? ($item['title_english'] ?? 'Untitled');
                    $image = $item['images']['jpg']['image_url'] ?? $item['image_url'] ?? '';
                    $synopsis = $item['synopsis'] ?? '';
                    $score = $item['score'] ?? null;
                    $episodes = $item['episodes'] ?? null;
                    $type = $item['type'] ?? ($item['broadcast'] ?? null);
                    $status = $item['status'] ?? null;
                    $aired = $item['aired'] ?? null;
                    $genres = $item['genres'] ?? [];
                    $producers = $item['producers'] ?? ($item['studios'] ?? []);
                @endphp

                <a href="{{ route('anime.show', ['id' => $id]) }}" class="block bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                    <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-64 object-cover">
                    <div class="p-4">
                        <h2 class="font-semibold text-lg mb-1">{{ $title }}</h2>

                        <div class="text-sm text-gray-600 mb-2">
                            @if($score)
                                <span class="inline-block bg-yellow-400 text-black px-2 py-0.5 rounded">Score: {{ $score }}</span>
                            @endif
                            @if($episodes)
                                <span class="ml-2 inline-block bg-gray-200 px-2 py-0.5 rounded">Episodes: {{ $episodes }}</span>
                            @endif
                            @if($type)
                                <span class="ml-2 inline-block bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">{{ $type }}</span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-700 mb-3">{{ \Illuminate\Support\Str::limit($synopsis, 140) }}</p>

                        <div class="text-xs text-gray-500">
                            @if(!empty($genres))
                                Genres: @foreach($genres as $g) <span class="px-1">{{ $g['name'] }}</span>@endforeach<br>
                            @endif
                            @if(!empty($producers))
                                Producers/Studios: @foreach($producers as $p) <span class="px-1">{{ $p['name'] }}</span>@endforeach<br>
                            @endif
                            @if(isset($item['year']))
                                Year: {{ $item['year'] }}
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if(isset($pagination))
            <div class="mt-6 flex items-center justify-center space-x-3">
                @php
                    $current = $pagination['current_page'] ?? 1;
                    $hasNext = $pagination['has_next_page'] ?? false;
                @endphp

                @if($current > 1)
                    <a href="?{{ http_build_query(array_merge($params, ['page' => $current - 1])) }}" class="px-3 py-1 bg-white border rounded">&larr; Prev</a>
                @endif

                <span class="px-3 py-1 bg-white border rounded">Page {{ $current }}</span>

                @if($hasNext)
                    <a href="?{{ http_build_query(array_merge($params, ['page' => $current + 1])) }}" class="px-3 py-1 bg-white border rounded">Next &rarr;</a>
                @endif
            </div>
        @endif
    @endif

@endsection

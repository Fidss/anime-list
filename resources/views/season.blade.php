@extends('layout')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Seasonal Anime</h1>

    @if(!empty($year) && !empty($season))
        <p class="mb-4 text-sm text-gray-600">Showing {{ ucfirst($season) }} {{ $year }}</p>
    @else
        <p class="mb-4 text-sm text-gray-600">Showing current season</p>
    @endif

    @if(empty($animes))
        <p class="text-gray-600">No anime found for this season.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($animes as $item)
                @php
                    $id = $item['mal_id'] ?? ($item['id'] ?? null);
                    $title = $item['title'] ?? ($item['title_english'] ?? 'Untitled');
                    $image = $item['images']['jpg']['image_url'] ?? $item['image_url'] ?? '';
                    $synopsis = $item['synopsis'] ?? '';
                @endphp

                <a href="{{ route('anime.show', ['id' => $id]) }}" class="block bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                    <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-64 object-cover">
                    <div class="p-4">
                        <h2 class="font-semibold text-lg mb-2">{{ $title }}</h2>
                        <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($synopsis, 100) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

@endsection

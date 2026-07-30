@extends('layout')

@section('content')
    @php
        $title = $anime['title'] ?? 'Untitled';
        $image = $anime['images']['jpg']['image_url'] ?? $anime['image_url'] ?? '';
        $synopsis = $anime['synopsis'] ?? '';
        $score = $anime['score'] ?? null;
        $episodes = $anime['episodes'] ?? null;
        $genres = $anime['genres'] ?? [];
    @endphp

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="md:flex">
                <div class="md:w-1/2 p-4">
                    @if(!empty($anime['trailer']) && isset($anime['trailer']['site']) && strtolower($anime['trailer']['site']) === 'youtube' && !empty($anime['trailer']['id']))
                        <div class="w-full aspect-video bg-black">
                            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $anime['trailer']['id'] }}" title="{{ $title }} trailer" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    @else
                        <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-auto object-cover rounded">
                    @endif

                    @if(!empty($anime['externalLinks']))
                        <div class="mt-4">
                            <h4 class="font-semibold">Official / Streaming Links</h4>

                            @php
                                $knownHosts = ['Crunchyroll','Netflix','Funimation','Hulu','YouTube','Dailymotion','Vimeo','Bilibili','Amazon','HBO','Anime-Planet','MyAnimeList'];
                                $primary = null;
                                foreach($anime['externalLinks'] as $link) {
                                    foreach($knownHosts as $host) {
                                        if (stripos($link['site'] ?? '', $host) !== false) {
                                            $primary = $link;
                                            break 2;
                                        }
                                    }
                                }
                            @endphp

                            @if($primary)
                                <div class="mb-3">
                                    <a href="{{ $primary['url'] }}" target="_blank" rel="noopener" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded">Watch on {{ $primary['site'] }}</a>
                                </div>
                            @endif

                            <ul class="list-disc list-inside text-sm text-gray-700">
                                @foreach($anime['externalLinks'] as $link)
                                    <li><a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="text-indigo-600">{{ $link['site'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="p-6 md:w-1/2">
                    <h1 class="text-2xl font-bold mb-2">{{ $title }}</h1>
                    <p class="text-sm text-gray-600 mb-4">{!! nl2br(e(strip_tags($synopsis))) !!}</p>

                    <div class="mb-4">
                        @if($score)
                            <span class="inline-block bg-yellow-400 text-black px-2 py-1 rounded">Score: {{ $score }}</span>
                        @endif
                        @if($episodes)
                            <span class="ml-2 inline-block bg-gray-200 px-2 py-1 rounded">Episodes: {{ $episodes }}</span>
                        @endif
                    </div>

                    @if(!empty($genres))
                        <div class="mt-4">
                            <h3 class="font-semibold mb-2">Genres</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($genres as $g)
                                    <span class="text-sm bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ $g['name'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('home') }}" class="text-indigo-600">&larr; Back to list</a>
                    </div>
                </div>
            </div>
    </div>

@endsection

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnimeController extends Controller
{
    public function index(Request $request)
    {
        // Show paginated catalog using AniList GraphQL
        $page = (int) $request->query('page', 1);
        $perPage = 10; // fixed page size as requested

        $query = <<<'GRAPHQL'
query ($page: Int, $perPage: Int) {
  Page(page: $page, perPage: $perPage) {
    pageInfo { total currentPage lastPage hasNextPage }
    media(type: ANIME, sort: POPULARITY_DESC) {
      id
      idMal
      title { romaji english native }
      coverImage { extraLarge large medium }
      description
      episodes
      averageScore
      genres
      trailer { id site thumbnail }
      siteUrl
    }
  }
}
GRAPHQL;

        $vars = ['page' => $page, 'perPage' => $perPage];

        $response = Http::withHeaders(['Accept' => 'application/json'])->post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => $vars,
        ]);

        $animes = [];
        $pagination = null;
        $params = ['page' => $page, 'limit' => $perPage];

        if ($response->successful()) {
            $json = $response->json();
            $media = $json['data']['Page']['media'] ?? [];
            $pageInfo = $json['data']['Page']['pageInfo'] ?? null;

            foreach ($media as $item) {
                $animes[] = [
                    'id' => $item['id'] ?? null,
                    'mal_id' => $item['idMal'] ?? null,
                    'title' => $item['title']['english'] ?? $item['title']['romaji'] ?? ($item['title']['native'] ?? 'Untitled'),
                    'images' => ['jpg' => ['image_url' => $item['coverImage']['extraLarge'] ?? $item['coverImage']['large'] ?? $item['coverImage']['medium'] ?? '']],
                    'synopsis' => $item['description'] ?? '',
                    'score' => $item['averageScore'] ?? null,
                    'episodes' => $item['episodes'] ?? null,
                    'genres' => array_map(fn($g) => ['name' => $g], $item['genres'] ?? []),
                    'trailer' => $item['trailer'] ?? null,
                    'siteUrl' => $item['siteUrl'] ?? null,
                ];
            }

            if ($pageInfo) {
                $pagination = [
                    'current_page' => $pageInfo['currentPage'] ?? $page,
                    'has_next_page' => $pageInfo['hasNextPage'] ?? false,
                    'last_visible_page' => $pageInfo['lastPage'] ?? null,
                    'items' => $pageInfo['total'] ?? null,
                ];
            }
        }

        return view('index', [
            'animes' => $animes,
            'pagination' => $pagination,
            'params' => $params,
            'query' => '',
        ]);
    }

    public function show($id)
    {
        // Use AniList GraphQL API as main source (no API key required for read-only queries)
        $query = <<<'GRAPHQL'
query ($id: Int, $idMal: Int) {
  Media(id: $id, idMal: $idMal, type: ANIME) {
    id
    idMal
    title { romaji english native }
    coverImage { extraLarge large medium }
    bannerImage
    description
    episodes
    averageScore
    status
    genres
    trailer { id site thumbnail }
    siteUrl
    externalLinks { site url }
  }
}
GRAPHQL;

        $vars = ['id' => is_numeric($id) ? (int) $id : null, 'idMal' => is_numeric($id) ? (int) $id : null];

        $response = Http::withHeaders(['Accept' => 'application/json'])->post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => $vars,
        ]);

        if (! $response->successful()) {
            abort(404);
        }

        $json = $response->json();
        $data = $json['data']['Media'] ?? null;

        if (! $data) {
            abort(404);
        }

        // normalize AniList media structure to view-friendly shape
        $anime = [
            'id' => $data['id'] ?? null,
            'mal_id' => $data['idMal'] ?? null,
            'title' => $data['title']['english'] ?? $data['title']['romaji'] ?? ($data['title']['native'] ?? 'Untitled'),
            'images' => [
                'jpg' => [
                    'image_url' => $data['coverImage']['extraLarge'] ?? $data['coverImage']['large'] ?? $data['coverImage']['medium'] ?? '',
                ],
            ],
            'synopsis' => $data['description'] ?? '',
            'score' => $data['averageScore'] ?? null,
            'episodes' => $data['episodes'] ?? null,
            'genres' => array_map(fn($g) => ['name' => $g], $data['genres'] ?? []),
            'trailer' => $data['trailer'] ?? null,
            'siteUrl' => $data['siteUrl'] ?? null,
            'externalLinks' => $data['externalLinks'] ?? [],
        ];

        return view('show', ['anime' => $anime]);
    }

    public function search(Request $request)
    {
        // Search using AniList GraphQL API
        $q = $request->query('q');
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 12);

        $animes = [];
        $pagination = null;
        $params = [];
        $error = null;

        if ($q) {
            $query = <<<'GRAPHQL'
query ($search: String, $page: Int, $perPage: Int) {
  Page(page: $page, perPage: $perPage) {
    pageInfo { total currentPage lastPage hasNextPage }
    media(search: $search, type: ANIME) {
      id
      idMal
      title { romaji english native }
      coverImage { extraLarge large medium }
      bannerImage
      description
      episodes
      averageScore
      status
      genres
      trailer { id site thumbnail }
      siteUrl
      externalLinks { site url }
    }
  }
}
GRAPHQL;

            $vars = ['search' => $q, 'page' => $page, 'perPage' => $limit];

            $response = Http::withHeaders(['Accept' => 'application/json'])->post('https://graphql.anilist.co', [
                'query' => $query,
                'variables' => $vars,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $pageData = $json['data']['Page'] ?? null;
                $media = $pageData['media'] ?? [];

                foreach ($media as $item) {
                    $mapped = [
                        'id' => $item['id'] ?? null,
                        'mal_id' => $item['idMal'] ?? null,
                        'title' => $item['title']['english'] ?? $item['title']['romaji'] ?? ($item['title']['native'] ?? 'Untitled'),
                        'images' => [
                            'jpg' => [
                                'image_url' => $item['coverImage']['extraLarge'] ?? $item['coverImage']['large'] ?? $item['coverImage']['medium'] ?? '',
                            ],
                        ],
                        'synopsis' => $item['description'] ?? '',
                        'score' => $item['averageScore'] ?? null,
                        'episodes' => $item['episodes'] ?? null,
                        'genres' => array_map(fn($g) => ['name' => $g], $item['genres'] ?? []),
                        'trailer' => $item['trailer'] ?? null,
                        'siteUrl' => $item['siteUrl'] ?? null,
                        'externalLinks' => $item['externalLinks'] ?? [],
                    ];

                    $animes[] = $mapped;
                }

                $pageInfo = $pageData['pageInfo'] ?? null;
                $pagination = [
                    'current_page' => $pageInfo['currentPage'] ?? $page,
                    'has_next_page' => $pageInfo['hasNextPage'] ?? false,
                    'last_visible_page' => $pageInfo['lastPage'] ?? null,
                    'items' => $pageInfo['total'] ?? null,
                ];

                $params = ['q' => $q, 'page' => $page, 'limit' => $limit];

            } else {
                $error = 'Gagal mengambil hasil pencarian dari AniList API';
            }
        }

        return view('index', [
            'animes' => $animes,
            'query' => $q ?? '',
            'pagination' => $pagination,
            'params' => $params,
            'error' => $error,
        ]);
    }

    public function season(Request $request)
    {
        // Use AniList to query by season and year when provided, otherwise current trending by seasonYear
        $year = $request->query('year');
        $season = $request->query('season'); // WINTER SPRING SUMMER FALL expected uppercase for AniList
        $limit = 24;

        $where = '';
        $vars = ['page' => 1, 'perPage' => $limit];

        $query = <<<'GRAPHQL'
query ($page: Int, $perPage: Int, $season: MediaSeason, $seasonYear: Int) {
  Page(page: $page, perPage: $perPage) {
    media(type: ANIME, season: $season, seasonYear: $seasonYear, sort: POPULARITY_DESC) {
      id
      idMal
      title { romaji english native }
      coverImage { extraLarge large medium }
      description
      episodes
      averageScore
      genres
      trailer { id site thumbnail }
      siteUrl
    }
  }
}
GRAPHQL;

        $variables = ['page' => 1, 'perPage' => $limit, 'season' => null, 'seasonYear' => null];

        if ($year && $season) {
            $variables['season'] = strtoupper($season);
            $variables['seasonYear'] = (int) $year;
        }

        $response = Http::withHeaders(['Accept' => 'application/json'])->post('https://graphql.anilist.co', [
            'query' => $query,
            'variables' => $variables,
        ]);

        $animes = [];
        if ($response->successful()) {
            $json = $response->json();
            $media = $json['data']['Page']['media'] ?? [];

            foreach ($media as $item) {
                $animes[] = [
                    'id' => $item['id'] ?? null,
                    'mal_id' => $item['idMal'] ?? null,
                    'title' => $item['title']['english'] ?? $item['title']['romaji'] ?? ($item['title']['native'] ?? 'Untitled'),
                    'images' => ['jpg' => ['image_url' => $item['coverImage']['extraLarge'] ?? $item['coverImage']['large'] ?? $item['coverImage']['medium'] ?? '']],
                    'synopsis' => $item['description'] ?? '',
                    'score' => $item['averageScore'] ?? null,
                    'episodes' => $item['episodes'] ?? null,
                    'genres' => array_map(fn($g) => ['name' => $g], $item['genres'] ?? []),
                    'trailer' => $item['trailer'] ?? null,
                    'siteUrl' => $item['siteUrl'] ?? null,
                ];
            }
        }

        return view('season', [
            'animes' => $animes,
            'seasonInfo' => null,
            'year' => $year,
            'season' => $season,
        ]);
    }
}

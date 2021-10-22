<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\News;

class NewsController extends Controller
{
    public function getList() {
        $news = News::query()->where('published_at', '<=', 'NOW')
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')->paginate(5);
        return view('news', ['news' => $news]);
    }

    public function getDetails(string $slug) {
        $news = News::query()->where('slug', $slug)
            ->where('is_published', true)
            ->where('published_at', '<=', 'NOW')->first();
        if ($news === null) {
            abort(404);
        }
        return view('news_item', ['news' => $news]);
    }
}

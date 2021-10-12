<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\News;

class NewsController extends Controller
{
    public function getList() {
        $news = News::query()->where('published_at', '<=', 'now')
            ->orderBy('published_at', 'desc')->orderBy('id', 'desc')->paginate(5);

        return view('news', ['news' => $news]);
    }

    public function getDetails(string $slug) {
        $news = News::query()->where('slug', $slug)
            ->where('published_at', '<=', 'now')->first();
        if ($news === null)
            abort(404);
        return view('news_item', ['news' => $news]);
    }
}

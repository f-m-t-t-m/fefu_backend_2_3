<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Command;
use \App\Models\News;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NewsController;

class ChangeNewsSlug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change_news_slug {oldSlug} {newSlug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oldSlug = $this->argument('oldSlug');
        $newSlug = $this->argument('newSlug');
        $oldUrl = route('news_item', ['slug' => $oldSlug], false);
        $newUrl = route('news_item', ['slug' => $newSlug], false);

        if ($oldSlug === $newSlug) {
            $this->error("Old slug equals to new slug");
            return 1;
        }

        $redirect = Redirect::where('old_slug',  $oldUrl)
            ->where('new_slug', $newUrl)->first();
        if($redirect !== null) {
            $this->error("This redirect already exists");
            return 1;
        }

        $news = News::where('slug', $oldSlug)->first();
        if ($news === null) {
            $this->error("News with old slug doesn't exist");
            return 1;
        }

        DB::transaction(function () use ($news, $newSlug, $newUrl){
            Redirect::where('old_slug',  $newUrl)->delete();
            $news->slug = $newSlug;
            $news->save();
        });

        return 0;
    }
}

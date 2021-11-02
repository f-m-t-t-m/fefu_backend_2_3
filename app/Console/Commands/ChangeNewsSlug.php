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

        if ($oldSlug === $newSlug) {
            $this->error("ERROR");
            return 1;
        }

        $news = News::where('slug', $oldSlug)->first();
        if ($news === null) {
            $this->error("ERROR");
            return 1;
        }

        DB::transaction(function () use ($news, $oldSlug, $newSlug){
            $news->slug = $newSlug;
            $news->save();
        });

        return 0;
    }
}

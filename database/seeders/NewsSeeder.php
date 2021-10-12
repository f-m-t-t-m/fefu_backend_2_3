<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\News;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        News::query()->delete();
        News::factory(random_int(20, 30))->create();
    }
}

<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory, Sluggable;

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }
    public function save(array $options = []) {
        if ($this->exists && $this->isDirty('slug')) {
            $oldSlug = $this->getOriginal('slug');
            $newSlug = $this->slug;

            $redirect = new Redirect();
            $oldUrl = route('news_item', ['slug' => $oldSlug]);
            $newUrl = route('news_item', ['slug' => $newSlug]);
            $redirect->old_slug = parse_url($oldUrl, PHP_URL_PATH);
            $redirect->new_slug = parse_url($newUrl, PHP_URL_PATH);
            $redirect->save();
        }
        return parent::save($options);
    }
}

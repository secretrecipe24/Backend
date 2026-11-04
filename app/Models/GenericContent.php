<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericContent extends Model
{
    use HasFactory;

    protected $table = 'generic_content';

    protected $fillable = [
        'title',
        'description',
        'category',
        'content_type',
        'content_url',
        'thumbnail_url',
        'difficulty',
        'duration',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
        ];
    }
}


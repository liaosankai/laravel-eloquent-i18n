<?php

namespace Liaosankai\LaravelEloquentI18n\Mocks\Models;

use Illuminate\Database\Eloquent\Model;
use Ariby\Ulid\HasUlid;
use Liaosankai\LaravelEloquentI18n\Models\TranslationTrait;

class Article extends Model
{
    use HasUlid;
    use TranslationTrait;

    public $fillable = [
        'title',
        'content',
        'author',
    ];

    public $i18nable = [
        'title',
        'content',
        'author',
    ];
}


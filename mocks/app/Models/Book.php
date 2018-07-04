<?php

namespace Liaosankai\LaravelEloquentI18n\Mocks\Models;

use Illuminate\Database\Eloquent\Model;
use Liaosankai\LaravelEloquentI18n\Models\TranslationTrait;

class Book extends Model
{
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


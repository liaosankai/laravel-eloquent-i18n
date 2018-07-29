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


    public static function boot()
    {
        self::creating(function ($book) {
            echo 'creating' . PHP_EOL;
        });

        self::created(function ($book) {
            echo 'created' . PHP_EOL;
        });

        self::updating(function ($book) {
            echo 'updating' . PHP_EOL;
        });

        self::updating(function ($book) {
            echo 'updated' . PHP_EOL;
        });

        self::saving(function ($book) {
            echo 'saving'. PHP_EOL;
        });

        self::saved(function ($book) {
            echo 'saved'. PHP_EOL;
        });
    }


}


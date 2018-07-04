<?php

namespace Liaosankai\LaravelEloquentI18n\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'key',
        'value',
        'locale',
        'translatable_id',
        'translatable_type',
    ];

    /**
     * Get all of the owning translatable models.
     */
    public function translatable()
    {
        return $this->morphTo();
    }

}


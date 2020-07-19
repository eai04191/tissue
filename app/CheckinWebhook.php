<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CheckinWebhook extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name'];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (CheckinWebhook $webhook) {
            $webhook->id = Str::random(64);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

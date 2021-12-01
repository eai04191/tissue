<?php

namespace App;

use App\Utilities\Formatter;
use Illuminate\Database\Eloquent\Model;

class CollectionItem extends Model
{
    protected $fillable = [
        'link'
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (CollectionItem $item) {
            $item->normalized_link = app(Formatter::class)->normalizeUrl($item->link);
        });
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * このアイテムでチェックインするためのURLを生成
     * @return string
     */
    public function makeCheckinURL(): string
    {
        return route('checkin', [
            'link' => $this->link,
//            'tags' => $this->textTags(),
//            'is_private' => $this->is_private,
//            'is_too_sensitive' => $this->is_too_sensitive,
        ]);
    }
}

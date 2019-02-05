<?php

namespace Illuminatech\SyncManyAttribute\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 */
class Tag extends Model
{
    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }
}

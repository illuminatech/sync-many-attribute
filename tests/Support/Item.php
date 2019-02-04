<?php

namespace Illuminatech\SyncManyAttribute\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminatech\SyncManyAttribute\SyncManyToManyAttribute;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 *
 * @property int[] $category_ids
 */
class Item extends Model
{
    use SyncManyToManyAttribute;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    protected function syncManyToManyAttributes(): array
    {
        return [
            'category_ids' => 'categories',
        ];
    }
}

<?php

namespace Illuminatech\SyncManyAttribute\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminatech\SyncManyAttribute\SyncManyAttributeTrait;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 *
 * @property int[] $category_ids
 */
class Item extends Model
{
    use SyncManyAttributeTrait;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    protected function manyToManyAttributes()
    {
        return [
            'category_ids' => 'categories',
        ];
    }
}

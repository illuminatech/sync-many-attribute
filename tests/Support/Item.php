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
 * @property int[] $tag_ids
 *
 * @property Category[] $categories
 * @property Tag[] $tags
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot(['reason', 'attached_at']);
    }

    protected function syncManyToManyAttributes(): array
    {
        return [
            'category_ids' => 'categories',
            'tag_ids' => [
                'tags' => [
                    'reason' => 'test-reason',
                    'attached_at' => function (Item $model) {
                        return time();
                    },
                ]
            ],
        ];
    }
}

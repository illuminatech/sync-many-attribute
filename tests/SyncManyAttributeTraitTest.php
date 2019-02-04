<?php

namespace Illuminatech\SyncManyAttribute\Test;

use Illuminatech\SyncManyAttribute\Test\Support\Item;
use Illuminatech\SyncManyAttribute\Test\Support\Category;

class SyncManyAttributeTraitTest extends TestCase
{
    public function testInsert()
    {
        $categoryIds = Category::query()->pluck('id')->toArray();

        $item = new Item();
        $item->name = 'new item';
        $item->category_ids = $categoryIds;
        $item->save();

        $this->assertEquals(count($categoryIds), $item->categories()->count());

        $item = $item->fresh();
        $this->assertEquals($categoryIds, $item->category_ids);
    }

    public function testUpdate()
    {
        $categoryIds = Category::query()->pluck('id')->toArray();

        $item = Item::query()->first();
        $item->category_ids = $categoryIds;
        $item->save();

        $this->assertEquals(count($categoryIds), $item->categories()->count());

        $item = $item->fresh();
        $this->assertEquals($categoryIds, $item->category_ids);
    }

    /**
     * @depends testUpdate
     */
    public function testClear()
    {
        $categoryIds = Category::query()->pluck('id')->toArray();

        $item = Item::query()->first();
        $item->category_ids = $categoryIds;
        $item->save();

        $item = $item->fresh();
        $item->category_ids = null;
        $item->save();

        $this->assertEquals(0, $item->categories()->count());
    }
}

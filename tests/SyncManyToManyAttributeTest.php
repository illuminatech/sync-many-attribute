<?php

namespace Illuminatech\SyncManyAttribute\Test;

use Illuminatech\SyncManyAttribute\Test\Support\Tag;
use Illuminatech\SyncManyAttribute\Test\Support\Item;
use Illuminatech\SyncManyAttribute\Test\Support\Category;

class SyncManyToManyAttributeTest extends TestCase
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

    /**
     * @depends testInsert
     * @depends testUpdate
     */
    public function testPivotAttributes()
    {
        $tagIds = Tag::query()->pluck('id')->toArray();

        $item = new Item();
        $item->name = 'new item';
        $item->tag_ids = $tagIds;
        $item->save();

        $tag = $item->tags()->first();

        $this->assertEquals('test-reason', $tag->pivot->reason);
        $this->assertTrue($tag->pivot->attached_at > 0);
    }

    public function testSerialize()
    {
        $tagIds = Tag::query()->pluck('id')->toArray();

        $item = new Item();
        $item->name = 'new item';
        $item->tag_ids = $tagIds;

        $serializedItem = serialize($item);

        $unserializedItem = unserialize($serializedItem);

        $this->assertEquals($item->tag_ids, $unserializedItem->tag_ids);
    }
}

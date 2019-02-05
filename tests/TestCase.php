<?php

namespace Illuminatech\SyncManyAttribute\Test;

use Illuminate\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $db = new Manager;

        $db->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();

        $this->seedData();

        Model::setEventDispatcher(new Dispatcher());
        Model::clearBootedModels();
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return Model::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema()
    {
        $this->getSchemaBuilder()->create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('price')->default(0);
        });

        $this->getSchemaBuilder()->create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->getSchemaBuilder()->create('category_item', function (Blueprint $table) {
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('item_id');
        });

        $this->getSchemaBuilder()->create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->getSchemaBuilder()->create('item_tag', function (Blueprint $table) {
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('tag_id');
            $table->string('reason');
            $table->integer('attached_at')->default(0);
        });
    }

    /**
     * Seeds the database.
     *
     * @return void
     */
    protected function seedData()
    {
        $this->getConnection()->table('items')->insert([
            'name' => 'item1',
            'price' => 10,
        ]);
        $this->getConnection()->table('items')->insert([
            'name' => 'item2',
            'price' => 20,
        ]);

        $this->getConnection()->table('categories')->insert([
            'name' => 'category1',
        ]);
        $this->getConnection()->table('categories')->insert([
            'name' => 'category2',
        ]);

        $this->getConnection()->table('tags')->insert([
            'name' => 'tag1',
        ]);
        $this->getConnection()->table('tags')->insert([
            'name' => 'tag2',
        ]);
    }
}

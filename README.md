<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Sync Eloquent Many-to-Many via Array Attribute</h1>
    <br>
</p>

This extension allows control over Eloquent many-to-many relations via array attributes.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/illuminatech/sync-many-attribute/v/stable.png)](https://packagist.org/packages/illuminatech/sync-many-attribute)
[![Total Downloads](https://poser.pugx.org/illuminatech/sync-many-attribute/downloads.png)](https://packagist.org/packages/illuminatech/sync-many-attribute)
[![Build Status](https://travis-ci.org/illuminatech/sync-many-attribute.svg?branch=master)](https://travis-ci.org/illuminatech/sync-many-attribute)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/sync-many-attribute
```

or add

```json
"illuminatech/sync-many-attribute": "*"
```

to the require section of your composer.json.


Usage
-----

This extension allows control over Eloquent many-to-many relations via array attributes.
Each such attribute matches particular `BelongsToMany` relation and accepts array of related model IDs.
Relations will be automatically synchronized during model saving.

> Note: in general such approach makes a little sense, since Eloquent already provides fluent interface for many-to-many
  relation synchronization. However, this extension make come in handy while working with 3rd party CMS like [Nova](https://nova.laravel.com),
  where you have a little control over model saving and post processing. Also it may simplify controller code, removing
  relation operations in favor to regular attribute mass assignment.

In order to use the feature you should add [[\Illuminatech\SyncManyAttribute\SyncManyToManyAttribute]] trait to your model class
and declare `syncManyToManyAttributes()` method, defining attributes for relation synchronization. This method should return
an array, which each key is the name of the new virtual attribute and value is the name of the relation to be synchronized. 

For example:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminatech\SyncManyAttribute\SyncManyToManyAttribute;

/**
 * @property int[] $category_ids
 * @property int[] $tag_ids
 */
class Item extends Model
{
    use SyncManyToManyAttribute;

    protected function syncManyToManyAttributes(): array
    {
        return [
            'category_ids' => 'categories',
            'tag_ids' => 'tags',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot(['created_at']);
    }

    // ...
}
```
 
Usage example:
 
```php
<?php

$item = new Item();
$item->category_ids = Category::query()->pluck('id')->toArray();
// ...
$item->save(); // relation `Item::categories()` synchronized automatically

$item = $item->fresh();
var_dump($item->category_ids); // outputs array of category IDs like `[1, 3, 8, ...]`
```

You may use sync attributes during HTML form input composition. For example:

```blade
...
<select multiple="multiple" name="category_ids[]" id="category_ids">
@foreach($allCategories as $category)
    <option value="{{$category->id}}" @if(in_array($category->id, $item->category_ids))selected="selected"@endif>{{$category->name}}</option>
@endforeach
...
</select>
```

Controller code example:

```php
<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KioskController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string'],
            // ...
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['int', 'exists:categories,id'],
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['int', 'exists:tags,id'],
        ]);
        
        $item = new Item;
        $item->fill($validatedData); // single assignment covers all many-to-many relations
        $item->save(); // relation `Item::categories()` synchronized automatically
        
        // return response
    }
}
```

> Note: remember you need to add the names of attribute for many-to-many synchronization to [[\Illuminate\Database\Eloquent\Model::$fillable]]
  in order to make them available for mass assignment.


## Pivot attributes setup

You may setup the pivot attributes, which should be saved during each relation synchronization. To do so, you should define
the sync attribute as an array, which key defines relation name and value - the pivot attributes. [[\Closure]] can be used
here for definition of particular pivot attribute value or entire pivot attributes set.
For example:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminatech\SyncManyAttribute\SyncManyToManyAttribute;

class Item extends Model
{
    use SyncManyToManyAttribute;

    protected function syncManyToManyAttributes(): array
    {
        return [
            'category_ids' => [
                'categories' => [
                    'type' => 'help-content',
                ],
            ],
            'tag_ids' => [
                'tags' => [
                    'attached_at' => function (Item $model) {
                        return now();
                    }
                ],
            ],
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot(['type']);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot(['attached_at']);
    }

    // ...
}
```

You may use [[\Illuminatech\SyncManyAttribute\ManyToManyAttribute]] to create sync attribute definition in more OOP style:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Illuminatech\SyncManyAttribute\ManyToManyAttribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminatech\SyncManyAttribute\SyncManyToManyAttribute;

class Item extends Model
{
    use SyncManyToManyAttribute;

    protected function syncManyToManyAttributes(): array
    {
        return [
            'category_ids' => (new ManyToManyAttribute)
                ->relationName('categories')
                ->pivotAttributes(['type' => 'help-content']),
            'tag_ids' => (new ManyToManyAttribute)
                ->relationName('tags')
                ->pivotAttributes([
                    'attached_at' => function (Item $model) {
                        return now();
                    },
                ]),
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot(['type']);
    }
    
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot(['attached_at']);
    }

    // ...
}
```

Defined pivot attributes will be automatically saved during relation synchronization on model saving:

```php
<?php

$item = new Item();
$item->category_ids = Category::query()->pluck('id')->toArray();
// ...
$item->save(); // relation `Item::categories()` synchronized automatically

$category = $item->categories()->first();
var_dump($category->pivot->type); // outputs 'help-content'
```

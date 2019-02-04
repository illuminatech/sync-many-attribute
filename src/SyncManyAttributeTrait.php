<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\SyncManyAttribute;

use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * SyncManyAttributeTrait
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait SyncManyAttributeTrait
{
    /**
     * @var array[]
     */
    private $syncManyToManyAttributes = [];

    /**
     * Boots this trait in the scope of the owner model.
     * @se \Illuminate\Database\Eloquent\Model::bootTraits()
     */
    public static function bootSyncManyAttributeTrait()
    {
        static::saved(function ($model) {
            /* @var $model \Illuminate\Database\Eloquent\Model|static */
            $model->syncManyToManyFromAttributes();
        });
    }

    /**
     * Set a given attribute on the model.
     * @see \Illuminate\Database\Eloquent\Model::setAttribute()
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|static|mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSyncManyAttribute($key)) {
            return $this->setSyncManyAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     * @see \Illuminate\Database\Eloquent\Model::getAttribute()
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($this->hasSyncManyAttribute($key)) {
            return $this->getSyncManyAttribute($key);
        }

        return parent::getAttribute($key);
    }

    public function hasSyncManyAttribute($key)
    {
        $definitions = $this->manyToManyAttributes();

        return isset($definitions[$key]);
    }

    public function setSyncManyAttribute($key, $value)
    {
        $this->syncManyToManyAttributes[$key] = Arr::wrap($value);

        return $this;
    }

    public function getSyncManyAttribute($key)
    {
        if (isset($this->syncManyToManyAttributes[$key])) {
            return $this->syncManyToManyAttributes[$key];
        }

        $definitions = $this->manyToManyAttributes();

        if (! isset($definitions[$key])) {
            throw new InvalidArgumentException("Undefined sync many attribute '{$key}'.");
        }

        $relationName = $definitions[$key];

        /* @var $relation \Illuminate\Database\Eloquent\Relations\BelongsToMany */
        $relation = $this->{$relationName}();

        $this->syncManyToManyAttributes[$key] = $relation->allRelatedIds()->toArray();

        return $this->syncManyToManyAttributes[$key];
    }

    private function syncManyToManyFromAttributes()
    {
        $definitions = $this->manyToManyAttributes();

        foreach ($this->syncManyToManyAttributes as $key => $values) {
            $relationName = $definitions[$key];
            /* @var $relation \Illuminate\Database\Eloquent\Relations\BelongsToMany */
            $relation = $this->{$relationName}();
            $relation->sync($values);

            unset($this->syncManyToManyAttributes[$key]);
        }

        return true;
    }

    abstract protected function manyToManyAttributes();
}

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
trait SyncManyToManyAttribute
{
    /**
     * @var array[] values of the attributes for many-to-many synchronization in format: `attributeName => [values]`.
     */
    private $syncManyToManyAttributes = [];

    /**
     * Boots this trait in the scope of the owner model, attaching necessary event handlers.
     * @see \Illuminate\Database\Eloquent\Model::bootTraits()
     */
    protected static function bootSyncManyToManyAttribute()
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
     * @param  string  $key attribute name.
     * @param  mixed  $value attribute value.
     * @return \Illuminate\Database\Eloquent\Model|static|mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSyncManyToManyAttribute($key)) {
            return $this->setSyncManyToManyAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     * @see \Illuminate\Database\Eloquent\Model::getAttribute()
     *
     * @param  string  $key attribute name.
     * @return mixed attribute value.
     */
    public function getAttribute($key)
    {
        if ($this->hasSyncManyToManyAttribute($key)) {
            return $this->getSyncManyToManyAttribute($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Checks whether particular attribute for many-to-many synchronization has been defined.
     *
     * @param  string  $key attribute name.
     * @return bool
     */
    public function hasSyncManyToManyAttribute($key): bool
    {
        $definitions = $this->syncManyToManyAttributes();

        return isset($definitions[$key]);
    }

    /**
     * Sets the value of the attribute for many-to-many synchronization.
     *
     * @param  string  $key attribute name.
     * @param  array|mixed|null  $value attribute value, it will be automatically casted to array.
     * @return \Illuminate\Database\Eloquent\Model|static self reference.
     */
    public function setSyncManyToManyAttribute($key, $value): self
    {
        $this->syncManyToManyAttributes[$key] = Arr::wrap($value);

        return $this;
    }

    /**
     * Returns value of the attribute for many-to-many synchronization.
     *
     * @param  string  $key attribute name.
     * @return array attribute value.
     */
    public function getSyncManyToManyAttribute($key): array
    {
        if (isset($this->syncManyToManyAttributes[$key])) {
            return $this->syncManyToManyAttributes[$key];
        }

        $definitions = $this->syncManyToManyAttributes();

        if (! isset($definitions[$key])) {
            throw new InvalidArgumentException("Undefined sync many-to-many attribute '{$key}'.");
        }

        $relationName = $definitions[$key];

        /* @var $relation \Illuminate\Database\Eloquent\Relations\BelongsToMany */
        $relation = $this->{$relationName}();

        $this->syncManyToManyAttributes[$key] = $relation->allRelatedIds()->toArray();

        return $this->syncManyToManyAttributes[$key];
    }

    /**
     * Synchronizes many-to-many relations according to the current {@link $syncManyToManyAttributes} values.
     * Synchronization is performed only for attributes, which has been set explicitly.
     *
     * @return void
     */
    private function syncManyToManyFromAttributes(): void
    {
        $definitions = $this->syncManyToManyAttributes();

        foreach ($this->syncManyToManyAttributes as $key => $values) {
            $relationName = $definitions[$key];
            /* @var $relation \Illuminate\Database\Eloquent\Relations\BelongsToMany */
            $relation = $this->{$relationName}();
            $relation->sync($values);

            unset($this->syncManyToManyAttributes[$key]);
        }
    }

    /**
     * Defines list of attributes  for many-to-many synchronization.
     * Method should return an array, which keys are the names of attributes, and values are the names
     * of the matching `BelongsToMany` relation.
     *
     * For example:
     *
     * ```php
     * return [
     *     'category_ids' => 'categories',
     * ];
     * ```
     *
     * @return array
     */
    abstract protected function syncManyToManyAttributes(): array;
}

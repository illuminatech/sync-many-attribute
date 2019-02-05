<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\SyncManyAttribute;

use Closure;
use InvalidArgumentException;

/**
 * AttributeDefinition
 *
 * @see SyncManyToManyAttribute
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class AttributeDefinition
{
    /**
     * @var string underlying many-to-many relation name.
     */
    protected $relationName;

    /**
     * @var Closure|array|null pivot attributes to be applied at relation synchronization.
     */
    protected $pivotAttributes = [];

    /**
     * Constructor.
     *
     * @param  array|string|null  $definition
     */
    public function __construct($definition = null)
    {
        if ($definition === null) {
            return;
        }

        if (is_array($definition)) {
            if (count($definition) !== 1) {
                throw new InvalidArgumentException('Attribute definition must be refer to exact one relation.');
            }

            $definitionKeys = array_keys($definition);

            $this->relationName(array_shift($definitionKeys));
            $this->pivotAttributes(array_shift($definition));

            return;
        }

        $this->relationName($definition);
    }

    /**
     * Sets relation name for this definition.
     *
     * @param  string  $relationName relation name.
     * @return static self reference.
     */
    public function relationName(string $relationName): self
    {
        $this->relationName = $relationName;

        return $this;
    }

    /**
     * Sets pivot attributes to be applied at relation synchronization.
     *
     * @param  Closure|array|null  $pivotAttributes
     * @return static self reference.
     */
    public function pivotAttributes($pivotAttributes): self
    {
        if ($pivotAttributes !== null && ! is_array($pivotAttributes) && ! $pivotAttributes instanceof Closure) {
            throw new InvalidArgumentException('"'.get_class($this).'::$pivotAttributes" must be null, array or Closure.');
        }

        $this->pivotAttributes = $pivotAttributes;

        return $this;
    }

    /**
     * Returns relation instance from given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model model instance to get relation from.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|mixed relation instance.
     */
    public function getRelation($model)
    {
        return $model->{$this->relationName}();
    }

    /**
     * Get all of the IDs for the related models.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array all of the IDs for the related models.
     */
    public function getRelatedIds($model): array
    {
        return $this->getRelation($model)->allRelatedIds()->toArray();
    }

    /**
     * Synchronizes relation with a list of IDs.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model model to be synchronized.
     * @param  array  $ids list of IDs from related models.
     * @return array sync changes report.
     */
    public function sync($model, array $ids)
    {
        $relation = $this->getRelation($model);

        if (empty($this->pivotAttributes)) {
            return $relation->sync($ids);
        }

        if ($this->pivotAttributes instanceof Closure) {
            $pivotAttributes = call_user_func($this->pivotAttributes, $model);
        } else {
            $pivotAttributes = array_map(function ($value) use ($model) {
                if (is_callable($value)) {
                    return call_user_func($value, $model);
                }

                return $value;
            }, $this->pivotAttributes);
        }

        $ids = array_fill_keys($ids, $pivotAttributes);

        return $relation->sync($ids);
    }
}

<?php
namespace Tusimo\Eloquent\Traits;

use Tusimo\Eloquent\Relations\EmbedsMany;

trait EmbedsRelation
{
    /**
     * Define a one-to-many relationship.
     *
     * @param  string $related
     * @param  string $foreignKey
     * @param  string $localKey
     * @param string $delimiter
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function embedsMany($related, $foreignKey = null, $localKey = null, $delimiter = ',')
    {
        $instance = method_exists($this, 'newRelatedInstance')
            ? $this->newRelatedInstance($related)
            : new $related;

        $foreignKey = $foreignKey ?: $instance->getKeyName();

        $localKey = $localKey ?: str_singular($instance->getTable()) . '_' . $this->getKeyName() . 's';

        return new EmbedsMany(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey, $delimiter
        );
    }
}

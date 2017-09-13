<?php
namespace Tusimo\Eloquent\Traits;

use Tusimo\Eloquent\Relations\EmbedsMany;

trait EmbedsRelation
{
    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function embedsMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $instance->getKeyName();

        $localKey = $localKey ?: str_singular($instance->getTable()) . '_' . $this->getKeyName() . 's';

        return new EmbedsMany(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: tusimo
 * Date: 13/09/2017
 * Time: 1:03 PM
 */

namespace Tusiomo\Traits;


use Tusimo\Eloquent\Relations\EmbedsMany;

class EmbedsRelation
{
    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new EmbedsMany(
            $instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
        );
    }

}
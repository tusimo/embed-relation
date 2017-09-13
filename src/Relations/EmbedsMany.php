<?php
namespace  Tusimo\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmbedsMany extends HasMany
{
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->whereIn($this->foreignKey, explode(',', $this->getParentKey()));
            $this->query->whereNotNull($this->foreignKey);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->whereIn(
            $this->foreignKey, $this->getEmbedsKeys($models, $this->localKey)
        );
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array $models
     * @param  string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }
        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $results = $results->keyBy(explode('.', $this->foreignKey)[1]);
        foreach ($models as $model) {
            $items = $results->filter(function ($value, $key) use ($model) {
                return in_array($key, explode(',', $model->getAttribute($this->localKey)));
            });
            $model->setRelation($relation, $items->values());
        }
        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }

    private function getEmbedsKeys($models, $key)
    {
        return collect($models)->map(function ($value) use ($key) {
            return explode(',', $key ? $value->getAttribute($key) : $value->getKey());
        })->flatten()->values()->unique()->sort()->all();
    }
}
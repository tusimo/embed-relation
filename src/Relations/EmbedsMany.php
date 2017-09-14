<?php
namespace  Tusimo\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * Attach a model instance to the parent model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function save(Model $model)
    {
        return $this->saveEmbedsModel([$model->getKey()]);
    }

    /**
     * Attach a collection of models to the parent instance.
     *
     * @param  \Traversable|array  $models
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    public function saveMany($models)
    {
        $newValue = [];
        foreach ($models as $model) {
            $newValue[] = $model->getKey();
        }
        return $this->saveEmbedsModel($newValue);
    }

    /**
     * @param array $newValue
     * @return \Illuminate\Database\Eloquent\Model|false
     */
    private function saveEmbedsModel($newValue)
    {
        $this->addEmbedsIds($newValue);
        //set relations if has already has relation
        if ($this->getParent()->save()) {
            if (!$this->getParent()->getRelations()) {
                return $this->getParent();
            } else {
                return $this->getParent()->refresh();
            }
        }
        return false;
    }

    private function addEmbedsIds($newIds)
    {
        $oldValue = explode(',', $this->getParentKey());
        $newValue = array_unique(array_merge($oldValue, $newIds));
        $this->getParent()->setAttribute($this->localKey, implode(',', $newValue));
        return $this;
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes)
    {
        return tap($this->related->newInstance($attributes), function ($instance) {
            $this->saveEmbedsModel([$instance->getKey()]);
        });
    }

    /**
     * Find a model by its primary key or return new instance of the related model.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();

            $this->addEmbedsIds([$instance->getKey()]);
        }

        return $instance;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);

            $this->addEmbedsIds([$instance->getKey()]);
        }

        return $instance;
    }
}

<?php
namespace  Hotelgg\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmbedsMany extends HasMany
{
    protected $slash = ',';

    public function __construct(Builder $query, Model $parent, $foreignKey, $localKey, $slash = ',')
    {
        parent::__construct($query, $parent, $foreignKey, $localKey);
        $this->slash = $slash;
    }

    private function getIdsArray($ids)
    {
        return is_array($ids) ? $ids : explode($this->slash, $ids);
    }
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->whereIn($this->foreignKey, $this->getIdsArray($this->getParentKey()));
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
            $currentIds = $this->getIdsArray($model->getAttribute($this->localKey));
            $items = $results->filter(function ($value, $key) use ($model, $currentIds) {
                return in_array($key, $currentIds);
            });

            if (empty($this->query->getQuery()->orders)) {
                $newItems = new Collection();
                foreach ($currentIds as $currentId) {
                    $newItems->push($items[$currentId]);
                }
                $model->setRelation($relation, $newItems->values());
            } else {
                $model->setRelation($relation, $items->values());
            }
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
        return (new Collection($models))->map(function ($value) use ($key) {
            return $this->getIdsArray($key ? $value->getAttribute($key) : $value->getKey());
        })->flatten()->filter()->unique()->values()->all();
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
        $oldValue = $this->getIdsArray($this->getParentKey());
        $newValue = array_unique(array_merge($oldValue, $newIds));
        $this->getParent()->setAttribute($this->localKey, implode($this->slash, $newValue));
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

    /**
     * 添加xx_ids到列里，并不保存
     * @param $ids
     * @return $this
     */
    public function associate($ids)
    {
        if (is_string($ids)) {
            $ids = explode($this->slash, $ids);
        }
        $oldValues = explode($this->slash, $this->getParent()->getAttribute($this->localKey));
        $newValues = array_unique(array_merge($oldValues, $ids));
        if (!empty($newValues)) {
            $newValues = implode($this->slash, $newValues);
        } else {
            $newValues = '';
        }
        $this->getParent()->setAttribute($this->localKey, $newValues);
        return $this;
    }

    /**
     * 将ids删除，不保存
     * @param $ids
     * @return $this
     */
    public function dissociate($ids)
    {
        if (is_string($ids)) {
            $ids = explode($this->slash, $ids);
        }
        $oldValues = explode($this->slash, $this->getParent()->getAttribute($this->localKey));
        if (empty($oldValues)) {
            return $this;
        }
        $newValues = array_unique(array_diff($oldValues, $ids));
        if (!empty($newValues)) {
            $newValues = implode($this->slash, $newValues);
        } else {
            $newValues = '';
        }
        $this->getParent()->setAttribute($this->localKey, $newValues);
        return $this;
    }

    /**
     * 添加ids并保存
     * @param $ids
     * @return $this
     */
    public function attach($ids)
    {
        $this->associate($ids);
        $this->getParent()->save();
        return $this;
    }

    /**
     * 删除ids并保存
     * @param $ids
     * @return $this
     */
    public function detach($ids)
    {
        $this->dissociate($ids)->getParent()->save();
        return $this;
    }
}

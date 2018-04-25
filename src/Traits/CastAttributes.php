<?php
namespace Tusimo\Eloquent\Traits;

trait CastAttributes
{
    protected $stringArrayGlue = ',';

    private $castmaps = [
        'string_array' => 'strval',
        'integer_array' => 'intval',
        'float_array' => 'floatval',
        'bool_array' => 'boolval',
    ];

    protected function castAttribute($key, $value)
    {
        if (in_array($cast = $this->getCastType($key), array_keys($this->castmaps))) {
            return string_to_array($value, $this->stringArrayGlue, $this->castmaps[$cast]);
        }
        return parent::castAttribute($key, $value);
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasCast($key, array_keys($this->castmaps))) {
            if (is_array($value)) {
                $value = implode($this->stringArrayGlue, $value);
            }
            $this->attributes[$key] = $value;
        } else {
            parent::setAttribute($key, $value);
        }
        if ($natureColumn = $this->getNatureColumnName($key)) {
            $values = string_to_array($this->attributes[$natureColumn], 'JSON');
            $values[$this->getVirtualOriginalKey($key, $natureColumn)] = $this->attributes[$key];
            $this->attributes[$natureColumn] = json_encode($values);
            unset($this->attributes[$key]);
        }
        return $this;
    }

    public function getAttribute($key)
    {
        if ($natureColumn = $this->getNatureColumnName($key)) {
            $values = string_to_array($this->attributes[$natureColumn], 'JSON');
            $this->attributes[$key] = $values[$this->getVirtualOriginalKey($key, $natureColumn)] ?? null;
            $value = parent::getAttribute($key);
            unset($this->attributes[$key]);
            return $value;
        }
        return parent::getAttribute($key);
    }

    private function getNatureColumnName($attribute)
    {
        foreach ($this->virtualColumnMaps as $natureKey => $columnMap) {
            foreach ($columnMap as $column) {
                if ($column == $attribute) {
                    return $natureKey;
                }
            }
        }
        return null;
    }

    private function getVirtualOriginalKey($attribute, $natureColumn = null)
    {
        if (is_null($natureColumn)) {
            $natureColumn = $this->getNatureColumnName($attribute);
        }
        $searchedKey = array_search($attribute, $this->virtualColumnMaps[$natureColumn]);
        return is_numeric($searchedKey) ? $attribute : $searchedKey;
    }

    public function isVirtualDirty($attributes = null)
    {
        $dirty = $this->getVirtualDirty();

        // If no specific attributes were provided, we will just see if the dirty array
        // already contains any attributes. If it does we will just return that this
        // count is greater than zero. Else, we need to check specific attributes.
        if (is_null($attributes)) {
            return count($dirty) > 0;
        }

        $attributes = is_array($attributes)
            ? $attributes : func_get_args();

        // Here we will spin through every attribute and see if this is in the array of
        // dirty attributes. If it is, we will return true and if we make it through
        // all of the attributes for the entire array we will return false at end.
        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }

        return false;
    }

    public function getVirtualDirty()
    {
        $dirty = parent::getDirty();
        //get virtual dirty
        if (empty($this->virtualColumnMaps)) {
            return $dirty;
        }
        foreach (array_keys($this->virtualColumnMaps) as  $columnMap) {
            if (!array_key_exists($columnMap, $dirty)) {
                continue;
            }
            $attributeJson = string_to_array($this->attributes[$columnMap], 'JSON');
            $originalJson = string_to_array($this->original[$columnMap], 'JSON');
            foreach ($attributeJson as $key => $attribute) {
                if (!array_key_exists($key, $originalJson)) {
                    $dirty[$this->virtualColumnMaps[$columnMap][$key] ?? $key] = $attribute;
                } else {
                    if ($attribute != $originalJson[$key]) {
                        $dirty[$this->virtualColumnMaps[$columnMap][$key] ?? $key] = $attribute;
                    }
                }
            }
        }
        return $dirty;
    }
}

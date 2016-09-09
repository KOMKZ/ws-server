<?php
namespace wsserver\redis;

use yii\redis\ActiveRecord as Base;

/**
 *
 */
class ActiveRecord extends Base
{
    public static function getPkStartValue(){
        return false;
    }
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        if (!$this->beforeSave(true)) {
            return false;
        }
        $db = static::getDb();
        $values = $this->getDirtyAttributes($attributes);
        $pk = [];
        foreach ($this->primaryKey() as $key) {
            $pk[$key] = $values[$key] = $this->getAttribute($key);
            if ($pk[$key] === null) {
                // use auto increment if pk is null
                $currentPk = $db->executeCommand('GET', [static::keyPrefix() . ':s:' . $key]);
                $startValueMap = static::getPkStartValue($key);

                if(null === $currentPk && false !== static::getPkStartValue($key) && (array_key_exists($key, $startValueMap))){
                    $pk[$key] = $values[$key] = $startValueMap[$key];
                    $db->executeCommand('SET', [static::keyPrefix() . ':s:' . $key, $startValueMap[$key]]);
                    $this->setAttribute($key, $startValueMap[$key]);
                }else{
                    $pk[$key] = $values[$key] = $db->executeCommand('INCR', [static::keyPrefix() . ':s:' . $key]);
                    $this->setAttribute($key, $values[$key]);
                }
            } elseif (is_numeric($pk[$key])) {
                // if pk is numeric update auto increment value
                $currentPk = $db->executeCommand('GET', [static::keyPrefix() . ':s:' . $key]);
                if ($pk[$key] > $currentPk) {
                    $db->executeCommand('SET', [static::keyPrefix() . ':s:' . $key, $pk[$key]]);
                }
            }
        }
        // save pk in a findall pool
        $db->executeCommand('RPUSH', [static::keyPrefix(), static::buildKey($pk)]);

        $key = static::keyPrefix() . ':a:' . static::buildKey($pk);
        // save attributes
        $setArgs = [$key];
        foreach ($values as $attribute => $value) {
            // only insert attributes that are not null
            if ($value !== null) {
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                $setArgs[] = $attribute;
                $setArgs[] = $value;
            }
        }

        if (count($setArgs) > 1) {
            $db->executeCommand('HMSET', $setArgs);
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }
}

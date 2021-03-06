<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\records;

use yii\db\ActiveQueryInterface;
use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $hash
 * @property string $type
 * @property string $query
 * @property ElementQueryCacheRecord[] $elementQueryCaches
 */
class ElementQueryRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

     /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%blitz_elementqueries}}';
    }

    /**
     * Returns the associated element query caches
     *
     * @return ActiveQueryInterface
     */
    public function getElementQueryCaches(): ActiveQueryInterface
    {
        return $this->hasMany(ElementQueryCacheRecord::class, ['queryId' => 'id']);
    }
}

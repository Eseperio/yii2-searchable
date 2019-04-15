<?php
/**
 * @link https://github.com/vuongxuongminh/yii2-search
 * @copyright Copyright (c) 2019 Vuong Xuong Minh
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace vxm\search;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\conditions\InCondition;
use yii\db\ExpressionInterface;
use yii\db\conditions\ConditionInterface;

/**
 * Class SearchableExpression make a searchable condition for ensure an alias of table name.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.0
 */
class SearchableExpression extends BaseObject implements ExpressionInterface
{
    /**
     * @var \yii\db\ActiveQuery
     */
    public $query;

    /**
     * @var int[]|string[]
     */
    public $ids = [];

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (empty($this->ids)) {
            throw new InvalidConfigException('`ids` property must be set to detect id instance!');
        }

        if ($this->query === null) {
            throw new InvalidConfigException('`query` property must be set to create condition instance!');
        }

        parent::init();
    }

    /**
     * Creating condition by search result ids.
     * It making diff because it call on `prepare` state for ensure an alias table name.
     *
     * @return ConditionInterface apply to `where` conditions.
     */
    public function getCondition(): ConditionInterface
    {
        /** @var \yii\db\ActiveRecord $modelClass */
        $modelClass = $this->query->modelClass;
        list(, $alias) = $this->getTableNameAndAlias();
        $key = '{{' . $alias . '}}.[[' . $modelClass::searchableKey() . ']]';

        return new InCondition($key, 'IN', $this->ids);
    }

    /**
     * Returns the table name and the table alias for [[query::modelClass]].
     * This method extract from \yii\db\ActiveQuery.
     *
     * @return array the table name and the table alias.
     */
    private function getTableNameAndAlias(): array
    {
        /** @var \yii\db\ActiveRecord $modelClass */
        $query = $this->query;
        $modelClass = $query->modelClass;

        if (empty($query->from)) {
            $tableName = $modelClass::tableName();
        } else {
            $tableName = '';
            // if the first entry in "from" is an alias-tablename-pair return it directly
            foreach ($query->from as $alias => $tableName) {
                if (is_string($alias)) {
                    return [$tableName, $alias];
                }
                break;
            }
        }

        if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
            $alias = $matches[2];
        } else {
            $alias = $tableName;
        }

        return [$tableName, $alias];
    }

}

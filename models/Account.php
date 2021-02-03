<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Account.
 *
 * @property int $id
 * @property int $client_uid
 * @property int $login
 *
 * @package app\models
 */
class Account extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounts';
    }
}

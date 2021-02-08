<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class User.
 *
 * @property int $id
 * @property int $client_uid
 * @property string $email
 * @property string $gender
 * @property string $fullname
 * @property string $country
 * @property string $region
 * @property string $city
 * @property string $address
 * @property int $partner_id
 * @property string $reg_date
 * @property int $status
 *
 * @package app\models
 */
class User extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * Returns direct partners.
     *
     * @return User[]
     */
    public function getDirectReferrals()
    {
        return static::find()->where([
            'partner_id' => $this->client_uid
        ])
        ->orderBy(['client_uid' => SORT_ASC])
        ->all();
    }
}

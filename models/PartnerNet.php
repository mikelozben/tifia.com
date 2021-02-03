<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class PartnerNet.
 *
 * @property int $client_uid
 * @property int $level
 * @property int $partner_uid
 *
 * @package app\models
 */
class PartnerNet extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'partner_net';
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ['client_uid', 'partner_uid'];
    }
}

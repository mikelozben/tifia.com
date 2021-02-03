<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Trade.
 *
 * @property int $id
 * @property int $ticket
 * @property int $login
 * @property string $symbol
 * @property int $cmd
 * @property float $volume
 * @property string $open_time
 * @property string $close_time
 * @property float $profit
 * @property float $coeff_h
 * @property float $coeff_cr
 *
 * @package app\models
 */
class Trade extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trades';
    }
}

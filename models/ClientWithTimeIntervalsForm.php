<?php

namespace app\models;

use Yii;

/**
 * Class ClientForm.
 *
 * @package app\models
 */
class ClientWithTimeIntervalsForm extends ClientForm
{
    /** @var string */
    public $dateTimeStart;

    /** @var string */
    public $dateTimeEnd;

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['dateTimeStart', 'dateTimeEnd'], 'required'],
            [['dateTimeStart', 'dateTimeEnd'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [
                'dateTimeStart',
                'compare',
                'compareAttribute' => 'dateTimeEnd',
                'operator' => '<',
                'type' => 'number',
                'message' => 'Start time should be less than end time',
            ],
        ]);
    }

    /**
     * Calculates total profit by given params.
     *
     * @return float
     */
    public function calcTotalProfitForReferralNet()
    {
        $this->checkIfValid();

        return Yii::$app->referralNetwork->calcTotalProfitForReferralNetForClientUid(
            $this->clientUid,
            $this->dateTimeStart,
            $this->dateTimeEnd
        );
    }

    /**
     * Calculates total volume (volume*coeff_h*coeff_cr) by given params.
     *
     * @return float
     */
    public function calcTotalVolumeForReferralNet()
    {
        $this->checkIfValid();

        return Yii::$app->referralNetwork->calcTotalVolumeForReferralNetForClientUid(
            $this->clientUid,
            $this->dateTimeStart,
            $this->dateTimeEnd
        );
    }
}

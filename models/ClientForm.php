<?php

namespace app\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Class ClientForm.
 *
 * @package app\models
 */
class ClientForm extends BaseForm
{
    /** @var int */
    public $clientUid;

    public function rules()
    {
        return [
            ['clientUid', 'required'],
            ['clientUid', 'integer', 'min' => 1, 'tooSmall' => 'client uid must be positive'],
        ];
    }

    /**
     * Rebuilds partner net contains given client uid.
     */
    public function rebuildPartnerNet()
    {
        $this->checkIfValid();
        Yii::$app->referralNetwork->rebuildPartnerNetForClientUid($this->clientUid);
    }

    public function calcAllReferralsNumber()
    {
        $this->checkIfValid();
        return Yii::$app->referralNetwork->calcAllReferralsForClientUid($this->clientUid);
    }

    /**
     * Calculates direct referrals number.
     *
     * @return int
     */
    public function calcDirectReferralsNumber()
    {
        $this->checkIfValid();
        return Yii::$app->referralNetwork->calcDirectReferralsForClientUid($this->clientUid);
    }
}

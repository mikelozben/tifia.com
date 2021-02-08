<?php

namespace app\models;

use yii\base\Model;
use yii\base\InvalidArgumentException;

class BaseForm extends Model
{
    /**
     * Validates given form and throws exception if invalid.
     *
     *@throws InvalidArgumentException
     */
    public function checkIfValid()
    {
        if (!$this->validate()) {
            throw new InvalidArgumentException(current($this->getErrorSummary(false)));
        }
    }
}
<?php

use yii\db\Migration;

/**
 * Class m210203_201826_add__indexes__trades
 */
class m210203_201826_add__indexes__trades extends Migration
{
    const TABLE_NAME = 'trades';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx__login',
            self::TABLE_NAME,
            ['login']
        );

        $this->createIndex(
            'idx__close_time',
            self::TABLE_NAME,
            ['close_time']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx__login', self::TABLE_NAME);
        $this->dropIndex('idx__close_time', self::TABLE_NAME);
    }
}

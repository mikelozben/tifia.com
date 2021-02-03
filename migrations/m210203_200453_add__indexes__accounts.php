<?php

use yii\db\Migration;

/**
 * Class m210203_200453_add__indexes__accounts
 */
class m210203_200453_add__indexes__accounts extends Migration
{
    const TABLE_NAME = 'accounts';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx__client_uid',
            self::TABLE_NAME,
            ['client_uid']
        );

        $this->createIndex(
            'idx__login',
            self::TABLE_NAME,
            ['login']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx__client_uid', self::TABLE_NAME);
        $this->dropIndex('idx__login', self::TABLE_NAME);
    }
}

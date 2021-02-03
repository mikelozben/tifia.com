<?php

use yii\db\Migration;

/**
 * Class m210203_164702_add__indexes__users
 */
class m210203_164702_add__indexes__users extends Migration
{
    const TABLE_NAME = 'users';

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
            'idx__partner_id',
            self::TABLE_NAME,
            ['partner_id']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx__partner_id', self::TABLE_NAME);
        $this->dropIndex('idx__client_uid', self::TABLE_NAME);
    }
}

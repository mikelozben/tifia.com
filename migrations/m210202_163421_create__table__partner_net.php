<?php

use yii\db\Migration;

/**
 * Class m210202_163421_create__table__partner_net
 */
class m210202_163421_create__table__partner_net extends Migration
{
    const TABLE_NAME = 'partner_net';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            self::TABLE_NAME,
            [
                'client_uid' => $this->integer()->notNull()->comment('Client uid'),
                'level' => $this->smallInteger()->unsigned()->notNull()->comment('Net level'),
                'partner_uid' => $this->integer()->notNull()->comment('Partner client uid'),
            ]
        );
        $this->addCommentOnTable(self::TABLE_NAME, 'Partners net table');

        $this->addPrimaryKey(
            'partner_net_pk',
            self::TABLE_NAME,
            ['client_uid', 'partner_uid']
        );

        $this->createIndex(
            'idx__client_uid',
            self::TABLE_NAME,
            ['client_uid']
        );

        $this->createIndex(
            'idx__level',
            self::TABLE_NAME,
            ['level']
        );

        $this->createIndex(
            'idx__partner_uid',
            self::TABLE_NAME,
            ['partner_uid']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}

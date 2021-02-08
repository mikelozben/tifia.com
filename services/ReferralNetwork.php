<?php

namespace app\services;

use Yii;
use Exception;
use yii\db\Query;
use yii\base\Component;

/**
 * Class ReferralNetwork.
 *
 * @package app\services
 */
class ReferralNetwork extends Component
{
    public $partnerTableName;
    public $userTableName;
    public $accountsTableName;
    public $tradesTableName;

    /**
     * Drops partner network by given root cliend uid.
     *
     * @param int $clientUid
     *
     * @throws \yii\db\Exception
     */
    public function dropPartnerNetByRootClientUid(int $clientUid)
    {
        $clientUids = [$clientUid];
        while (!empty($clientUids) && ($partnerUids = (new Query())
                ->select(['client_uid'])
                ->from($this->partnerTableName)
                ->where(['in', 'partner_uid', $clientUids])
                ->column()
            )) {
            Yii::$app->db->createCommand()->delete(
                $this->partnerTableName,
                ['in', 'client_uid', $clientUids]
            )->execute();

            $clientUids = $partnerUids;
        }
    }

    /**
     * Forms data for building partners tree.
     *
     * @param array $netLevels
     *
     * @return array
     */
    protected function makeInsertDataForPartnerNet(array $netLevels)
    {
        $insertData = [];

        if (!empty($netLevels)) {
            $prevLevelUids = current($netLevels);
            foreach ($prevLevelUids as $prevLevelUid) {
                $directPartnerUids = array_unique((new Query())
                    ->select(['client_uid'])
                    ->from($this->userTableName)
                    ->where(['partner_id' => $prevLevelUid])
                    ->column()
                );

                foreach ($directPartnerUids as $directPartnerUid) {
                    foreach ($netLevels as $level => $net) {
                        foreach ($net as $partnerUid) {
                            $insertData[] = [(int) $directPartnerUid, $level + 1, $partnerUid];
                        }
                    }

                    $insertData = array_merge(
                        $insertData,
                        $this->makeInsertDataForPartnerNet(array_merge(
                            [[$directPartnerUid]],
                            $netLevels
                        ))
                    );
                }
            }
        }

        return $insertData;
    }

    /**
     * Builds partner tree.
     *
     * @param int $clientUid
     * @param int $level
     *
     * @throws \yii\db\Exception
     */
    protected function buildPartnerNetForClientUid(int $clientUid, int $level)
    {
        $timeStart = microtime(true);
        Yii::debug('[ReferralNetwork::buildPartnerNetForClientUid] start : ' . $timeStart);

        Yii::$app->db->createCommand()->batchInsert(
            $this->partnerTableName,
            ['client_uid', 'level', 'partner_uid'],
            $this->makeInsertDataForPartnerNet([[$clientUid]])
        )->execute();

        $timeEnd = microtime(true);
        Yii::debug(
            '[ReferralNetwork::buildPartnerNetForClientUid] end : '
            . $timeEnd
            . ' (duration : '
            . ($timeEnd - $timeStart)
            . ' sec)'
        );
    }

    /**
     * Returns root client uids fror partner trees.
     *
     * @return array
     */
    public function getRootPartnerUids()
    {
        return (new Query())
            ->select(['client_uid'])
            ->from($this->userTableName)
            ->where(['partner_id' => 0])
            ->orWhere('partner_id IS NULL')
            ->orderBy(['client_uid' => SORT_ASC])
            ->column();
    }

    /**
     * Returns root client uids for partner trees with given client uid inside.
     *
     * @param int $clientUid
     *
     * @return array
     */
    public function getRootPartnerUidsForClientUid(int $clientUid)
    {
        $rootPartnerUids = [];
        $clientUids = [$clientUid];
        $level = 0;

        Yii::debug(
            '[ReferralNetwork::getRootPartnerUidsForClientUid] $clientUid : '
            . json_encode($clientUid)
        );

        $timeStart = microtime(true);
        Yii::debug('[ReferralNetwork::getRootPartnerUidsForClientUid] start : ' . $timeStart);

        while (!empty($clientUids) && ($rows = (new Query())
                ->select(['client_uid', 'partner_id'])
                ->from($this->userTableName)
                ->where(['in', 'client_uid', $clientUids])
                ->all()
            )) {
            Yii::debug(
                "[ReferralNetwork::getRootPartnerUidsForClientUid] * level : {$level}, rows : "
                . json_encode($rows)
            );
            $clientUids = [];
            foreach ($rows as $row) {
                if (empty($row['partner_id'])) {
                    $rootPartnerUids[] = $row['client_uid'];
                } else {
                    $clientUids[] = $row['partner_id'];
                }
            }

            $level--;
        }

        $rootPartnerUids = array_unique($rootPartnerUids);

        $timeEnd = microtime(true);
        Yii::debug(
            '[ReferralNetwork::getRootPartnerUidsForClientUid] end : '
            . $timeEnd
            . ' (duration : '
            . ($timeEnd - $timeStart)
            . ' sec)'
        );
        Yii::debug(
            '[ReferralNetwork::getRootPartnerUidsForClientUid] $rootPartnerUids : '
            . json_encode($rootPartnerUids)
        );

        return $rootPartnerUids;
    }

    /**
     * Rebuilds partner trees including given client uid.
     *
     * @param int $clientUid
     *
     * @throws \Exception
     */
    public function rebuildPartnerNetForClientUid(int $clientUid)
    {
        $rootPartnerUids = $this->getRootPartnerUidsForClientUid($clientUid);
        Yii::debug(
            '[ReferralNetwork::rebuildPartnerNetForClientUid] root partner ids : '
            . json_encode($rootPartnerUids)
        );

        $timeStart = microtime(true);
        Yii::debug(
            '[ReferralNetwork::rebuildPartnerNetForClientUid] start : '
            . $timeStart
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($rootPartnerUids as $rootPartnerUid) {
                self::dropPartnerNetByRootClientUid($rootPartnerUid);
                self::buildPartnerNetForClientUid($rootPartnerUid, 1);
            }

            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            throw $ex;
        }

        $timeEnd = microtime(true);
        Yii::debug(
            '[ReferralNetwork::rebuildPartnerNetForClientUid] end : '
            . $timeEnd
            . ' (duration : '
            . ($timeEnd - $timeStart)
            . ' sec)'
        );
    }

    /**
     * Rebuilds partner network.
     *
     * @throws Exception
     */
    public function rebuildPartnerNet()
    {
        $rootClientUids = $this->getRootPartnerUids();
        Yii::debug(
            '[ReferralNetwork::rebuildPartnerNet] $rootClientUids : '
            . json_encode($rootClientUids)
        );

        $timeStart = microtime(true);
        Yii::debug(
            '[ReferralNetwork::rebuildPartnerNet] start : '
            . $timeStart
        );

        foreach ($rootClientUids as $rootClientUid) {
            $this->rebuildPartnerNetForClientUid($rootClientUid);
        }

        $timeEnd = microtime(true);
        Yii::debug(
            '[ReferralNetwork::rebuildPartnerNet] end : '
            . $timeEnd
            . ' (duration : '
            . ($timeEnd - $timeStart)
            . ' sec)'
        );
    }

    /**
     * Retirns referral uids for given client uid.
     *
     * @param int $clientUid
     *
     * @return array
     */
    public function getReferralNetworkUidsForClientUid(int $clientUid)
    {
        return array_unique((new Query())
            ->select(['client_uid'])
            ->from($this->partnerTableName)
            ->where(['partner_uid' => $clientUid])
            ->column()
        );
    }

    /**
     * Returns trade logins by given client uids.
     *
     * @param array $clientUids
     *
     * @return array
     */
    public function getTradeLoginsForClientUids(array $clientUids)
    {
        return array_unique((new Query())
            ->select(['login'])
            ->from($this->accountsTableName)
            ->where(['in', 'client_uid',  $clientUids])
            ->column()
        );
    }

    /**
     * Calculates total volume (volume*coeff_h*coeff_cr) by given params.
     *
     * @param int $clientUid
     * @param string $dateTimeStart
     * @param string $dateTimeEnd
     *
     * @return float
     */
    public function calcTotalVolumeForReferralNetForClientUid(
        int $clientUid,
        string $dateTimeStart,
        string $dateTimeEnd
    ) {
        $referralNetUids = array_merge([$clientUid], $this->getReferralNetworkUidsForClientUid($clientUid));
        $logins = $this->getTradeLoginsForClientUids($referralNetUids);

        return (float) (new Query())
            ->select('sum(volume*coeff_h*coeff_cr)')
            ->from($this->tradesTableName)
            ->where(['in', 'login', $logins])
            ->andWhere(['between', 'close_time', $dateTimeStart, $dateTimeEnd])
            ->scalar();
    }

    /**
     * Calculates total profit by given params.
     *
     * @param int $clientUid
     * @param string $dateTimeStart
     * @param string $dateTimeEnd
     *
     * @return float
     */
    public function calcTotalProfitForReferralNetForClientUid(
        int $clientUid,
        string $dateTimeStart,
        string $dateTimeEnd
    ) {
        $referralNetUids = array_merge([$clientUid], $this->getReferralNetworkUidsForClientUid($clientUid));
        $logins = $this->getTradeLoginsForClientUids($referralNetUids);

        return (float) (new Query())
            ->select('sum(profit)')
            ->from($this->tradesTableName)
            ->where(['in', 'login', $logins])
            ->andWhere(['between', 'close_time', $dateTimeStart, $dateTimeEnd])
            ->scalar();
    }

    /**
     * Calculates direct referrals number.
     *
     * @param int $clientUid
     *
     * @return int
     */
    public function calcDirectReferralsForClientUid(int $clientUid)
    {
        return (int) (new Query())
            ->select(['client_uid'])
            ->from($this->partnerTableName)
            ->where(['partner_uid' => $clientUid])
            ->andWhere(['level' => 1])
            ->count();
    }

    /**
     * Calculates all referrals number.
     *
     * @param int $clientUid
     *
     * @return int
     */
    public function calcAllReferralsForClientUid(int $clientUid)
    {
        return (int) (new Query())
            ->select(['client_uid'])
            ->from($this->partnerTableName)
            ->where(['partner_uid' => $clientUid])
            ->count();
    }

    /**
     * Loads partners tree in a connections list for given client uid.
     *
     * @param int $clientUid
     *
     * @return array
     */
    public function loadReferralNetworkForClientUid(int $clientUid)
    {
        $parentUids = [$clientUid];
        $tree = [];

        while (!empty($parentUids) && ($nodes =  (new Query())
            ->select(['client_uid', 'partner_uid'])
            ->from($this->partnerTableName)
            ->where(['in', 'partner_uid', $parentUids])
            ->andWhere(['level' => 1])
            ->orderBy(['client_uid' => SORT_ASC])
            ->all()
        )) {
            $parentUids = [];
            foreach ($nodes as $node) {
                $partnerUid = (int) $node['partner_uid'];
                $clientUid = (int) $node['client_uid'];

                $parentUids[] = $clientUid;

                if (!array_key_exists($partnerUid, $tree)) {
                    $tree[$partnerUid] = [];
                }
                $tree[$partnerUid][] = $clientUid;
            }
        }

        return $tree;
    }

    /**
     * Loads root nodes and partners tree in a connections list.
     *
     * @return array
     */
    public function loadReferralNetwork()
    {
        $tree = [];

        $nodes =  (new Query())
            ->select(['client_uid', 'partner_uid'])
            ->from($this->partnerTableName)
            ->where(['level' => 1])
            ->orderBy(['client_uid' => SORT_ASC])
            ->all();

        foreach ($nodes as $node) {
            $partnerUid = (int) $node['partner_uid'];
            $clientUid = (int) $node['client_uid'];

            $parentUids[] = $clientUid;

            if (!array_key_exists($partnerUid, $tree)) {
                $tree[$partnerUid] = [];
            }
            $tree[$partnerUid][] = $clientUid;
        }

        return [
            'rootNodes' => $this->getRootPartnerUids(),
            'tree' => $tree
        ];
    }
}

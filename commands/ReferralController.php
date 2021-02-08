<?php

namespace app\commands;

use app\models\ClientWithTimeIntervalsForm;
use Yii;
use Exception;
use app\models\ClientForm;
use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Referral net commands.
 *
 * @package app\commands
 */
class ReferralController extends Controller
{
    /**
     * Recursively echoes partners net without relations table.
     *
     * @param User $user
     * @param int $netTabulationNum
     * @param int $level
     */
    public static function echoNetLevel(User $user, int $netTabulationNum, int $level) {
        $clientUid = (string) $user->client_uid;
        echo str_repeat(' ', $netTabulationNum)
            . "|- [level #{$level}] "
            . $clientUid . PHP_EOL;

        foreach ($user->getDirectReferrals() as $directReferral) {
            self::echoNetLevel(
                $directReferral,
                $netTabulationNum + 4,
                $level + 1
            );
        }
    }

    /**
     * Recursively echoes partners net using relations table.
     *
     * @param array $tree
     * @param array $nodes
     * @param int $level
     */
    public static function echoPartnerTreeNet(array $tree, array $nodes, int $level) {
        foreach ($nodes as $node) {
            $clientUid = (string)$node;
            echo str_repeat(' ', $level * 4)
                . "|- [level #{$level}] "
                . $clientUid . PHP_EOL;

            if (array_key_exists($node, $tree)) {
                foreach ($tree[$node] as $nextNode) {
                    self::echoPartnerTreeNet(
                        $tree,
                        [$nextNode],
                        $level + 1
                    );
                }
            }
        }
    }

    /**
     * Process exceptions.
     *
     * @param Exception $ex
     *
     * @return int
     */
    public static function processException(Exception $ex)
    {
        echo 'ERROR : ' . $ex->getMessage() . PHP_EOL;
        return ExitCode::DATAERR;
    }

    /**
     * Echoes partner network without using relation table.
     *
     * @return int Exit code
     */
    public function actionFullNetWithoutRelationTable()
    {
        foreach (User::find()
            ->where(['partner_id' => 0])
            ->orWhere('partner_id IS NULL')
            ->orderBy(['client_uid' => SORT_ASC])
            ->all() as $user
        ) {
            self::echoNetLevel($user, 0, 0);
        }

        return ExitCode::OK;
    }

    /**
     * Echoes partner network without using relation table.
     *
     * @return int Exit code
     */
    public function actionFullNetWithRelationTable()
    {
        $referralNetworkData = Yii::$app->referralNetwork->loadReferralNetwork();

        self::echoPartnerTreeNet(
            $referralNetworkData['tree'],
            $referralNetworkData['rootNodes'],
            0
        );

        return ExitCode::OK;
    }

    /**
     * Echoes partner network for given client uid, e.g. 82824897, without using relation table.
     *
     * @param int $clientUid
     *
     * @return int Exit code
     */
    public function actionFullNetForClientWithoutRelationTable(int $clientUid)
    {
        $clientForm = new ClientForm(['clientUid' => $clientUid]);

        try {
            $clientForm->checkIfValid();

            self::echoNetLevel(
                User::findOne(['client_uid' => $clientForm->clientUid]),
                0,
                0
            );
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }

    /**
     * Echoes partner network for given client uid, e.g. 82824897, using relation table.
     *
     * @param int $clientUid
     *
     * @return int Exit code
     */
    public function actionFullNetForClientWithRelationTable(int $clientUid)
    {
        $clientForm = new ClientForm(['clientUid' => $clientUid]);

        try {
            $clientForm->checkIfValid();

            self::echoPartnerTreeNet(
                Yii::$app->referralNetwork->loadReferralNetworkForClientUid($clientForm->clientUid),
                [$clientForm->clientUid],
                0
            );
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }

    /**
     * Rebuilds partner net contains given client uid.
     *
     * @param $clientUid
     *
     * @return int Exit code
     *
     * @throws Exception
     */
    public function actionRebuildPartnerNetForClient(int $clientUid)
    {
        $clientForm = new ClientForm(['clientUid' => $clientUid]);

        try {
            $clientForm->rebuildPartnerNet();
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }

    /**
     * Rebuilds all partner net.
     *
     * @return int Exit code
     */
    public function actionRebuildPartnerNet()
    {
        Yii::$app->referralNetwork->rebuildPartnerNet();

        return ExitCode::OK;
    }

    /**
     * Calculates total volume (volume*coeff_h*coeff_cr) by given params.
     *
     * @param int $clientUid
     * @param string $dateTimeStart
     * @param string $dateTimeEnd
     *
     * @return int
     */
    public function actionGetTotalVolumeForReferralNetForClientUid(
        int $clientUid,
        string $dateTimeStart,
        string $dateTimeEnd

    ) {
        $clientWithTimeIntervalsForm = new ClientWithTimeIntervalsForm([
            'clientUid' => $clientUid,
            'dateTimeStart' => $dateTimeStart,
            'dateTimeEnd' => $dateTimeEnd,
        ]);

        try {
            echo 'volume : '
                . $clientWithTimeIntervalsForm->calcTotalVolumeForReferralNet()
                . PHP_EOL;
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }

    /**
     * Calculates total profit by given params.
     *
     * @param int $clientUid
     * @param string $dateTimeStart
     * @param string $dateTimeEnd
     *
     * @return int
     */
    public function actionGetTotalProfitForReferralNetForClientUid(
        int $clientUid,
        string $dateTimeStart,
        string $dateTimeEnd

    ) {
        $clientWithTimeIntervalsForm = new ClientWithTimeIntervalsForm([
            'clientUid' => $clientUid,
            'dateTimeStart' => $dateTimeStart,
            'dateTimeEnd' => $dateTimeEnd,
        ]);

        try {
            echo 'profit : '
                . $clientWithTimeIntervalsForm->calcTotalProfitForReferralNet()
                . PHP_EOL;
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }

    /**
     * Calculates direct referrals number.
     *
     * @param int $clientUid
     *
     * @return int
     */
    public function actionGetDirectReferralsNumberForClientUid(int $clientUid) {
        $clientForm = new ClientForm(['clientUid' => $clientUid]);

        try {
            echo 'direct referrals number : '
                . $clientForm->calcDirectReferralsNumber()
                . PHP_EOL;
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }

    /**
     * Calculates all referrals number.
     *
     * @param int $clientUid
     *
     * @return int
     */
    public function actionGetAllReferralsNumberForClientUid(int $clientUid) {
        $clientForm = new ClientForm(['clientUid' => $clientUid]);

        try {
            echo 'direct referrals number : '
                . $clientForm->calcAllReferralsNumber()
                . PHP_EOL;
        } catch (Exception $ex) {
            self::processException($ex);
        }

        return ExitCode::OK;
    }
}

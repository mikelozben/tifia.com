<?php

namespace app\commands;

use Yii;
use app\models\PartnerNet;
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
     * Echoes partner network for given client uid, e.g. 82824897, without using relation table.
     *
     * @param $client_uid
     *
     * @return int Exit code
     */
    public function actionFullNetForClientWithoutRelationTable(int $clientUid)
    {
        self::echoNetLevel(User::findOne(['client_uid' => $clientUid]), 0, 0);

        return ExitCode::OK;
    }

    /**
     * Rebuilds partner net contains given client uid.
     *
     * @param $clientUid
     *
     * @return int Exit code
     *
     * @throws \Exception
     */
    public function actionRebuildPartnerNetForClient(int $clientUid)
    {
        Yii::$app->referralNetwork->rebuildPartnerNetForClientUid($clientUid);

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
        echo 'volume : '
            . Yii::$app->referralNetwork->calcTotalVolumeForReferralNetForClientUid(
                $clientUid,
                $dateTimeStart,
                $dateTimeEnd
            )
            . PHP_EOL;

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
        echo 'profit : '
            . Yii::$app->referralNetwork->calcTotalProfitForReferralNetForClientUid(
                $clientUid,
                $dateTimeStart,
                $dateTimeEnd
            )
            . PHP_EOL;

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
        echo 'direct referrals number : '
            . Yii::$app->referralNetwork->calcDirectReferralsForClientUid($clientUid)
            . PHP_EOL;

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
        echo 'direct referrals number : '
            . Yii::$app->referralNetwork->calcAllReferralsForClientUid($clientUid)
            . PHP_EOL;

        return ExitCode::OK;
    }
}

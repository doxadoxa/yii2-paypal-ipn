<?php
namespace frontend\controllers;

use c006\paypal_ipn\PayPal_Ipn;
use sokoji\payPalIPN\PayPalIPN;
use Yii;
use yii\web\Controller;

class PaypalController extends Controller
{
    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    function beforeAction($action)
    {
        $this->enableCsrfValidation = ($action != 'ipn');
        return parent::beforeAction($action);
    }

    /**
     * @throws \yii\base\Exception
     */
    public function actionIpn()
    {
        if (isset($_POST)) {
            $ipn = new PayPalIPN(true, true); // sandbox = true, debug = true
            if ($ipn->checkIpnRequest()) {

                /* Get any key/value */
                $custom = $ipn->getKeyValue('custom');

                // Do something with $custom
            }
        }
    }


}


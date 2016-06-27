<?php

namespace sokoji\payPalIPN;

use yii;
use yii\base\Exception;

/**
 * Class PayPalIPN
 *
 * @package sokoji\payPalIPN
 */

/**
 * Class PayPalIPN
 * @package sokoji\payPalIPN
 * @author Jon Chambers <jchambers.dev@gmail.com>
 * @author Kirill Arutyunov <kirill@arutynov.me>
 */
class PayPalIPN
{
    /**
     * @var bool sandbox mode
     */
    public $sandbox = false;

    /**
     * @var string
     */
    private $sandboxUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    /**
     * @var string
     */
    private $liveUrl = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * POST data for IPN verify request
     *
     * @var array|null
     */
    private $postData;

    /**
     * @var string
     */
    private $validateQuery = 'cmd=_notify-validate';

    /**
     * Debug mode
     *
     * @var bool
     */
    private $debug = false;

    /**
     * Data from IPN
     *
     * @var array
     */
    private $ipnData = [];


    /**
     * @param bool $sandbox — use sandbox mode
     * @param bool $debug   — use debug (log everything into app logfile)
     */
    function __construct($sandbox = false, $debug = false)
    {
        $this->sandbox = $sandbox;
        $this->debug = $debug;
    }

    /**
     * Checks IPN Request and verify it. Return true if IPN is VERIFIED
     *
     * @return bool
     * @throws Exception
     */
    function checkIpnRequest()
    {
        $this->ipnData = [];
        $this->postData = file_get_contents('php://input');
        $this->postData = explode('&', $this->postData);

        foreach ($this->postData as $item) {
            list($key, $value) = explode('=', stripslashes($item));
            $this->ipnData[$key] = urldecode($value);
            $this->validateQuery .= '&' . $key . '=' . $value;
        }

        $ch = ($this->sandbox) ? curl_init($this->liveUrl) : curl_init($this->sandboxUrl);

        if ($ch == false) {
            Yii::info(date('[Y-m-d H:i e] ') . "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 'app');
            return false;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->validateQuery);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 4);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        if ($this->debug == true) {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        $return = curl_exec($ch);

        $response = curl_exec($ch);
        $responseStatus = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if ($response === false || $responseStatus == '0') {
            $errNo = curl_errno($ch);
            $errStr = curl_error($ch);
            throw new Exception("cURL error: [$errNo] $errStr");
        }

        if ($this->debug) {
            Yii::info(PHP_EOL . date('[Y-m-d H:i e] ') . "HTTP request of validation request:" . curl_getinfo($ch, CURLINFO_HEADER_OUT) . PHP_EOL, 'app');
            Yii::info(PHP_EOL . date('[Y-m-d H:i e] ') . "HTTP response of validation request: " . $this->validateQuery . PHP_EOL, 'app');
        }

        curl_close($ch);

        if (stripos($return, 'VERIFIED') !== false) {
            if($this->debug) {
                Yii::info(PHP_EOL . 'VERIFIED: ', 'app');
                Yii::info(PHP_EOL . print_r($this->validateQuery, TRUE) . PHP_EOL, 'app');
            }

            return true;
        }

        Yii::info(PHP_EOL . date('[Y-m-d H:i e] ') . "Invalid IPN:" . $this->validateQuery . PHP_EOL, 'app');

        return false;
    }


    /**
     * @return array
     */
    public function getIpnData()
    {
        return $this->ipnData;
    }


    /**
     * @param $key
     * @return string|null
     */
    public function getKeyValue($key)
    {
        foreach ($this->ipnData as $k => $v) {
            if (strtolower($key) == strtolower($k)) {
                return $v;
            }
        }

        return null;
    }
}
Yii2 PayPal IPN
===================

This is a simple PayPal IPN listener, no other files required.




Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-source "sokoji/yii2-paypal-ipn" "dev-master"
```

or add

```
"sokoji/yii2-paypal-ipn": "dev-master"
```

to the require section of your `composer.json` file.



Usage
-----

Test with [IpnSimulator](https://developer.paypal.com/developer/ipnSimulator).

You must sign up on the dev site to use.

Example of action:

```
public function actionIPN()
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
```



Updates
--------
Here is no updates.



Contributors
-----------



Useful Links
------------

* [IpnSimulator](https://developer.paypal.com/developer/ipnSimulator)
* [IPN History (SandBox PayPal Dashboard)](https://www.sandbox.paypal.com/au/cgi-bin/webscr?cmd=%5fdisplay%2dipns%2dhistory&nav=0%2e3%2e4)

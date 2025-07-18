emerchantpay Gateway Module for X-Cart
======================================

[![Software License](https://img.shields.io/badge/license-GPL-green.svg?style=flat)](LICENSE)

This is a Payment Module for X-Cart, that gives you the ability to process payments through emerchantpay's Payment Gateway - Genesis.

Requirements
------------

* X-Cart 5.5.x (Tested up to 5.5.1.44)
* [GenesisPHP v2.1.5](https://github.com/GenesisGateway/genesis_php/releases/tag/2.1.5) - (Integrated in Module)

GenesisPHP Requirements
------------

* PHP version 5.5.9 or newer
* PHP Extensions:
    * [BCMath](https://php.net/bcmath)
    * [CURL](https://php.net/curl) (required, only if you use the curl network interface)
    * [Filter](https://php.net/filter)
    * [Hash](https://php.net/hash)
    * [XMLReader](https://php.net/xmlreader)
    * [XMLWriter](https://php.net/xmlwriter)
    * [JSON](https://www.php.net/manual/en/book.json)
    * [OpenSSL](https://www.php.net/manual/en/book.openssl.php)

Installation (Manual)
------------

* Download the archive with the desired release and extract the files in a separate folder
* Upload the folder ```modules``` to the ```<root>``` folder of your X-Cart installation
* In the ```<root>``` folder of your X-Cart installation run:
  ```$ ./bin/service xcst:rebuild```
* To enable the plugin in the ```<root>``` folder of your X-Cart installation run:
  ```$ ./bin/service xcst:rebuild --enable=EMerchantPay-Genesis```

Configuration
------------

* In the X-Cart admin panel navigate to `Store::Payment methods` and select `emerchantpay Checkout` settings
* Fill in the Username, Password and select the appropriate transaction types.
* For testing purposes use the test environment from `Test/Live mode` drop down list.
* When ready save the settings and enable the plugin by using the green toggle button located under the plugin name.

Supported Transactions & Payment Methods
---------------------
* ```emerchantpay Checkout``` Payment Method
  * __Apple Pay__
  * __Argencard__
  * __Aura__
  * __Authorize__
  * __Authorize (3D-Secure)__
  * __Baloto__
  * __Bancomer__
  * __Bancontact__
  * __Banco de Occidente__
  * __Banco do Brasil__
  * __BitPay__
  * __Boleto__
  * __Bradesco__
  * __Cabal__
  * __CashU__
  * __Cencosud__
  * __Davivienda__
  * __Efecty__
  * __Elo__
  * __eps__
  * __eZeeWallet__
  * __Fashioncheque__
  * __Google Pay__
  * __iDeal__
  * __iDebit__
  * __InstaDebit__
  * __InitRecurringSale__
  * __InitRecurringSale (3D-Secure)__
  * __Intersolve__
  * __Itau__
  * __Multibanco__
  * __MyBank__
  * __Naranja__
  * __Nativa__
  * __Neosurf__
  * __Neteller__
  * __Online Banking__
    * __Interac Combined Pay-in (CPI)__ 
    * __Bancontact (BCT)__ 
    * __BLIK (BLK)__
    * __SPEI (SE)__
    * __PayID (PID)__
  * __OXXO__
  * __P24__
  * __Pago Facil__
  * __PayPal__
  * __PaySafeCard__
  * __PayU__
  * __Pix__
  * __POLi__
  * __Post Finance__
  * __PSE__
  * __RapiPago__
  * __Redpagos__
  * __SafetyPay__
  * __Sale__
  * __Sale (3D-Secure)__
  * __Santander__
  * __Sepa Direct Debit__
  * __SOFORT__
  * __Tarjeta Shopping__
  * __TCS__
  * __Trustly__
  * __TrustPay__
  * __UPI__
  * __WebMoney__
  * __WebPay__
  * __WeChat__

_Note_: If you have trouble with your credentials or terminal configuration, get in touch with our [support] team

You're now ready to process payments through our gateway.

Development
------------
* Install dev packages
```shell
composer install
```
* Run PHP Code Sniffer
```shell
composer php-cs
```
* Run PHP Mess Detector
```shell
composer php-md
```

[support]: mailto:tech-support@emerchantpay.net

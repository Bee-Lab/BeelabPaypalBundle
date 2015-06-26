BeelabPaypalBundle Documentation
================================

## Installation

1. [Installation](#1-installation)
2. [Configuration](#2-configuration)
3. [Usage](#3-usage)

### 1. Installation

Run from terminal:

```bash
$ composer require beelab/paypal-bundle
```

Enable bundle in the kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Beelab\PaypalBundle\BeelabPaypalBundle(),
    );
}
```

### 2. Configuration

You must configure your Paypal credentials. Also, you need to configure two routes, one
for return (after successful payment) and one for cancel (aborted payment).
You likely want to put this in your main configuration file:

```yaml
# app/config/config.yml

beelab_paypal:
    return_route: your_return_route
    cancel_route: your_cancel_route
    test_mode:    "%kernel.debug%"
```

Then, your production configuration file (suppose you created some parameters entries):

```yaml
# app/config/config_prod.yml

beelab_paypal:
    username:  "%paypal_username%"
    password:  "%paypal_password%"
    signature: "%paypal_signature%"
```

And the same configuration in your dev configuration file, with Paypal sandbox credentials.

There is a basic entity, representing your transaction (the one for which you need a payment).
You need to extend it and, of course, you can add your own properties or relationships.
Create a ``Transaction`` entity class:

```php
<?php
// src/AppBundle/Entity
namespace AppBundle\Entity;

use Beelab\PaypalBundle\Entity\Transaction as BaseTransaction
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Transaction extends BaseTransaction
{
    // if you need other properties, or relationships, add them here...

    public function getDescription()
    {
        // here you can return a generic description, if you don't want to list items
    }

    public function getItems()
    {
        // here you can return an array of items, with each item being an array of name, quantity, price
        // Note that if the total (price * quantity) of items doesn't match total amount, this won't work
    }

    public function getShippingAmount()
    {
        // here you can return shipping amount. This amount MUST be already in your total amount
    }
}
```

### 3. Usage

You can now implement your actions inside a controller:

```php
<?php
// src/AppBundle/Controller/DefaultController
namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use Beelab\PaypalBundle\Paypal\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefaultController
{
    public function paymentAction(Request $request)
    {
        $amount = 100;  // get an amount, e.g. from your cart
        $transaction = new Transaction($amount);
        try {
            $response = $this->get('beelab_paypal.service')->setTransaction($transaction)->start();
            $this->getDoctrine()->getManager()->persist($transaction);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($response->getRedirectUrl());
        } catch (Exception $e) {
            throw new HttpException(503, 'Payment error', $e);
        }
    }

    /**
     * The route configured in "cancel_route" (see above) should point here
     */
    public function canceledPaymentAction(Request $request)
    {
        $token = $request->query->get('token');
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);
        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $transaction->cancel(null);
        $this->getDoctrine()->getManager()->flush();

        return array(); // or a Response...
    }

    /**
     * The route configured in "return_route" (see above) should point here
     */
    public function completedPaymentAction(Request $request)
    {
        $token = $request->query->get('token');
        $transaction = $this->getDoctrine()->getRepository('AppBundle:Transaction')->findOneByToken($token);
        if (is_null($transaction)) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $this->get('beelab_paypal.service')->setTransaction($transaction)->complete();
        $this->getDoctrine()->getManager()->flush();
        if (!$transazione->isOk()) {
            return array(); // or a Response (in case of error)
        }

        return array(); // or a Response (in case of success)
    }
}
```

If you need to pass some custom parameters to Paypal, you can use the optional second parameter of ``setTransaction``
method. For example, if you want to hide shipping address, you can do:

```php
$response = $this->get('beelab_paypal.service')->setTransaction($transaction, array('noShipping' => 1))->start();
```

For a complete set of options, please refer to Paypal official documentation or to OmniPay documentation.

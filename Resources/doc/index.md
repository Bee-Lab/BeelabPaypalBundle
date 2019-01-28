BeelabPaypalBundle Documentation
================================

## Installation

1. [Installation](#1-installation)
2. [Configuration](#2-configuration)
3. [Usage](#3-usage)

### 1. Installation

Install the bundle:

```bash
$ composer require beelab/paypal-bundle
```

If you didn't already installed Omnipay, you'll need to install a library implementing 
[php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation)
*before*. For example:

```bash
$ composer require php-http/guzzle6-adapter
```

Enable bundle in the kernel (unless you're using flex):

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Beelab\PaypalBundle\BeelabPaypalBundle(),
    ];
}
```

### 2. Configuration

You must configure your Paypal credentials. Also, you need to configure two routes, one
for return (after successful payment) and one for cancel (aborted payment).
You likely want to put this in your main configuration file:

```yaml
# config/packages/beelab_paypal.yaml

beelab_paypal:
    username: "%paypal_username%"
    password: "%paypal_password%"
    signature: "%paypal_signature%"
    return_route: your_return_route
    cancel_route: your_cancel_route
    test_mode: "%kernel.debug%"
```

You should change your parameters in dev environment, using Paypal sandbox credentials.

There is a basic entity, representing your transaction (the one for which you need a payment).
You need to extend it and, of course, you can add your own properties or relationships.
Create a `Transaction` entity class:

```php
<?php
// src/Entity
namespace App\Entity;

use Beelab\PaypalBundle\Entity\Transaction as BaseTransaction;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Transaction extends BaseTransaction
{
    // if you need other properties, or relationships, add them here...

    public function getDescription(): ?string
    {
        // here you can return a generic description, if you don't want to list items
    }

    public function getItems(): array
    {
        // here you can return an array of items, with each item being an array of name, quantity, price
        // Note that if the total (price * quantity) of items doesn't match total amount, this won't work
    }

    public function getShippingAmount(): string
    {
        // here you can return shipping amount. This amount MUST be already in your total amount
    }
}
```

### 3. Usage

You can now implement your actions inside a controller:

```php
<?php
// src/Controller/DefaultController
namespace App\Controller;

use App\Entity\Transaction;
use Beelab\PaypalBundle\Paypal\Exception;
use Beelab\PaypalBundle\Paypal\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefaultController
{
    public function payment(Service $service)
    {
        $amount = 100;  // get an amount, e.g. from your cart
        $transaction = new Transaction($amount);
        try {
            $response = $service->setTransaction($transaction)->start();
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
    public function canceledPayment(Request $request)
    {
        $token = $request->query->get('token');
        $transaction = $this->getDoctrine()->getRepository('App:Transaction')->findOneByToken($token);
        if (null === $transaction) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $transaction->cancel(null);
        $this->getDoctrine()->getManager()->flush();

        return []; // or a Response...
    }

    /**
     * The route configured in "return_route" (see above) should point here
     */
    public function completedPayment(Service $service, Request $request)
    {
        $token = $request->query->get('token');
        $transaction = $this->getDoctrine()->getRepository('App:Transaction')->findOneByToken($token);
        if (null === $transaction) {
            throw $this->createNotFoundException(sprintf('Transaction with token %s not found.', $token));
        }
        $service->setTransaction($transaction)->complete();
        $this->getDoctrine()->getManager()->flush();
        if (!$transazione->isOk()) {
            return []; // or a Response (in case of error)
        }

        return []; // or a Response (in case of success)
    }
}
```

If you need to pass some custom parameters to Paypal, you can use the optional second parameter of `setTransaction`
method. For example, if you want to hide shipping address, you can do:

If you need to use your custom class instead of bundle's `Service`, you can extend `Service` and define your
class a public service. Using `service_class` option was supported, but it's now deprecated.

```php
$response = $service->setTransaction($transaction, ['noShipping' => 1])->start();
```

For a complete set of options, please refer to Paypal official documentation or to OmniPay documentation.

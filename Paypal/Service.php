<?php

namespace Beelab\PaypalBundle\Paypal;

use Beelab\PaypalBundle\Entity\Transaction;
use Omnipay\PayPal\ExpressGateway as Gateway;
use RuntimeException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Paypal service
 */
class Service
{
    private $gateway;
    private $router;
    private $config;
    private $params;
    private $transaction;

    /**
     * @param Gateway         $gateway
     * @param RouterInterface $router
     * @param Transaction     $transaction
     * @param array           $config
     */
    public function __construct(Gateway $gateway, RouterInterface $router, array $config)
    {
        $gateway
            ->setUsername($config['username'])
            ->setPassword($config['password'])
            ->setSignature($config['signature'])
            ->setTestMode($config['test_mode'])
        ;
        $this->gateway = $gateway;
        $this->config = $config;
        $this->router = $router;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->params = array(
            'amount'        => $transaction->getAmount(),
            'currency'      => $this->config['currency'],
            'description'   => '',
            'transactionId' => $transaction->getId(),
            'returnUrl'     => $this->router->generate(
                $this->config['return_route'],
                array(),
                RouterInterface::ABSOLUTE_URL
            ),
            'cancelUrl'     => $this->router->generate(
                $this->config['cancel_route'],
                array(),
                RouterInterface::ABSOLUTE_URL
            ),
        );
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    public function start()
    {
        if (is_null($this->transaction)) {
            throw new RuntimeException('Transaction not defined. Call setTransaction() first.');
        }
        $response = $this->gateway->purchase($this->params)->send();
        if (!$response->isRedirect()) {
            throw new Exception($response->getMessage());
        }
        $this->transaction->setToken($response->getTransactionReference());

        return $response;
    }

    /**
     * @param Transaction $transaction
     */
    public function complete()
    {
        if (is_null($this->transaction)) {
            throw new RuntimeException('Transaction not defined. Call setTransaction() first.');
        }
        $response = $this->gateway->completePurchase($this->params)->send();
        $this->transaction->complete($response->getData());
    }
}

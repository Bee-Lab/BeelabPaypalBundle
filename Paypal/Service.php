<?php

namespace Beelab\PaypalBundle\Paypal;

use Beelab\PaypalBundle\Entity\Transaction;
use Omnipay\PayPal\ExpressGateway as Gateway;
use RuntimeException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Paypal service.
 */
class Service
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $params;

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
     * Set Transaction and parameters.
     *
     * @param Transaction $transaction
     * @param array       $customParameters
     *
     * @return Service
     */
    public function setTransaction(Transaction $transaction, array $customParameters = array())
    {
        $defaultParameters = array(
            'amount' => $transaction->getAmount(),
            'currency' => $this->config['currency'],
            'description' => $transaction->getDescription(),
            'transactionId' => $transaction->getId(),
            'returnUrl' => $this->router->generate(
                $this->config['return_route'],
                array(),
                RouterInterface::ABSOLUTE_URL
            ),
            'cancelUrl' => $this->router->generate(
                $this->config['cancel_route'],
                array(),
                RouterInterface::ABSOLUTE_URL
            ),
        );
        $this->params = array_merge($defaultParameters, $customParameters);
        $this->transaction = $transaction;
        $items = $transaction->getItems();
        if (!empty($items)) {
            $this->params['shippingAmount'] = $transaction->getShippingAmount();
        }

        return $this;
    }

    /**
     * Start transaction. You need to call setTransaction() before.
     *
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    public function start()
    {
        if (is_null($this->transaction)) {
            throw new RuntimeException('Transaction not defined. Call setTransaction() first.');
        }
        $items = $this->transaction->getItems();
        $purchase = $this->gateway->purchase($this->params);
        $response = !empty($items) ? $purchase->setItems($items)->send() : $purchase->send();
        if (!$response->isRedirect()) {
            throw new Exception($response->getMessage());
        }
        $this->transaction->setToken($response->getTransactionReference());

        return $response;
    }

    /**
     * Complete transaction. You need to call setTransaction() before.
     */
    public function complete()
    {
        if (is_null($this->transaction)) {
            throw new RuntimeException('Transaction not defined. Call setTransaction() first.');
        }
        $items = $this->transaction->getItems();
        $purchase = $this->gateway->completePurchase($this->params);
        $response = !empty($items) ? $purchase->setItems($items)->send() : $purchase->send();
        $responseData = $response->getData();
        if (!isset($responseData['ACK'])) {
            throw new RuntimeException('Missing ACK Payapl in response data.');
        }
        if ($responseData['ACK'] != 'Success' && $responseData['ACK'] != 'SuccessWithWarning') {
            $this->transaction->error($responseData);
        } else {
            $this->transaction->complete($responseData);
        }
    }
}

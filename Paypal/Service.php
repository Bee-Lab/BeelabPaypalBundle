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
     *
     * @return Service
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->params = array(
            'amount'        => $transaction->getAmount(),
            'currency'      => $this->config['currency'],
            'description'   => $transaction->getDescription(),
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
     * Start transaction. You need to call setTransaction() before.
     *
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
     * Complete transaction. You need to call setTransaction() before.
     */
    public function complete()
    {
        if (is_null($this->transaction)) {
            throw new RuntimeException('Transaction not defined. Call setTransaction() first.');
        }
        $response = $this->gateway->completePurchase($this->params)->send();
        $responseData = $response->getData();
        if (!isset($responseData['ACK'])) {
            throw new RuntimeException('Missing ACK Payapl in response data.');
        }
        if ($responseData['ACK'] != 'Success' && $responseData['ACK'] != 'SuccessWithWarning') {
            throw new Exception(sprintf('Paypal failure: %s', $responseData['ACK']));
        }
        $this->transaction->complete($responseData);
    }
}

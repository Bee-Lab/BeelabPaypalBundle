<?php

namespace Beelab\PaypalBundle\Tests\Paypal;

use Beelab\PaypalBundle\Paypal\Service;
use Beelab\PaypalBundle\Test\TransactionStub as Transaction;
use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */
class ServiceTest extends PHPUnit_Framework_TestCase
{
    private $gateway;
    private $router;
    private $service;

    protected function setUp()
    {
        $this->gateway = $this->getMock('Omnipay\PayPal\ExpressGateway');
        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $config = array(
            'username'     => 'a',
            'password'     => 'b',
            'signature'    => 'c',
            'currency'     => 'EUR',
            'return_route' => 'pippo',
            'cancel_route' => 'pluto',
            'test_mode'    => true,
        );
        $this->gateway
            ->expects($this->once())
            ->method('setUsername')
            ->with('a')
            ->will($this->returnSelf())
        ;
        $this->gateway
            ->expects($this->once())
            ->method('setPassword')
            ->with('b')
            ->will($this->returnSelf())
        ;
        $this->gateway
            ->expects($this->once())
            ->method('setSignature')
            ->with('c')
            ->will($this->returnSelf())
        ;
        $this->gateway
            ->expects($this->once())
            ->method('setTestMode')
            ->with(true)
            ->will($this->returnSelf())
        ;
        $this->service = new Service($this->gateway, $this->router, $config);
    }

    public function testStart()
    {
        $request = $this->getRequestMock();
        $response = $this->getMock('Omnipay\Common\Message\ResponseInterface');
        $this->gateway
            ->expects($this->once())
            ->method('purchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        ;
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('isRedirect')
            ->will($this->returnValue(true))
        ;
        $response
            ->expects($this->once())
            ->method('getTransactionReference')
            ->will($this->returnValue('ref'))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->start();
    }

    /**
     * @expectedException \Beelab\PaypalBundle\Paypal\Exception
     */
    public function testStartFailure()
    {
        $request = $this->getRequestMock();
        $response = $this->getMock('Omnipay\Common\Message\ResponseInterface');
        $this->gateway
            ->expects($this->once())
            ->method('purchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        ;
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('isRedirect')
            ->will($this->returnValue(false))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->start();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testStartWithoutTransaction()
    {
        $this->service->start();
    }

    public function testCompleteWithSuccess()
    {
        $request = $this->getRequestMock();
        $response = $this->getMock('Omnipay\Common\Message\ResponseInterface');
        $this->gateway
            ->expects($this->once())
            ->method('completePurchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        ;
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array('ACK' => 'Success')))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->complete();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCompleteWithFailure()
    {
        $request = $this->getRequestMock();
        $response = $this->getMock('Omnipay\Common\Message\ResponseInterface');
        $this->gateway
            ->expects($this->once())
            ->method('completePurchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        ;
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array('foo' => 'bar')))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->complete();
    }

    public function testCompleteWithError()
    {
        $request = $this->getRequestMock();
        $response = $this->getMock('Omnipay\Common\Message\ResponseInterface');
        $this->gateway
            ->expects($this->once())
            ->method('completePurchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        ;
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array('ACK' => 'Failure')))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->complete();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCompleteWithoutTransaction()
    {
        $this->service->complete();
    }

    private function getRequestMock()
    {
        $methods = array('setItems', 'initialize', 'getParameters', 'getResponse', 'send', 'sendData', 'getData');

        return $this->getMock('Omnipay\Common\Message\RequestInterface', $methods);
    }
}

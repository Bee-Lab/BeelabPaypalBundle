<?php

namespace Beelab\PaypalBundle\Tests\Paypal;

use Beelab\PaypalBundle\Paypal\Service;
use Beelab\PaypalBundle\Test\TransactionStub as Transaction;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ServiceTest extends TestCase
{
    private $gateway;
    private $router;
    private $service;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock('Omnipay\PayPal\ExpressGateway');
        $this->router = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $config = [
            'username' => 'a',
            'password' => 'b',
            'signature' => 'c',
            'currency' => 'EUR',
            'return_route' => 'pippo',
            'cancel_route' => 'pluto',
            'test_mode' => true,
        ];
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

    public function testStart(): void
    {
        $request = $this->getRequestMock();
        $response = $this->getMockBuilder('Omnipay\Common\Message\ResponseInterface')->getMock();
        $this->gateway
            ->expects($this->once())
            ->method('purchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
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

    public function testStartFailure(): void
    {
        $this->expectException(\Beelab\PaypalBundle\Paypal\Exception::class);

        $request = $this->getRequestMock();
        $response = $this->getMockBuilder('Omnipay\Common\Message\ResponseInterface')->getMock();
        $this->gateway
            ->expects($this->once())
            ->method('purchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
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

    public function testStartWithoutTransaction(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service->start();
    }

    public function testCompleteWithSuccess(): void
    {
        $request = $this->getRequestMock();
        $response = $this->getMockBuilder('Omnipay\Common\Message\ResponseInterface')->getMock();
        $this->gateway
            ->expects($this->once())
            ->method('completePurchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(['ACK' => 'Success']))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->complete();
    }

    public function testCompleteWithFailure(): void
    {
        $this->expectException(\RuntimeException::class);

        $request = $this->getRequestMock();
        $response = $this->getMockBuilder('Omnipay\Common\Message\ResponseInterface')->getMock();
        $this->gateway
            ->expects($this->once())
            ->method('completePurchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(['foo' => 'bar']))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->complete();
    }

    public function testCompleteWithError(): void
    {
        $request = $this->getRequestMock();
        $response = $this->getMockBuilder('Omnipay\Common\Message\ResponseInterface')->getMock();
        $this->gateway
            ->expects($this->once())
            ->method('completePurchase')
            ->will($this->returnValue($request))
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->will($this->returnSelf());
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response))
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(['ACK' => 'Failure']))
        ;

        $transaction = new Transaction(11);
        $this->service->setTransaction($transaction);
        $this->service->complete();
    }

    public function testCompleteWithoutTransaction(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service->complete();
    }

    private function getRequestMock()
    {
        $methods = ['setItems', 'initialize', 'getParameters', 'getResponse', 'send', 'sendData', 'getData'];

        return $this->getMockBuilder('Omnipay\Common\Message\RequestInterface')->setMethods($methods)->getMock();
    }
}

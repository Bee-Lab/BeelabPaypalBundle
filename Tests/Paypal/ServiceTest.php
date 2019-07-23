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
            ->willReturnSelf()
        ;
        $this->gateway
            ->expects($this->once())
            ->method('setPassword')
            ->with('b')
            ->willReturnSelf()
        ;
        $this->gateway
            ->expects($this->once())
            ->method('setSignature')
            ->with('c')
            ->willReturnSelf()
        ;
        $this->gateway
            ->expects($this->once())
            ->method('setTestMode')
            ->with(true)
            ->willReturnSelf()
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
            ->willReturn($request)
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $response
            ->expects($this->once())
            ->method('isRedirect')
            ->willReturn(true)
        ;
        $response
            ->expects($this->once())
            ->method('getTransactionReference')
            ->willReturn('ref')
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
            ->willReturn($request)
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $response
            ->expects($this->once())
            ->method('isRedirect')
            ->willReturn(false)
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
            ->willReturn($request)
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['ACK' => 'Success'])
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
            ->willReturn($request)
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['foo' => 'bar'])
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
            ->willReturn($request)
        ;
        $request
            ->expects($this->once())
            ->method('setItems')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('send')
            ->willReturn($response)
        ;
        $response
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['ACK' => 'Failure'])
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

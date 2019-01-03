<?php

namespace Beelab\PaypalBundle\Tests\Entity;

use Beelab\PaypalBundle\Test\TransactionStub as Transaction;
use DateTime;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function testGetSetStart(): void
    {
        $transaction = new Transaction();
        $date = new DateTime();
        $transaction->setStart($date);

        $this->assertEquals($date, $transaction->getStart());
    }

    public function testGetSetEnd(): void
    {
        $transaction = new Transaction();
        $date = new DateTime();
        $transaction->setEnd($date);

        $this->assertEquals($date, $transaction->getEnd());
    }

    public function testGetSetStatus(): void
    {
        $transaction = new Transaction();
        $transaction->setStatus(1);

        $this->assertEquals(1, $transaction->getStatus());
    }

    public function testStatusLabel(): void
    {
        $transaction = new Transaction();

        $this->assertEquals('started', $transaction->getStatusLabel());
    }

    public function testStatusLabelInvalid(): void
    {
        $transaction = new Transaction();
        $transaction->setStatus(99);

        $this->assertEquals('invalid', $transaction->getStatusLabel());
    }

    public function testGetSetToken(): void
    {
        $transaction = new Transaction();
        $transaction->setToken('bar');

        $this->assertEquals('bar', $transaction->getToken());
    }

    public function testCancel(): void
    {
        $transaction = new Transaction();
        $transaction->cancel();

        $this->assertNull($transaction->getResponse());
    }

    public function testIsOk(): void
    {
        $transaction = new Transaction();

        $this->assertFalse($transaction->isOk());
    }
}

<?php

namespace Beelab\PaypalBundle\Tests\Entity;

use Beelab\PaypalBundle\Test\TransactionStub as Transaction;
use DateTime;
use PHPUnit_Framework_TestCase;

class TransactionTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetStart()
    {
        $transaction = new Transaction();
        $date = new DateTime();
        $transaction->setStart($date);

        $this->assertEquals($date, $transaction->getStart());
    }

    public function testGetSetEnd()
    {
        $transaction = new Transaction();
        $date = new DateTime();
        $transaction->setEnd($date);

        $this->assertEquals($date, $transaction->getEnd());
    }

    public function testGetSetStatus()
    {
        $transaction = new Transaction();
        $transaction->setStatus(1);

        $this->assertEquals(1, $transaction->getStatus());
    }

    public function testStatusLabel()
    {
        $transaction = new Transaction();

        $this->assertEquals('started', $transaction->getStatusLabel());
    }

    public function testStatusLabelInvalid()
    {
        $transaction = new Transaction();
        $transaction->setStatus(99);

        $this->assertEquals('invalid', $transaction->getStatusLabel());
    }

    public function testGetSetToken()
    {
        $transaction = new Transaction();
        $transaction->setToken('bar');

        $this->assertEquals('bar', $transaction->getToken());
    }

    public function testCancel()
    {
        $transaction = new Transaction();
        $transaction->cancel();

        $this->assertEquals(null, $transaction->getResponse());
    }
}

<?php

namespace Beelab\PaypalBundle\Test;

use Beelab\PaypalBundle\Entity\Transaction;

class TransactionStub extends Transaction
{
    public function getDescription()
    {
        return 'Dummy description';
    }
}

<?php

namespace Beelab\PaypalBundle\Test;

use Beelab\PaypalBundle\Entity\Transaction;

class TransactionStub extends Transaction
{
    public function getDescription()
    {
        return 'Dummy description';
    }

    public function getItems()
    {
        return array(array('name' => 'an item', 'price' => '1.00', 'quantity' => 2));
    }
}

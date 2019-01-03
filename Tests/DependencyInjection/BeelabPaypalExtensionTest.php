<?php

namespace Beelab\PaypalBundle\Tests\DependencyInjection;

use Beelab\PaypalBundle\DependencyInjection\BeelabPaypalExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class BeelabPaypalExtensionTest extends TestCase
{
    public function testLoadSetParameters(): void
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $parameterBag = $this->getMockBuilder(ParameterBag::class)->disableOriginalConstructor()->getMock();

        $parameterBag->expects($this->any())->method('add');
        $container->expects($this->any())->method('getParameterBag')->will($this->returnValue($parameterBag));

        $extension = new BeelabPaypalExtension();
        $configs = [
            ['username' => 'a'],
            ['password' => 'b'],
            ['signature' => 'c'],
            ['return_route' => 'pippo'],
            ['cancel_route' => 'pluto'],
        ];
        $extension->load($configs, $container);
        $this->assertTrue(true);
    }
}

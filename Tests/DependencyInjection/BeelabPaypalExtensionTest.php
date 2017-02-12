<?php

namespace Beelab\PaypalBundle\Tests\DependencyInjection;

use Beelab\PaypalBundle\DependencyInjection\BeelabPaypalExtension;
use PHPUnit\Framework\TestCase;

class BeelabPaypalExtensionTest extends TestCase
{
    public function testLoadSetParameters()
    {
        $container = $this
            ->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $parameterBag = $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\ParameterBag\\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock()
        ;

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

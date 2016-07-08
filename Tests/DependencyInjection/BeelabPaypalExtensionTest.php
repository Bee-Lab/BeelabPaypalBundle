<?php

namespace Beelab\PaypalBundle\Tests\DependencyInjection;

use Beelab\PaypalBundle\DependencyInjection\BeelabPaypalExtension;
use PHPUnit_Framework_TestCase;

class BeelabPaypalExtensionTest extends PHPUnit_Framework_TestCase
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
    }
}

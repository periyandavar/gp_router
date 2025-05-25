<?php

use PHPUnit\Framework\TestCase;
use Router\Route;
use Router\Wrapper;


class WrapperTest extends TestCase
{
    public function testGetSetRoutes()
    {
        $routes = [
            new Route('/', 'home/page')
        ];
        $wrapper = new Wrapper();
        $wrapper->setRoutes($routes);
        $this->assertEquals($routes, $wrapper->getRoutes());
    }
}
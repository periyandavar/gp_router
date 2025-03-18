<?php

use PHPUnit\Framework\TestCase;
use Router\Request\Request;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        // Mock global variables
        $_POST = ['key1' => 'value1'];
        $_GET = ['key2' => 'value2'];
        $_FILES = ['file1' => ['name' => 'test.txt']];
        $_SERVER = ['REQUEST_METHOD' => 'GET'];
        $_COOKIE = ['cookie1' => 'value3'];
        $_SESSION = ['session1' => 'value4'];
    }

    public function testInit()
    {
        // Arrange
        $urlParams = ['param1' => 'value5'];

        // Act
        $request = new Request($urlParams);

        // Assert
        $this->assertEquals(['key1' => 'value1'], $request->post());
        $this->assertEquals(['key2' => 'value2'], $request->get());
        $this->assertEquals(['param1' => 'value5'], $request->urlParam());
        $this->assertEquals('GET', $request->server('REQUEST_METHOD'));
        $this->assertEquals('value3', $request->cookie('cookie1'));
        $this->assertEquals(null, $request->session('session1'));
    }
}

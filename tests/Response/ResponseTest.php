<?php

use PHPUnit\Framework\TestCase;
use Router\Response\Response;

class ResponseTest extends TestCase
{
    public function testSetStatusCode()
    {
        // Arrange
        $response = new Response();
        $statusCode = 404;

        // Act
        $response->setStatusCode($statusCode);

        // Assert
        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}

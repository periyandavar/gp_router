<?php

use PHPUnit\Framework\TestCase;
use Router\Response\Response;

class ResponseTest extends TestCase
{
    public function testSetAndGetStatusCode()
    {
        $response = new Response();
        $response->setStatusCode(404);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testSetAndGetHeaders()
    {
        $response = new Response();
        $headers = ['X-Test' => 'value', 'Content-Type' => 'text/plain'];
        $response->setHeaders($headers);
        $this->assertEquals($headers, $response->getHeaders());
    }

    public function testSetAndGetHeader()
    {
        $response = new Response();
        $response->setHeader('X-Test', 'value');
        $this->assertEquals(['X-Test' => 'value'], $response->getHeaders());
    }

    public function testSetAndGetBody()
    {
        $response = new Response();
        $response->setBody('Hello');
        $this->assertEquals('Hello', $response->getBody());
    }

    public function testSetAndGetType()
    {
        $response = new Response();
        $response->setType(Response::TYPE_JSON);
        $this->assertEquals(Response::TYPE_JSON, $response->getType());
    }

    public function testGenerateJson()
    {
        $response = new Response();
        $data = ['foo' => 'bar'];
        $response->setType(Response::TYPE_JSON)->setBody($data);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    public function testGenerateHtml()
    {
        $response = new Response();
        $html = '<h1>Hello</h1>';
        $response->setType(Response::TYPE_HTML)->setBody($html);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('text/html', $headers['Content-Type']);
    }

    public function testGenerateText()
    {
        $response = new Response();
        $text = 'plain text';
        $response->setType(Response::TYPE_TEXT)->setBody($text);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('text/plain', $headers['Content-Type']);
    }

    public function testArrayToXml()
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];
        $xml = Response::arrayToXml($data, '<root/>');
        $this->assertStringContainsString('<foo>bar</foo>', $xml);
        $this->assertStringContainsString('<baz>qux</baz>', $xml);
    }

    public function testArrayToCsv()
    {
        $data = [
            ['foo' => 'bar', 'baz' => 'qux'],
            ['foo' => 'hello', 'baz' => 'world'],
        ];
        $csv = Response::arrayToCsv($data);
        $this->assertStringContainsString('foo,baz', $csv);
        $this->assertStringContainsString('bar,qux', $csv);
        $this->assertStringContainsString('hello,world', $csv);
    }

    public function testArrayToYaml()
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];
        $yaml = Response::arrayToYaml($data);
        $this->assertStringContainsString('foo: bar', $yaml);
        $this->assertStringContainsString('baz: qux', $yaml);
    }
}
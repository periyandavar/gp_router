<?php

use PHPUnit\Framework\TestCase;
use Router\Request\Request;
use Router\Response\Response;

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
        $urlParams = ['param1' => ['value5']];

        // Act
        $request = new Request($urlParams);

        // Assert
        $this->assertEquals(['key1' => 'value1'], $request->post());
        $this->assertEquals('value1', $request->post('key1'));
        $this->assertEquals(['key2' => 'value2'], $request->get());
        $this->assertEquals('value2', $request->get('key2'));
        $request->setEscape(true);
        $this->assertEquals($_POST, $request->post());
        $this->assertEquals(['param1' => ['value5']], $request->urlParam());
        $this->assertEquals(['value5'], $request->urlParam('param1'));
        $this->assertEquals('GET', $request->server('REQUEST_METHOD'));
        $this->assertEquals('value3', $request->cookie('cookie1'));
        $this->assertEquals(null, $request->session('session1'));
        $this->assertNull($request->getData('aa', 'aa'));
        $this->assertNull($request->data('test'));
        $this->assertEmpty($request->header());
        $this->assertNull($request->header('v'));
    }

    public function testGetNonExistentValues()
    {
        $request = new Request([]);
        $this->assertNull($request->post('not_exist'));
        $this->assertNull($request->get('not_exist'));
        $this->assertNull($request->urlParam('not_exist'));
        $this->assertNull($request->server('not_exist'));
        $this->assertNull($request->cookie('not_exist'));
        $this->assertNull($request->session('not_exist'));
    }

    /**
 * @runInSeparateProcess
 */
    public function testSetSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $request = new Request([]);
        $request->setSession('foo', 'bar');
        $this->assertEquals('bar', $_SESSION['foo']);
        $this->assertEquals('bar', $request->session('foo'));
    }

    public function testHasHeader()
    {
        $request = new Request([]);
        // Simulate headers
        $reflection = new ReflectionClass($request);
        $prop = $reflection->getProperty('headers');
        $prop->setAccessible(true);
        $prop->setValue($request, ['X-Test-Header' => 'test-value']);

        $this->assertTrue($request->hasHeader('X-Test-Header'));
        $this->assertFalse($request->hasHeader('X-Not-Set'));
    }

    public function testEscapeRecursivelyEscapesAllValues()
    {
        $request = new Request([]);
        $reflection = new ReflectionClass($request);
        $escapeMethod = $reflection->getMethod('escape');
        $escapeMethod->setAccessible(true);

        $input = [
            'key1' => '<script>alert(1)</script>',
            'key2' => [
                'subkey' => '<b>bold</b>',
                'arr' => ['<i>italic</i>', '<a href="#">link</a>'],
            ],
            'key3' => 'normal',
        ];

        $escaped = $escapeMethod->invoke($request, $input);

        $this->assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', $escaped['key1']);
        $this->assertEquals('&lt;b&gt;bold&lt;/b&gt;', $escaped['key2']['subkey']);
        $this->assertEquals('&lt;i&gt;italic&lt;/i&gt;', $escaped['key2']['arr'][0]);
        $this->assertEquals('&lt;a href=&quot;#&quot;&gt;link&lt;/a&gt;', $escaped['key2']['arr'][1]);
        $this->assertEquals('normal', $escaped['key3']);
    }

    public function testArrayToXml()
    {
        $data = ['foo' => 'bar', 'baz' => ['qux' => 'quux']];
        $xml = Response::arrayToXml($data, '<root/>');
        $this->assertStringContainsString('<foo>bar</foo>', $xml);
        $this->assertStringContainsString('<baz>', $xml);
        $this->assertStringContainsString('<qux>quux</qux>', $xml);
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testSendOutputsContent()
    {
        $response = new Response();
        $data = ['data' => 'data'];
        $response->setType(Response::TYPE_TEXT)->setBody($data);
        ob_start();
        $response->send();
        $output = ob_get_clean();
        $this->assertStringContainsString(json_encode($data), $output);
    }

    public function testSendOutputsNoContent()
    {
        $response = new Response();
        $response->setType(Response::TYPE_TEXT)->setBody('data');
        ob_start();
        $response->send();
        $output = ob_get_clean();
        $this->assertStringContainsString('', $output);
    }

    public function testArrayToCsvWithEmptyData()
    {
        $this->assertEmpty(Response::arrayToCsv(''));
    }

    public function testGenerateXml()
    {
        $response = new Response();
        $data = ['foo' => 'bar', 'baz' => ['qux' => 'quux']];
        $response->setType(Response::TYPE_XML)->setBody($data);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('application/xml', $headers['Content-Type']);
        $content = $this->getPrivateProperty($response, 'content');
        $this->assertStringContainsString('<foo>bar</foo>', $content);
        $this->assertStringContainsString('<baz>', $content);
        $this->assertStringContainsString('<qux>quux</qux>', $content);
    }

    public function testGenerateText()
    {
        $response = new Response();
        $text = 'plain text';
        $response->setType(Response::TYPE_TEXT)->setBody($text);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('text/plain', $headers['Content-Type']);
        $this->assertEquals($text, $this->getPrivateProperty($response, 'content'));
    }

    public function testGenerateCsv()
    {
        $response = new Response();
        $data = [
            ['foo' => 'bar', 'baz' => 'qux'],
            ['foo' => 'hello', 'baz' => 'world'],
        ];
        $response->setType(Response::TYPE_CSV)->setBody($data);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('text/csv', $headers['Content-Type']);
        $content = $this->getPrivateProperty($response, 'content');
        $this->assertStringContainsString('foo,baz', $content);
        $this->assertStringContainsString('bar,qux', $content);
        $this->assertStringContainsString('hello,world', $content);
    }

    public function testGenerateYaml()
    {
        $response = new Response();
        $data = ['foo' => 'bar', 'baz' => 'qux'];
        $response->setType(Response::TYPE_YAML)->setBody($data);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('application/x-yaml', $headers['Content-Type']);
        $content = $this->getPrivateProperty($response, 'content');
        $this->assertStringContainsString('foo: bar', $content);
        $this->assertStringContainsString('baz: qux', $content);
    }

    public function testGenerateBinary()
    {
        $response = new Response();
        $data = random_bytes(10);
        $response->setType(Response::TYPE_BINARY)->setBody($data);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('application/octet-stream', $headers['Content-Type']);
        $this->assertEquals($data, $this->getPrivateProperty($response, 'content'));
    }

    public function testGenerateImage()
    {
        $response = new Response();
        $file = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($file, 'imagecontent');
        $response->setType(Response::TYPE_IMAGE)->setBody($file);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('image/jpeg', $headers['Content-Type']);
        $this->assertEquals('imagecontent', $this->getPrivateProperty($response, 'content'));
        unlink($file);
    }

    public function testGenerateAudio()
    {
        $response = new Response();
        $file = tempnam(sys_get_temp_dir(), 'aud');
        file_put_contents($file, 'audiocontent');
        $response->setType(Response::TYPE_AUDIO)->setBody($file);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('audio/mpeg', $headers['Content-Type']);
        $this->assertEquals('audiocontent', $this->getPrivateProperty($response, 'content'));
        unlink($file);
    }

    public function testGenerateVideo()
    {
        $response = new Response();
        $file = tempnam(sys_get_temp_dir(), 'vid');
        file_put_contents($file, 'videocontent');
        $response->setType(Response::TYPE_VIDEO)->setBody($file);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('video/mp4', $headers['Content-Type']);
        $this->assertEquals('videocontent', $this->getPrivateProperty($response, 'content'));
        unlink($file);
    }

    public function testGenerateStream()
    {
        $response = new Response();
        $data = 'stream content';
        $response->setType(Response::TYPE_STREAM)->setBody($data);
        $response->generate();
        $headers = $response->getHeaders();
        $this->assertEquals('application/octet-stream', $headers['Content-Type']);
        $this->assertEquals($data, $response->getBody());
    }

    public function testGenerateUnsupportedTypeThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        $response = new Response();
        $response->setType('unsupported')->setBody('data');
        $response->generate();
    }

    private function getPrivateProperty($object, $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}

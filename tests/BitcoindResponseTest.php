<?php

use Denpa\Bitcoin;
use Denpa\Bitcoin\Exceptions;
use GuzzleHttp\Psr7\BufferStream;

class BitcoindResponseTest extends TestCase
{
    /**
     * Set up test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->guzzleResponse = $this->getBlockResponse();
        $this->response = Bitcoin\BitcoindResponse::createFrom($this->guzzleResponse);
        $this->response = $this->response->withHeader('X-Test', 'test');
    }

    /**
     * Test response with result.
     *
     * @return void
     */
    public function testResult()
    {
        $this->assertTrue($this->response->hasResult());

        $this->assertEquals(
            null, $this->response->error()
        );
        $this->assertEquals(
            self::$getBlockResponse,
            $this->response->result()
        );
    }

    /**
     * Test response without result.
     *
     * @return void
     */
    public function testNoResult()
    {
        $response = Bitcoin\BitcoindResponse::createFrom(
            $this->rawTransactionError()
        );

        $this->assertFalse($response->hasResult());
    }

    /**
     * Test getter for status code.
     *
     * @return void
     */
    public function testStatusCode()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * Test getter for reason phrase.
     *
     * @return void
     */
    public function testReasonPhrase()
    {
        $this->assertEquals('OK', $this->response->getReasonPhrase());
    }

    /**
     * Test changing status for response.
     *
     * @return void
     */
    public function testWithStatus()
    {
        $response = $this->response->withStatus(444, 'test');

        $this->assertEquals(444, $response->getStatusCode());
        $this->assertEquals('test', $response->getReasonPhrase());
    }

    /**
     * Test creating BitcoindResponse from Guzzle.
     *
     * @return void
     */
    public function testCreateFrom()
    {
        $guzzleResponse = $this->getBlockResponse();

        $response = Bitcoin\BitcoindResponse::createFrom($guzzleResponse);

        $this->assertInstanceOf(Bitcoin\BitcoindResponse::class, $response);
        $this->assertEquals($response->response(), $guzzleResponse);
    }

    /**
     * Test error in response.
     *
     * @return void
     */
    public function testError()
    {
        $response = Bitcoin\BitcoindResponse::createFrom(
            $this->rawTransactionError()
        );

        $this->assertTrue($response->hasError());

        $this->assertEquals(
            null, $response->result()
        );
        $this->assertEquals(
            self::$rawTransactionError,
            $response->error()
        );
    }

    /**
     * Test no error in response.
     *
     * @return void
     */
    public function testNoError()
    {
        $this->assertFalse($this->response->hasError());
    }

    /**
     * Test getting values through ArrayAccess.
     *
     * @return void
     */
    public function testArrayAccessGet()
    {
        $this->assertEquals(
            self::$getBlockResponse['hash'],
            $this->response['hash']
        );
    }

    /**
     * Test setting values through ArrayAccess.
     *
     * @return void
     */
    public function testArrayAccessSet()
    {
        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage('Cannot modify readonly object');
        $this->response['hash'] = 'test';
    }

    /**
     * Test unsetting values through ArrayAccess.
     *
     * @return void
     */
    public function testArrayAccessUnset()
    {
        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage('Cannot modify readonly object');
        unset($this->response['hash']);
    }

    /**
     * Test checking value through ArrayAccess.
     *
     * @return void
     */
    public function testArrayAccessIsset()
    {
        $this->assertTrue(isset($this->response['hash']));
        $this->assertFalse(isset($this->response['cookie']));
    }

    /**
     * Test setting key through invokation.
     *
     * @return void
     */
    public function testInvoke()
    {
        $response = $this->response;

        $this->assertEquals(
            self::$getBlockResponse['hash'],
            $response('hash')->get()
        );
    }

    /**
     * Test getting value by key.
     *
     * @return void
     */
    public function testGet()
    {
        $this->assertEquals(
            self::$getBlockResponse['hash'],
            $this->response->get('hash')
        );

        $this->assertEquals(
            self::$getBlockResponse['tx'][0],
            $this->response->get('tx.0')
        );
    }

    /**
     * Test getting first element of array.
     *
     * @return void
     */
    public function testFirst()
    {
        $this->assertEquals(
            self::$getBlockResponse['tx'][0],
            $this->response->key('tx')->first()
        );

        $this->assertEquals(
            self::$getBlockResponse['tx'][0],
            $this->response->first('tx')
        );

        $this->assertEquals(
            reset(self::$getBlockResponse),
            $this->response->first()
        );

        $this->assertEquals(
            self::$getBlockResponse['hash'],
            $this->response->key('hash')->first()
        );
    }

    /**
     * Test getting last element of array.
     *
     * @return void
     */
    public function testLast()
    {
        $this->assertEquals(
            self::$getBlockResponse['tx'][3],
            $this->response->key('tx')->last()
        );

        $this->assertEquals(
            self::$getBlockResponse['tx'][3],
            $this->response->last('tx')
        );

        $this->assertEquals(
            end(self::$getBlockResponse),
            $this->response->last()
        );

        $this->assertEquals(
            self::$getBlockResponse['hash'],
            $this->response->key('hash')->last()
        );
    }

    /**
     * Test method used to check if array has key.
     *
     * @return void
     */
    public function testHas()
    {
        $response = $this->response;

        $this->assertTrue($response->has('hash'));
        $this->assertTrue($response->has('tx.0'));
        $this->assertTrue($response('tx')->has(0));
        $this->assertFalse($response->has('tx.3'));
        $this->assertFalse($response->has('cookies'));
        $this->assertFalse($response->has('height'));
    }

    /**
     * Test method used to check if array has key pointing to non-null value.
     *
     * @return void
     */
    public function testExists()
    {
        $this->assertTrue($this->response->exists('hash'));
        $this->assertTrue($this->response->exists('tx.0'));
        $this->assertTrue($this->response->exists('tx.3'));
        $this->assertTrue($this->response->exists('height'));
        $this->assertFalse($this->response->exists('cookies'));
    }

    /**
     * Test method used to check if array has value.
     *
     * @return void
     */
    public function testContains()
    {
        $this->assertTrue($this->response->contains('00000000839a8e6886ab5951d76f411475428afc90947ee320161bbf18eb6048'));
        $this->assertTrue($this->response->contains('bedb088c480e5f7424a958350f2389c839d17e27dae13643632159b9e7c05482', 'tx'));
        $this->assertFalse($this->response->contains('cookies'));
    }

    /**
     * Test method used to check if array has value on non-array.
     *
     * @return void
     */
    public function testContainsOnNonArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('method contains() should be called on array');
        $this->response->key('version')->contains('test');
    }

    /**
     * Test getting array keys.
     *
     * @return void
     */
    public function testKeys()
    {
        $this->assertEquals(
            array_keys(self::$getBlockResponse),
            $this->response->keys()
        );
        $this->assertEquals(
            array_keys(self::$getBlockResponse['tx']),
            $this->response->keys('tx')
        );
    }

    /**
     * Test getting array keys on non array.
     *
     * @return void
     */
    public function testKeysOnNonArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('method keys() should be called on array');
        $this->response->keys('version');
    }

    /**
     * Test getting array values.
     *
     * @return void
     */
    public function testValues()
    {
        $this->assertEquals(
            array_values(self::$getBlockResponse),
            $this->response->values()
        );
        $this->assertEquals(
            array_values(self::$getBlockResponse['tx']),
            $this->response->values('tx')
        );
    }

    /**
     * Test getting array values on non array.
     *
     * @return void
     */
    public function testValuesOnNonArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('method values() should be called on array');
        $this->response->values('version');
    }

    /**
     * Test getting random elements from array.
     *
     * @return void
     */
    public function testRandom()
    {
        $tx1 = $this->response->random(1, 'tx');
        $tx2 = $this->response->random(1, 'tx');
        $this->assertContains($tx1, self::$getBlockResponse['tx']);
        $this->assertContains($tx2, self::$getBlockResponse['tx']);

        $random = $this->response->random();
        $this->assertContains($random, self::$getBlockResponse);

        $random2 = $this->response->random(2);
        $this->assertCount(2, $random2);
        $this->assertArraySubset($random2, self::$getBlockResponse);

        $random3 = $this->response->random(1, 'merkleroot');
        $this->assertEquals(self::$getBlockResponse['merkleroot'], $random3);

        $random4 = $this->response->random(6, 'tx');
        $this->assertEquals(self::$getBlockResponse['tx'], $random4);

        $response = $this->response;
        $random5 = $response('tx')->random(6);
        $this->assertEquals(self::$getBlockResponse['tx'], $random5);
    }

    /**
     * Test counting number of elements in array.
     *
     * @return void
     */
    public function testCount()
    {
        $this->assertEquals(
            count(self::$getBlockResponse),
            count($this->response)
        );

        $this->assertEquals(
            count(self::$getBlockResponse),
            $this->response->count()
        );

        $this->assertEquals(
            4,
            $this->response->count('tx')
        );

        $this->assertEquals(
            1,
            $this->response->count('hash')
        );

        $this->assertEquals(
            1,
            $this->response->key('hash')->count()
        );

        $this->assertEquals(
            0,
            $this->response->count('nonexistent')
        );
    }

    /**
     * Test getting protocol version.
     *
     * @return void
     */
    public function testProtocolVersion()
    {
        $response = $this->response->withProtocolVersion(1.0);
        $protocolVersion = $response->getProtocolVersion();

        $this->assertEquals('1.0', $protocolVersion);
    }

    /**
     * Test setting response header.
     *
     * @return void
     */
    public function testWithHeader()
    {
        $response = $this->response->withHeader('X-Test', 'bar');

        $this->assertTrue($response->hasHeader('X-Test'));
        $this->assertEquals('bar', $response->getHeaderLine('X-Test'));
    }

    /**
     * Test adding header to response.
     *
     * @return void
     */
    public function testWithAddedHeader()
    {
        $response = $this->response->withAddedHeader('X-Bar', 'baz');

        $this->assertTrue($response->hasHeader('X-Test'));
        $this->assertTrue($response->hasHeader('X-Bar'));
    }

    /**
     * Test removing headers from response.
     *
     * @return void
     */
    public function testWithoutHeader()
    {
        $response = $this->response->withoutHeader('X-Test');

        $this->assertFalse($response->hasHeader('X-Test'));
    }

    /**
     * Test getting response header.
     *
     * @return void
     */
    public function testGetHeader()
    {
        $response = $this->response->withHeader('X-Bar', 'baz');

        $expected = [
            'X-Test' => ['test'],
            'X-Bar'  => ['baz'],
        ];

        $this->assertEquals($expected, $response->getHeaders());

        foreach ($expected as $name => $value) {
            $this->assertEquals($value, $response->getHeader($name));
        }
    }

    /**
     * Test setting response body.
     *
     * @return void
     */
    public function testBody()
    {
        $stream = new BufferStream();
        $stream->write('cookies');

        $response = $this->response->withBody($stream);

        $this->assertEquals('cookies', $response->getBody()->__toString());
    }

    /**
     * Test serialization.
     *
     * @return void
     */
    public function testSerialize()
    {
        $serializedContainer = serialize($this->response->getContainer());
        $class = Bitcoin\BitcoindResponse::class;

        $serialized = sprintf(
            'C:%u:"%s":%u:{%s}',
            strlen($class),
            $class,
            strlen($serializedContainer),
            $serializedContainer
        );

        $this->assertEquals(
            $serialized,
            serialize($this->response)
        );
    }

    /**
     * Test unserialization.
     *
     * @return void
     */
    public function testUnserialize()
    {
        $container = $this->response->getContainer();

        $this->assertEquals(
            $container,
            unserialize(serialize($this->response))->getContainer()
        );
    }

    /**
     * Test serialization to JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $this->assertEquals(
            json_encode($this->response->getContainer()),
            json_encode($this->response)
        );
    }

    /**
     * Test sum of array values.
     *
     * @return void
     */
    public function testSum()
    {
        $response = $this->response;

        $this->assertEquals(7, $response('test1.*.*')->sum('amount'));
        $this->assertEquals(7, $response('test1.*.*.amount')->sum());
        $this->assertEquals(7, $response->sum('test1.*.*.amount'));
    }

    /**
     * Test array flattening.
     *
     * @return void
     */
    public function testFlatten()
    {
        $response = $this->response;

        $this->assertEquals([3, 4], $response('test1.*.*')->flatten('amount'));
        $this->assertEquals([3, 4], $response('test1.*.*.amount')->flatten());
        $this->assertEquals([3, 4], $response->flatten('test1.*.*.amount'));
    }
}

<?php

namespace V8\Tests\Feature\Http;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use V8\Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_homepage_returns_200()
    {
        $client = new HttpBrowser(HttpClient::create());

        $client->request('GET', 'http://127.0.0.1:8000');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}

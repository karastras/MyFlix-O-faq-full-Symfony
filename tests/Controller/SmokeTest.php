<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/show/list');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/category/list');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/character/list');
        $this->assertResponseIsSuccessful();
    }
}

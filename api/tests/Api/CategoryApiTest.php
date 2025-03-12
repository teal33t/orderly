<?php

namespace App\Tests;


use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

use Symfony\Component\HttpFoundation\Response;

class CategoryApiTest extends ApiTestCase
{
    public function testGetCategoriesAsAdmin(): void
    {
        
        $client = static::createClient();
        $client->request('POST', '/auth', [
            'json' => [
                'email' => 'admin@admin.com',
                'password' => 'admin',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $token = $client->getResponse()->toArray()['token'];

        // Use the token to access the categories endpoint
        $client->request('GET', '/api/categories', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGetCategoriesAsNonAdmin(): void
    {
        
        $client = static::createClient();
        $client->request('POST', '/auth', [
            'json' => [
                'email' => 'guest@guest.com',
                'password' => 'guest',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $token = $client->getResponse()->toArray()['token'];

        // Use the token to access the categories endpoint
        $client->request('GET', '/api/categories', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
} 
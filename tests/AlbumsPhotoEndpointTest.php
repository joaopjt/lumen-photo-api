<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AlbumnsPhotoEndpoint extends TestCase
{
    public $authHeaders = [
        'headers' => [
            'Authorization' => 'Bearer abc123'
        ]
    ];

    public function setUp() {
        $this->client = new Client([
            'base_uri' => 'http://localhost:3001'
        ]);
    }

    public function test_Get_all_without_login()
    {
        $res = $this->client->request('GET', '/albumns/4/photos');

        $data = json_decode($res->getBody(), true);
        $data = $data[0];

        $this->assertEquals(200, $res->getStatusCode());

        $this->assertEquals(4, $data['albumn_id']);
        $this->assertEquals(1, $data['public']);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('url', $data);
        $this->assertArrayHasKey('public', $data);
    }
}

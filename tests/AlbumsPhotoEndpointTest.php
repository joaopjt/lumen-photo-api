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

    public function test_Get_all_logged_in()
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

    public function test_Post_new_without_login()
    {
        try {
            $res = $this->client->request('POST', '/albumns/4/photos', [
    			'json' => [
    				'name' => 'TEST',
                    'url' => 'http://test.com.br/',
                    'public' => '1'
    			]
    		]);
        } catch(ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
    }

    public function test_Post_with_validation_errors()
    {
        try {
            $res = $this->client->request('POST', '/albumns/4/photos', array_merge($this->authHeaders, [
                'json' => [
                    'name' => '',
                    'url' => '123'
                ]
            ]));
        } catch(ClientException $e) {
            $res = $e->getResponse();
            $body = json_decode($res->getBody(), true);

            $this->assertEquals(422, $res->getStatusCode());

            $this->assertArrayHasKey('name', $body);
            $this->assertArrayHasKey('url', $body);
        }
    }

    public function test_Post_a_new_one()
    {
        $res = $this->client->request('POST', '/albumns/4/photos', array_merge($this->authHeaders, [
            'json' => [
                'name' => 'TEST PHPUNIT',
                'url' => 'http://google.com/'
            ]
        ]));

        $body = json_decode($res->getBody(), true);

        $this->assertEquals(201, $res->getStatusCode());

        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('url', $body);
        $this->assertArrayHasKey('public', $body);

        $this->assertEquals(4, $body['albumn_id']);
        $this->assertEquals(1, $body['owner_id']);
    }

    public function test_Edit_the_new_one()
    {
        $records = $this->client->request('GET', '/albumns/4/photos?sort=-created_at&limit=1', $this->authHeaders);

        $lastOne = json_decode($records->getBody(), true);
        $lastOne = $lastOne[0];

        $res = $this->client->request('PUT', 'albumns/4/photos/'.$lastOne['id'], array_merge($this->authHeaders, [
            'json' => [
                'name' => 'TEST PHPUNIT EDITED',
                'public' => true
            ]
        ]));

        $this->assertEquals(200, $res->getStatusCode());
    }

    public function test_Remove_the_new_one()
    {
        $records = $this->client->request('GET', '/albumns/4/photos?sort=-created_at&limit=1', $this->authHeaders);

        $lastOne = json_decode($records->getBody(), true);
        $lastOne = $lastOne[0];

        $res = $this->client->request('DELETE', 'albumns/4/photos/'.$lastOne['id'], $this->authHeaders);

        $this->assertEquals(204, $res->getStatusCode());
    }
}

<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AlbumnsEndpoint extends TestCase
{
    public $authHeaders = [
        'headers' => [
            'Authorization' => 'Bearer abc123'
        ]
    ];
    public $albumnId = null;

    public function setUp() {
        $this->client = new Client([
            'base_uri' => 'http://localhost:3001'
        ]);
    }

    public function test_Get_all_without_login()
    {
        $res = $this->client->request('GET', '/albumns');

        $data = json_decode($res->getBody(), true);
        $data = $data[0];

        $this->assertEquals(200, $res->getStatusCode());

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('public', $data);
    }

    public function test_Get_all_when_logged_in()
    {
        $res = $this->client->request('GET', '/albumns', $this->authHeaders);

        $this->assertEquals(200, $res->getStatusCode());

        $data = json_decode($res->getBody(), true);
        $data = $data[count($data) - 1];

        $this->assertArrayHasKey('owner_id', $data);
        $this->assertEquals(1, $data['owner_id']);
        $this->assertEquals(false, $data['public']);
    }

    public function test_Post_a_new_albumn_unlogged()
    {
        /////////////////////////////////////////////
        // In this first case, we simulate         //
        // a user not logged in trying to post     //
        // a new albumn. We should verify if       //
        // the errors are being returned correctly //
        /////////////////////////////////////////////

        try {
            $res = $this->client->request('POST', '/albumns');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
        }
    }

    public function test_Post_a_new_albumn_logged_with_unvalid_payload() {
        ////////////////////////////////////
        // In this second case, we should //
        // get a validation error         //
        ////////////////////////////////////

        try {
            $res = $this->client->request('POST', '/albumns', array_merge($this->authHeaders, [
                'json' => [
                    'name' => ''
                ]
            ]));
        } catch (ClientException $e) {
            $this->assertEquals(422, $e->getResponse()->getStatusCode());
        }
    }

    public function test_Post_a_new_albumn_logged_with_valid_payload() {
        ///////////////////////////////
        // In this one, we should    //
        // get a successful body     //
        // with the new added albumn //
        ///////////////////////////////

        $res = $this->client->request('POST', '/albumns', array_merge($this->authHeaders, [
            'json' => [
                'name' => 'PHPUnit'
            ]
        ]));

        $this->assertEquals(201, $res->getStatusCode());

        $data = json_decode($res->getBody(), true);

        $this->albumnId = $data['id'];
    }

    public function test_Delete_the_added_albumn() {
        $endpoint = "/albumns/{$this->albumnId}";
        $res = $this->client->request('DELETE', $endpoint, $this->authHeaders);

        $this->assertEquals(204, $res->getStatusCode());
    }
}

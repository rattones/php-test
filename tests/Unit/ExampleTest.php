<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function test_get_list_of_matches()
    {
        $response= $this->call('GET', 'api/match');

        foreach(json_decode($response->getContent()) as $match) {
            $this->assertArrayHasKey('id', (array)$match);
        }
    }

    /**
     * @test
     */
    public function test_add_new_match()
    {
        $matches= $this->call('GET', 'api/match');
        $matches= json_decode($matches->getContent());
        $response= $this->call('POST', 'api/match');
        $response= json_decode($response->getContent());

        $this->assertNotEquals(count($matches), count($response));

    }

    /**
     * @test
     */
    public function test_remove_a_match()
    {
        $matches= $this->call('GET', 'api/match');
        $matches= json_decode($matches->getContent());
        foreach($matches as $match) {
            $id= $match->id;
        }
        $response= $this->call('delete', "api/match/{$id}");
        $response= json_decode($response->getContent());

        $this->assertNotEquals(count($matches), count($response));
    }
    
    /**
     * @test
     */
    public function test_making_a_move_in_a_match()
    {
        // reset board for testing move
        $this->call('PUT', 'api/reset/1');

        $pos= rand(0, 8);
        $match= $this->call('PUT', 'api/match/1', ['position'=>$pos]);
        $board= json_decode($match->getContent())->board;

        $this->assertNotEquals($board[$pos], 0);
    }

    /**
     * @test
     */
    public function test_winner_for_a_match()
    {
        // fake move for a win match

        $match= $this->call('GET', 'api/match/1');
        $match= json_decode($match->getContent());
        
        while($match->winner == 0) {
            $pos= rand(0, 8);
            $match= $this->call('PUT', 'api/match/1', ['position'=>$pos]);
            $match= json_decode($match->getContent());
        }

        $this->assertNotEquals($match->winner, 0);
    }
}

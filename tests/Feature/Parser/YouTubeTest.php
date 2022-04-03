<?php

namespace Tests\Feature\Parser;

use Tests\TestCase;

class YouTubeTest extends TestCase
{
    public function testBasic()
    {
        $response = $this->get( '/' );

        $response->assertStatus( 200 );
    }
}
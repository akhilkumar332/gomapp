<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user_in_sqlite()
    {
        $user = DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_can_create_a_user_in_mysql()
    {
        // Set the DB_CONNECTION to mysql in the .env file before running this test
        $user = DB::table('users')->insert([
            'name' => 'Test User MySQL',
            'email' => 'test_mysql@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test_mysql@example.com',
        ]);
    }
}

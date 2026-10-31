<?php

namespace Tests\Feature;

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_health_endpoint_returns_ok_and_version(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertExactJson([
                'status' => 'ok',
                'version' => HealthController::VERSION,
            ]);
    }

    public function test_notes_validation_fails_on_empty_request(): void
    {
        $response = $this->postJson('/notes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    public function test_notes_validation_fails_when_text_exceeds_500_chars(): void
    {
        $response = $this->postJson('/notes', [
            'text' => str_repeat('a', 501),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    public function test_notes_returns_503_when_database_is_unavailable(): void
    {
        // Force an invalid host for the connection to trigger unavailable database
        config([
            'database.connections.pgsql.host' => '192.0.2.1', // Non-routable IP
            'database.connections.pgsql.options' => [\PDO::ATTR_TIMEOUT => 1],
        ]);
        DB::purge('pgsql');

        $response = $this->getJson('/notes');
        $response->assertStatus(503)
            ->assertExactJson([
                'error' => 'database_unavailable',
            ]);

        $postResponse = $this->postJson('/notes', ['text' => 'hello']);
        $postResponse->assertStatus(503)
            ->assertExactJson([
                'error' => 'database_unavailable',
            ]);
    }

    public function test_stats_returns_503_when_redis_is_unavailable(): void
    {
        $response = $this->getJson('/stats');

        $response->assertStatus(503)
            ->assertExactJson([
                'error' => 'redis_unavailable',
            ]);
    }

    public function test_crash_returns_404_when_disabled(): void
    {
        config(['app_extra.enable_crash' => '0']);

        $response = $this->getJson('/crash');

        $response->assertStatus(404)
            ->assertExactJson([
                'error' => 'not_found',
            ]);
    }
}

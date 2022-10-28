<?php

namespace Tests\Feature\Web;

use App\Traits\Tests\HealthCheck;
use Illuminate\Support\Str;
use Tests\TestCase;

class HealthTest extends TestCase {

    use HealthCheck;
    private array $health_check_labels = [];

    public function setUp(): void{
        parent::setUp();
        $this->artisan('health:check');

        $this->health_check_labels = array_filter($this->health_info_labels, function($label){
            // memcached is not available for unit tests
            return !Str::contains($label, 'memcached');
        });
    }

    /**
     * A basic feature test example.
     */
    public function testHealthJsonEndpoint(){
        $response = $this->getJson('/health.json');
        $response
            ->assertStatus(200)
            ->assertJsonCount(count($this->health_check_labels), 'checkResults');
        foreach ($this->health_check_labels as $health_info_label){
            $response->assertJsonFragment(['label'=>$health_info_label]);
        }
    }

}

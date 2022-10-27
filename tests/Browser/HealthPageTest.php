<?php

namespace Tests\Browser;

use App\Traits\Tests\HealthCheck;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HealthPageTest extends DuskTestCase {

    use HealthCheck;

    private static string $SELECTOR_HEALTH_INFO_NODE = 'div.flex.items-start';
    private static string $SELECTOR_HEALTH_INFO_NODE_LABEL = 'div dd:first-child';

    public function setUp(): void {
        parent::setUp();
        $this->artisan('health:check');
    }

    public function testHealthEndpoint(){
        $this->browse(function (Browser $browser) {
            $browser->visit('/health')
                    ->assertSee('Laravel Health');

            $health_info_nodes = $browser->elements(self::$SELECTOR_HEALTH_INFO_NODE);
            $this->assertCount(count($this->health_info_labels), $health_info_nodes);
            foreach ($health_info_nodes as $health_info_node){
                $health_info_node_label = $health_info_node->findElement(WebDriverBy::cssSelector(self::$SELECTOR_HEALTH_INFO_NODE_LABEL))->getText();
                $this->assertContains($health_info_node_label, $this->health_info_labels);
            }
        });
    }

}

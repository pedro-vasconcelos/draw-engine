<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule;

class DrawTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_has_a_description(): void
    {
        $draw = Draw::find(1);
        $this->assertEquals('Between two dates', $draw->description);
    }
    
    /** @test */
    public function it_can_use_the_draw_class(): void
    {
        $schedule = PrizeDeliverySchedule::create(['draw_id' => 1, 'draw_type' => 'Fake\Draw']);
        $this->assertEquals('Fake\Draw', $schedule->draw_type);
    }
}

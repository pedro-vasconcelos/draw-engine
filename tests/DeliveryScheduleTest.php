<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use PedroVasconcelos\DrawEngine\Exceptions\InvalidDatePeriod;
use PedroVasconcelos\DrawEngine\Exceptions\InvalidPrizeQuantity;
use PedroVasconcelos\DrawEngine\PrizeDeliverySchedule;
use Carbon\Carbon;

class DeliveryScheduleTest extends TestCase
{
    /** @test
     * @throws InvalidPrizeQuantity
     * @throws InvalidDatePeriod
     */
    public function it_distribute_all_prizes_between_two_dates(): void
    {
        $prizes = 319;
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => 20,
            'prizes' => $prizes,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
        $res = $scheduler->distributePrizes(4);
        $distributed = $this->distributed = array_reduce($res, static function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals($prizes,$distributed);
    }

    /** @test */
    public function it_distribute_all_prizes_for_week(): void
    {
        $prizes = 10;
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => 10,
            'prizes' => $prizes,
            'algorithm' => 'spaced',
            'type' => 'week',
            'week' => 34,
        ]);
        $res = $scheduler->distributePrizes(0);
        $distributed = $this->distributed = array_reduce($res, static function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals($prizes,$distributed);
    }

    /** @test */
    public function it_distribute_all_prizes_for_month(): void
    {
        $prizes = 45;
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => 10,
            'prizes' => $prizes,
            'algorithm' => 'spaced',
            'type' => 'month',
            'month' => 9,
        ]);
        $res = $scheduler->distributePrizes(2);
        $distributed = $this->distributed = array_reduce($res, static function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals($prizes,$distributed);
    }
}

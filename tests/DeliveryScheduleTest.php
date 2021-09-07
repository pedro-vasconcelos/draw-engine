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
        $draw = Draw::find(1);
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'start_period' => $draw->start_period,
            'end_period' => $draw->end_period,
        ]);
        $res = $scheduler->distributePrizes(4);
        $distributed = $this->distributed = array_reduce($res, static function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals($draw->prizes,$distributed);
    }

    /** @test */
    public function it_distribute_all_prizes_for_week(): void
    {
        $draw = Draw::find(2);
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'week' => $draw->week,
        ]);
        $res = $scheduler->distributePrizes(0);
        $distributed = $this->distributed = array_reduce($res, static function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals($draw->prizes,$distributed);
    }

    /** @test */
    public function it_distribute_all_prizes_for_month(): void
    {
        $draw = Draw::find(3);
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'month' => $draw->month,
        ]);
        $res = $scheduler->distributePrizes(2);
        $distributed = $this->distributed = array_reduce($res, static function($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals($draw->prizes,$distributed);
    }
}

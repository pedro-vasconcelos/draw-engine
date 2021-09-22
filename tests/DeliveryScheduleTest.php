<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use PedroVasconcelos\DrawEngine\Exceptions\InvalidDatePeriod;
use PedroVasconcelos\DrawEngine\Exceptions\InvalidPrizeQuantity;
use PedroVasconcelos\DrawEngine\PrizeDeliverySchedule;
use Carbon\Carbon;

class DeliveryScheduleTest extends TestCase
{
    
    /** @test */
    public function it_breaks_with_wrong_prize_economics(): void
    {
        // == ARRANGE ==
        $draw = Draw::create([
            'description' => 'Draw A',
            'daily_prize_cap' => 1,
            'prizes' => 1319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
        // == ACT ==
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'start_period' => $draw->start_period,
            'end_period' => $draw->end_period,
        ]);
        // == ASSERT ==
        $this->expectException(InvalidPrizeQuantity::class);
        $scheduler->distributePrizes(4);
    }
    
    /** @test */
    public function it_breaks_without_a_start_date(): void
    {
        // == ARRANGE ==
        $draw = Draw::create([
            'description' => 'Draw A',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => null,
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
        // == ACT ==
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'start_period' => $draw->start_period,
            'end_period' => $draw->end_period,
        ]);
        $this->expectException(InvalidDatePeriod::class);
        $scheduler->distributePrizes(4);
        // == ASSERT ==
    }
    
    /** @test */
    public function it_breaks_without_an_end_date(): void
    {
        // == ARRANGE ==
        $draw = Draw::create([
            'description' => 'Draw A',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => Carbon::createSafe(2021, 11, 30),
            'end_period' => null,
        ]);
        // == ACT ==
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'start_period' => $draw->start_period,
            'end_period' => $draw->end_period,
        ]);
        $this->expectException(InvalidDatePeriod::class);
        $scheduler->distributePrizes(4);
        // == ASSERT ==
    }
    
    
    /** @test */
    public function it_will_distribute_all_prizes_with_equal_algorithm(): void
    {
        $draw = Draw::create([
            'description' => 'Draw A',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'equal',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
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

<?php declare(strict_types=1);

namespace PedroVasconcelos\DrawEngine;

use PedroVasconcelos\DrawEngine\Exceptions\InvalidDatePeriod;
use PedroVasconcelos\DrawEngine\Exceptions\InvalidPrizeQuantity;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

class PrizeDeliverySchedule
{
    public string $type;
    public int $week;
    public int $month;
    public string $algorithm;
    public int $prizes;
    public int $dailyPrizeCap;

    public ?Carbon $start_period = null;
    public ?Carbon $end_period = null;
    private ?CarbonPeriod $period = null;
    public int $periodDays;

    public int $distributed;

    public function __construct($params)
    {
        $this->dailyPrizeCap = $params['dailyPrizeCap'];
        $this->prizes = $params['prizes'];
        $this->algorithm = $params['algorithm'];
        $this->type = $params['type'];
        if ( $this->type === 'dates' ) {
            $this->start_period = $params['start_period'];
            $this->end_period   = $params['end_period'];
        }
        if ( $this->type === 'month' ) {
            $this->month   = $params['month'];
        }
        if ( $this->type === 'week' ) {
            $this->week   = $params['week'];
        }
    }

    public function initializePeriodStartEndDate(): void
    {
        $date = Carbon::now();
        if ( $this->type === 'week' ) {
            $date->setISODate($date->year, $this->week);
            $this->start_period = $date->copy()->startOfWeek(CarbonInterface::MONDAY);
            $this->end_period   = $date->copy()->endOfWeek(CarbonInterface::SUNDAY);
        }
        if ( $this->type === 'month' ) {
            $date->setDate($date->year, $this->month, 1);
            $this->start_period = $date->copy()->startOfMonth();
            $this->end_period   = $date->copy()->endOfMonth();
        }
        $this->period     = CarbonPeriod::create($this->start_period, $this->end_period);
        $this->periodDays = $this->period->count();
    }

    /**
     * @throws InvalidPrizeQuantity
     * @throws InvalidDatePeriod
     */
    public function verifyConfig(): bool
    {
        if ( !$this->start_period ) {
            throw InvalidDatePeriod::couldNotMakePeriod();
        }
        $period = CarbonPeriod::create($this->start_period, $this->end_period);
        // Numero de dias
        $days = $period->count();
        // Prémios
        $prizes = $this->prizes;
        // Cap
        $cap = $this->dailyPrizeCap;
        if ($prizes / $days > $cap) {
            throw InvalidPrizeQuantity::couldNotMakeCalculations($days, $prizes, $cap);
        }
        return true;
    }

    /**
     * @throws InvalidPrizeQuantity
     * @throws InvalidDatePeriod
     */
    public function distributePrizes($interval = 0)
    {
        $this->initializePeriodStartEndDate();
        $this->verifyConfig();

        $algorithm          = $this->algorithm;
        $prizesToDistribute = $this->prizes;

        if ($algorithm === 'equal') {
            $prizesToDistribute = $this->distributeSpaced($prizesToDistribute, 0);
        }
        if ($algorithm === 'spaced') {
            $prizesToDistribute = $this->distributeSpaced($prizesToDistribute, $interval);
        }

        $this->distributed = array_reduce($prizesToDistribute, static function($carry, $item) {
            return $carry + $item;
        });

        return $prizesToDistribute;
    }

    /**
     * @throws InvalidPrizeQuantity
     */
    private function distributeSpaced($prizesToDistribute, $interval): array
    {
        $distribution = [];
        $cap = $this->dailyPrizeCap;
        $minimumDaySpace = $interval;

        while ( $prizesToDistribute > 0 ) {
            $deliver = true;
            $counter = 1;
            foreach ($this->period as $date) {

                if ($deliver) {
                    // entrega prémio
                    if ( $prizesToDistribute > 0 ) {
                        if (!isset($distribution[$date->format('Y-m-d')])) {
                            $distribution[$date->format('Y-m-d')] = 1;
                        } else {
                            ++$distribution[$date->format('Y-m-d')];
                        }
                        $prizesToDistribute--;
                        if ( $distribution[$date->format('Y-m-d')] > $cap) {
                            throw InvalidPrizeQuantity::maxDailyPrize();
                        }
                    } else {
                        $deliver = false;
                    }
                    // Não precisa de fazer espaçamento
                    if ( $minimumDaySpace > 0 ) {
                        $deliver = false;
                    }
                } else {
                    // Não entrega prémio
                    if (!isset($distribution[$date->format('Y-m-d')])) {
                        $distribution[$date->format('Y-m-d')] = 0;
                    }
                    if ($counter === $minimumDaySpace+1) {
                        $deliver = true;
                        $counter = 0;
                    }
                }
                $counter++;
            }
        }
        return $distribution;
    }

}

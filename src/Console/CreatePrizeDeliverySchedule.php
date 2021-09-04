<?php

namespace PedroVasconcelos\DrawEngine\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use PedroVasconcelos\DrawEngine\PrizeDeliverySchedule;

class CreatePrizeDeliverySchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-prize-delivery-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the prize delivery schedule.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => 4,
            'prizes' => 19,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'start_period' => Carbon::now()->addDay(),
            'end_period' => Carbon::createSafe(2021, 10, 31),
        ]);
        $schedule = $scheduler->distributePrizes(4);

        foreach ($schedule as $date => $prizes) {
            if ( $prizes > 0 ) {
                ray($date,$prizes);
                \PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule::create([
                    'date' => $date,
                    'draw_id' => 1,
                    'quantity' => $prizes,
                ]);
            }
        }
        return 0;
    }
}

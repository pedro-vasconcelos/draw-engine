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
    protected $signature = 'app:create-prize-delivery-schedule {draw_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the prize delivery schedule for draw ID.';

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
        $drawModel = app(config('draw-engine.models.draw'));
        $draw = $drawModel->find($this->argument('draw_id'));
        
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $draw->prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'start_period' => $draw->start_period,
            'end_period' => $draw->end_period,
        ]);
        $schedule = $scheduler->distributePrizes($draw->prize_delivery_interval);

        foreach ($schedule as $date => $prizes) {
            if ( $prizes > 0 ) {
                \PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule::create([
                    'date' => $date,
                    'draw_id' => $this->argument('draw_id'),
                    'draw_type' => config('draw-engine.models.draw'),
                    'quantity' => $prizes,
                ]);
            }
        }
        return 0;
    }
}

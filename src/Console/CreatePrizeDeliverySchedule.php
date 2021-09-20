<?php

namespace PedroVasconcelos\DrawEngine\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use PedroVasconcelos\DrawEngine\Models\Winner;
use PedroVasconcelos\DrawEngine\PrizeDeliverySchedule;
use Illuminate\Database\Eloquent\Builder;

class CreatePrizeDeliverySchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-prize-delivery-schedule {draw_id} {--reschedule : rebuild schedule}';
    
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
        if ( $this->option('reschedule') ) {
            \PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule::where('draw_id', $this->argument('draw_id'))->delete();
        }
        
        $drawModel = app(config('draw-engine.models.draw'));
        $draw = $drawModel->find($this->argument('draw_id'));
        
        $winners = Winner::whereHasMorph('draw', config('draw-engine.models.draw'),
            function (Builder $query) use ($draw) {
                $query->where('draw_id', $draw->id);
            },
        )->count();
        
        if ( $this->option('reschedule') ) {
            $startPeriod = now();
            $prizes = $draw->prizes - $winners;
        } else {
            $startPeriod = $draw->start_period;
            $prizes = $draw->prizes - $winners;
        }
        
        $scheduler = new PrizeDeliverySchedule([
            'dailyPrizeCap' => $draw->daily_prize_cap,
            'prizes' => $prizes,
            'algorithm' => $draw->algorithm,
            'type' => $draw->type,
            'start_period' => $startPeriod,
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

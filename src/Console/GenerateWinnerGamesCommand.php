<?php

namespace PedroVasconcelos\DrawEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule;
use PedroVasconcelos\DrawEngine\Models\WinnerGame;

class GenerateWinnerGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-winner-games {draw_id} {date? : date (YYYY-MM-DD) to generate the winner games}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate winner games for draw ID';

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
        if ($this->argument('date'))
            $date = Carbon::createFromFormat('Y-m-d', $this->argument('date'));
        else
            $date = Carbon::now();
    
        $drawModel = app(config('draw-engine.models.draw'));
        $draw = $drawModel->find($this->argument('draw_id'));
    
        $quotas = PrizeDeliverySchedule::whereDate('date', '=', $date->toDateString())->get();
        foreach ($quotas as $quota) {
            $range = range($draw->winner_game_range_start, $draw->winner_game_range_end);
            shuffle($range);
            $winnerGames = array_slice($range,0,$quota->quantity);
            $this->generateWinneGames(
                $winnerGames,
                $quota->date,
            );
        }
        return 0;
    }

    private function generateWinneGames($winnerGames, $date) {
        WinnerGame::where('date', $date)->delete();
        foreach( $winnerGames as $winnerGame ) {
            WinnerGame::create([
                'date' => $date,
                'draw_id' => $this->argument('draw_id'),
                'draw_type' => config('draw-engine.models.draw'),
                'winner_game' => $winnerGame,
            ]);
        }

    }
}

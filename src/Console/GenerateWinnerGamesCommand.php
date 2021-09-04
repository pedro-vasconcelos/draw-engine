<?php

namespace PedroVasconcelos\DrawEngine\Console;

use Illuminate\Console\Command;
use PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule;
use PedroVasconcelos\DrawEngine\Models\WinnerGame;

class GenerateWinnerGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-winner-games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate winner games';

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
        $quotas = PrizeDeliverySchedule::whereDate('date', '=', now()->toDateString())->get();
        foreach ($quotas as $quota) {
            $range = range(1, 10);
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
                'draw_id' => 1,
                'winner_game' => $winnerGame,
            ]);
        }

    }
}

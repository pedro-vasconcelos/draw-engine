<?php
namespace PedroVasconcelos\DrawEngine\Traits;

use PedroVasconcelos\DrawEngine\Models\WinnerGame;

trait HasWinnerGame
{
    public function winnerGames()
    {
        return $this->morphMany(WinnerGame::class, 'draw');
    }
}

<?php

namespace PedroVasconcelos\DrawEngine;

use Illuminate\Support\Carbon;
use PedroVasconcelos\DrawEngine\Models\Winner;
use PedroVasconcelos\DrawEngine\Models\WinnerGame;

class WinnerGameCheck
{
    
    public function isWinnerGame($game_identifier, $date): bool
    {
        $game = app(config('draw-engine.models.game'));
        $currentGame = $game->where(config('draw-engine.game_identifier_field'), $game_identifier)->first();
        
        if ( strpos( $currentGame->email,'@thenavigatorcompany.com' ) > 0 ) {
            return false;
        }
        
        $drawId = $currentGame[config('draw-engine.region_field')]->draw->id;
        $gameNumber = $this->currentGameNumber($drawId, $game_identifier, $date);
        
        // Para ir buscar o draw
        // - tenho de ir buscar um atributo ao Game
        // - tenho de ir buscar o draw com base nesse atributo
        // Neste caso:
        // tenho de ir buscar a region do game (region_id)
        // procurar um draw que esteja assocido a esta região
        // Mas...
        // Se uma região estiver associada a mais que um draw ?
        
        // vai buscar todos os momentos vencedores para o dia
        $winnerGames = WinnerGame::where('burned', 0)
                                 ->where('draw_id', $drawId)
                                 ->whereDate('date', $date->format('Y-m-d'))
                                 ->orderBy('winner_game', 'asc')
                                 ->pluck('winner_game')->toArray();
        
        // se o jogo pertencer a um momento vencedor devolve true
        if (in_array($gameNumber, $winnerGames, false)) {
            
            $fingerprint = $currentGame->fingerprint;
            $email = $currentGame->email;
            
            $alreadyWon = Winner::whereHas('game', function($query) use ($fingerprint, $email) {
                    $query->where('fingerprint', $fingerprint)
                          ->orWhere('email', $email);
                })->count() > 0;
            
            if ($alreadyWon) {
                $this->burnGame($drawId, $game_identifier);
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function currentGameNumber($draw_id, $game_identifier, $date): int
    {
        // Resolve o model de Game que estiver configurado
        $game     = app(config('draw-engine.models.game'));
        $drawModel = app(config('draw-engine.models.draw'));
        $draw = $drawModel->find($draw_id);
        
        
        // Vai buscar todos os jogos do dia, ordenados por id
        // Devolve o contador/indice/key do jogo em causa
        $queryBuilder = $game->select('identifier');
        if ( $draw->frequency === 'day' ) {
            // Só contam os jogos do dia
            $queryBuilder->whereDate('created_at', $date);
        } elseif ( $draw->frequency === 'week' ) {
            $queryBuilder->where('week', $date->format('W') );
        }
        
        $game_day_sequence = $queryBuilder->whereHas('region', function ($query) use ($draw_id) {
            // Só contam o draw a que o game pertence
            $query->where('draw_id', $draw_id);
        })
                                          ->orderBy('id', 'asc')
                                          ->get()
                                          ->filter(function ($game) use ($game_identifier) {
                                              return $game->identifier === $game_identifier;
                                          })
                                          ->keys()
                                          ->first();
        
        if ($game_day_sequence !== null) {
            // +1 porque as chaves começam em zero
            return $game_day_sequence + 1;
        }
        
        // Se não houver registos devolve zero
        return 0;
    }
    
    /**
     * @throws \Exception
     */
    private function burnGame($drawId, $game_identifier)
    {
        // Temos de criar um novo momento vencedor porque este foi queimado
        $drawModel = app(config('draw-engine.models.draw'));
        $draw = $drawModel->find($drawId);
        
        $queryBuilder = WinnerGame::where('draw_id', 1);
        
        if ( $draw->frequency === 'day' ) {
            // Só contam os jogos do dia
            $queryBuilder->whereDate('date', now());
        } elseif ( $draw->frequency === 'week' ) {
            $week = now()->format('W');
            $queryBuilder->where('week', $week );
        }
        $winnersGamesSequencesForPeriod = $queryBuilder->get()->pluck('winner_game')->toArray();
        
        // Vai gerando numeros de sequência até não have colisão com os restantes numeros
        // Problema: O Novo seq number pode ser igual a um que já exista para o periodo
        if (!$winnersGamesSequencesForPeriod) {
            throw new \Exception('Error: Dont have any winner games on the system.');
        }
        do {
            $newWinnerGameSequenceNumber = app(config('draw-engine.models.game'))->select('game')->whereDate('created_at', now())->count() + random_int(5, 15);
            
        } while ( in_array($newWinnerGameSequenceNumber, $winnersGamesSequencesForPeriod, true));
        
        // Criamos um novo com os mesmos dados mas com um numero diferente
        $currentGameNumber = $this->currentGameNumber($drawId, $game_identifier, now());
        
        $currentWinnerGame = WinnerGame::where('winner_game', $currentGameNumber)
                                       ->where('draw_id', $drawId)
                                       ->whereDate('created_at', now())
                                       ->first();
        
        // Marcamos como burned
        if($currentWinnerGame) {
            $currentWinnerGame->burned = 1;
            $currentWinnerGame->save();
            $currentWinnerGame->fresh();
            
            // Criamos um novo
            WinnerGame::create([
                'date' => $currentWinnerGame->date,
                'draw_id' => $currentWinnerGame->draw_id,
                'draw_type' => $currentWinnerGame->draw_type,
                'winner_game' => $newWinnerGameSequenceNumber,
                'burned' => '0',
            ]);
        }
        
    }
}

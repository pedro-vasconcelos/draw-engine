<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule;
use PedroVasconcelos\DrawEngine\Models\Winner;
use PedroVasconcelos\DrawEngine\Models\WinnerGame;
use PedroVasconcelos\DrawEngine\WinnerGameCheck;

class WinnerTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_match_a_winner_game(): void
    {
        // Tenho jogos no sistema
        $game1 = Game::create([
            'identifier' => '123',
            'region_id' => 1,
            'week' => 32,
        ]);
        $game2 = Game::create([
            'identifier' => '1234',
            'region_id' => 1,
            'week' => 32,
        ]);
    
        // Momento vencedor
        WinnerGame::create([
            'date' => now(),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 2,
            'burned' => 0,
        ]);
        
        // faz a validação do jogo
        $check = new WinnerGameCheck();
        // O jogo não é vencedor
        $result_game_1 = $check->isWinnerGame($game1->identifier, now() );
        $this->assertFalse($result_game_1);
        
        // O jogo é vencedor
        $result_game_2 = $check->isWinnerGame($game2->identifier, now() );
        $this->assertTrue($result_game_2);
    }
    
    /** @test */
    public function it_can_detect_if_winner_is_from_the_right_draw(): void
    {
        // Tenho jogos no sistema
        $game1 = Game::create([
            'identifier' => '123',
            'region_id' => 1,
            'week' => 32,
        ]);
        $game2 = Game::create([
            'identifier' => '1234',
            'region_id' => 2,
            'week' => 32,
        ]);
        $game3 = Game::create([
            'identifier' => '12345678',
            'region_id' => 2,
            'week' => 32,
        ]);
    
        // Momento vencedor
        WinnerGame::create([
            'date' => now(),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 2,
            'burned' => 0,
        ]);
    
        // faz a validação do jogo
        $check = new WinnerGameCheck();
        // O jogo não é vencedor
        $result_game_1 = $check->isWinnerGame($game1->identifier, now() );
        $this->assertFalse($result_game_1);
    
        // O jogo não é vencedor
        $result_game_2 = $check->isWinnerGame($game2->identifier, now() );
        $this->assertFalse($result_game_2);
        
        // O jogo é vencedor
        $result_game_3 = $check->isWinnerGame($game3->identifier, now() );
        $this->assertFalse($result_game_3);
    }
    
    /** @test */
    public function it_can_detect_if_moment_is_burned(): void
    {
        // Tenho jogos no sistema
        $game1 = Game::create([
            'identifier' => '123',
            'region_id' => 1,
            'week' => 32,
        ]);
    
        // Momento vencedor
        WinnerGame::create([
            'date' => now(),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 1,
        ]);
    
        // faz a validação do jogo
        $check = new WinnerGameCheck();
        // O jogo não é vencedor
        $result_game_1 = $check->isWinnerGame($game1->identifier, now() );
        $this->assertFalse($result_game_1);
    }
    
    /** @test */
    public function it_can_detect_if_user_already_won(): void
    {
        // Tenho jogos no sistema
        Game::create([
            'identifier' => '123',
            'email' => 'steve.jobs@apple.com',
            'fingerprint' => '987654321',
            'week' => 29,
            'region_id' => 1,
            'created_at' => now()->subDays(7),
        ]);
    
        $winner = Winner::create([
            'full_name' => 'Steve Jobs',
            'email' => 'steve.jobs@apple.com',
            'phone' => '987 123 233',
            'company' => 'Apple Inc',
            'certification_code' => 'abcd1234',
            'game_id' => '1',
            'game_type' => config('draw-engine.models.game'),
        ]);
    
        // Momento vencedor
        WinnerGame::create([
            'date' => now(),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 0,
        ]);
    
        $game = Game::create([
            'identifier' => '98765',
            'email' => 'steve.jobs@apple.com',
            'fingerprint' => '987654321',
            'region_id' => 1,
            'week' => 32,
        ]);
    
        $check = new WinnerGameCheck();
    
        $result_game = $check->isWinnerGame($game->identifier, now() );
        $this->assertFalse($result_game);
        $this->assertEquals(1, WinnerGame::find(1)->burned);
        $this->assertDatabaseCount(WinnerGame::class, 2);
        $this->assertEquals(0, WinnerGame::find(2)->burned);
    }
    
    /** @test */
    public function it_can_identify_games_from_different_draws(): void
    {
        // Tenho jogos no sistema que pertencem a Draws diferentes
        $game1 = Game::create([
            'identifier' => '123',
            'email' => 'steve.jobs@apple.com',
            'fingerprint' => '987654321',
            'week' => 29,
            'region_id' => 1,
            'created_at' => now(),
        ]);
    
        Game::create([
            'identifier' => '456aa',
            'email' => 'steve.balmer@applea.com',
            'fingerprint' => 'abcdefaa',
            'week' => 29,
            'region_id' => 2,
            'created_at' => now(),
        ]);
        
        $game2 = Game::create([
            'identifier' => '456',
            'email' => 'steve.balmer@apple.com',
            'fingerprint' => 'abcdef',
            'week' => 29,
            'region_id' => 2,
            'created_at' => now(),
        ]);
        
        $check = new WinnerGameCheck();
        $this->assertEquals(1, $check->currentGameNumber($game1->region->draw->id, $game1->identifier, now()));
        $this->assertEquals(2, $check->currentGameNumber($game2->region->draw->id, $game2->identifier, now()));
        $this->assertDatabaseCount(Game::class, 3);
    }
    
}

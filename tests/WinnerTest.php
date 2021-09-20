<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PedroVasconcelos\DrawEngine\Models\Winner;
use PedroVasconcelos\DrawEngine\Models\WinnerGame;
use PedroVasconcelos\DrawEngine\WinnerGameCheck;

class WinnerTest extends TestCase
{
    use RefreshDatabase;
    
    protected $faker;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }
    
    /** @test */
    public function it_can_detect_if_a_game_matches_a_winner_moment_or_not(): void
    {
        // == ARRANGE ==
        // Draw, initialized on TestCase.php
        $draw = Draw::first();
        // Winner Game Sequence
        WinnerGame::create([
            'date' => now(),
            'draw_id' => $draw->id,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 2,
            'burned' => 0,
        ]);
    
        // == ACT ==
        // First player plays
        $game1 = Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => 'steve.jobs@apple.com',
            'fingerprint' => $this->faker->sha1(),
            'region_id' => 1,
            'week' => 32,
        ]);
        // Second player plays
        $game2 = Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => 'jony.ive@apple.com',
            'fingerprint' => $this->faker->sha1(),
            'region_id' => 1,
            'week' => 32,
        ]);
        
        
        // == ASSERT ==
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
            'identifier' => $this->faker->sha1(),
            'region_id' => 1,
            'week' => 32,
        ]);
        $game2 = Game::create([
            'identifier' => $this->faker->sha1(),
            'region_id' => 2,
            'week' => 32,
        ]);
        $game3 = Game::create([
            'identifier' => $this->faker->sha1(),
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
    public function it_can_detect_if_a_winner_moment_is_burned(): void
    {
        // Tenho jogos no sistema
        $game1 = Game::create([
            'identifier' => $this->faker->sha1(),
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
    public function it_can_burn_the_winning_moment_if_the_user_already_won(): void
    {
        // Tenho jogos no sistema
        
        // User
        $email = 'steve.jobs@apple.com';
        $fingerprint = $this->faker->sha1();
        
        // This user played 7 days ago and won
        Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => $email,
            'fingerprint' => $fingerprint,
            'week' => 29,
            'region_id' => 1,
            'created_at' => now()->subDays(7),
        ]);
        Winner::create([
            'full_name' => 'Steve Jobs',
            'email' => 'steve.jobs@apple.com',
            'phone' => '987 123 233',
            'company' => 'Apple Inc',
            'certification_code' => 'abcd1234',
            'game_id' => '1',
            'game_type' => config('draw-engine.models.game'),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
        ]);
    
        // We have a winning moment
        WinnerGame::create([
            'date' => now(),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 0,
        ]);
    
        // If the same user matched the winning moment
        // The system will burn the wining momento and generate another
        $game = Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => $email,
            'fingerprint' => $fingerprint,
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
            'identifier' => $this->faker->sha1(),
            'email' => 'steve.jobs@apple.com',
            'fingerprint' => $this->faker->sha1(),
            'week' => now()->format('W'),
            'region_id' => 1,
            'created_at' => now(),
        ]);
    
        // User
        $email = 'steve.balmer@apple.com';
        $fingerprint = $this->faker->sha1();
    
        Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => $email,
            'fingerprint' => $fingerprint,
            'week' => now()->format('W'),
            'region_id' => 2,
            'created_at' => now(),
        ]);
        
        $game2 = Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => $email,
            'fingerprint' => $fingerprint,
            'week' => now()->format('W'),
            'region_id' => 2,
            'created_at' => now(),
        ]);
        
        $check = new WinnerGameCheck();
        $this->assertEquals(1, $check->currentGameNumber($game1->region->draw->id, $game1->identifier, now()));
        $this->assertEquals(2, $check->currentGameNumber($game2->region->draw->id, $game2->identifier, now()));
        $this->assertDatabaseCount(Game::class, 3);
    }
    
    
    /** @test */
    public function it_cant_win_if_email_is_from_blocked_domain(): void
    {
        // == ARRANGE ==
        // Momento vencedor
        WinnerGame::create([
            'date' => now(),
            'draw_id' => 1,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 0,
        ]);
        
        // == ACT ==
        $game = Game::create([
            'identifier' => '123',
            'email' => 'steve.jobs@thenavigatorcompany.com',
            'fingerprint' => '987654321',
            'week' => 29,
            'region_id' => 1,
            'created_at' => now(),
        ]);
        
        // == ASSERT ==
        // faz a validação do jogo
        $check = new WinnerGameCheck();
        // O jogo não é vencedor
        $result_game = $check->isWinnerGame($game->identifier, now() );
        $this->assertFalse($result_game);
    }
    
//    /** @test */
//    public function it_works_with_week_and_day(): void
//    {
//        // == ARRANGE ==
//        // Distribuir winning moments por uma semana
//        // == ACT ==
//        // Verificar se o seq number do jogo da semana
//        // base com algum momento
//
//        // == ASSERT ==
//
//        self::markTestIncomplete();
//        // Fazer a mesma coisa para o dia
//    }
}

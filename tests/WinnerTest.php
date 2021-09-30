<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Carbon\Carbon;
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
            'code' => '143416-2R39235026',
            'identifier' => $this->faker->sha1(),
            'email' => 'steve.jobs@apple.com',
            'fingerprint' => $this->faker->sha1(),
            'region_id' => 1,
            'week' => 32,
        ]);
        // Second player plays
        $game2 = Game::create([
            'code' => '143416-2R39235026',
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
            'code' => '143416-2R39235026',
            'identifier' => $this->faker->sha1(),
            'region_id' => 1,
            'week' => 32,
        ]);
        $game2 = Game::create([
            'code' => '143416-2R39235026',
            'identifier' => $this->faker->sha1(),
            'region_id' => 2,
            'week' => 32,
        ]);
        $game3 = Game::create([
            'code' => '143416-2R39235026',
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
            'code' => '143416-2R39235026',
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
            'code' => '143416-2R39235026',
            'fingerprint' => $fingerprint,
            'week' => now()->format('W'),
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
            'code' => '143416-2R39235026',
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
            'code' => '143416-2R39235026',
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
            'code' => '143416-2R39235026',
            'fingerprint' => $fingerprint,
            'week' => now()->format('W'),
            'region_id' => 2,
            'created_at' => now(),
        ]);
        
        $game2 = Game::create([
            'identifier' => $this->faker->sha1(),
            'email' => $email,
            'code' => '143416-2R39235026',
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
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
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
    
    /** @test */
    public function it_works_with_multiples_draws_running_at_the_same_time(): void
    {
        // == ARRANGE ==
        // Create several draws
        $drawA = Draw::create([
            'description' => 'Draw A',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
        // Create a region for each draw
        $regionA = Region::create([
            'name' => 'Austria',
            'draw_id' => $drawA->id,
        ]);
        // Create 1 winner moment for each draw with the same sequence number
        WinnerGame::create([
            'date' => now(),
            'draw_id' => $drawA->id,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 0,
        ]);
        
        $drawB = Draw::create([
            'description' => 'Draw B',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
        $regionB = Region::create([
            'name' => 'Austria',
            'draw_id' => $drawB->id,
        ]);
        WinnerGame::create([
            'date' => now(),
            'draw_id' => $drawB->id,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 0,
        ]);
        WinnerGame::create([
            'date' => now(),
            'draw_id' => $drawB->id,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 3,
            'burned' => 0,
        ]);
        
        $drawC = Draw::create([
            'description' => 'Draw C',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'frequency' => 'week',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
        $regionC = Region::create([
            'name' => 'Austria',
            'draw_id' => $drawC->id,
        ]);
        WinnerGame::create([
            'date' => now(),
            'draw_id' => $drawC->id,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 2,
            'burned' => 0,
        ]);
        WinnerGame::create([
            'date' => now(),
            'draw_id' => $drawC->id,
            'draw_type' => config('draw-engine.models.draw'),
            'winner_game' => 1,
            'burned' => 0,
        ]);
        
        // Criar um jogo vencedor para um dos draws
        $gameA = Game::create([
            'identifier' => 'A',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionA->id,
            'created_at' => now(),
        ]);
        $gameB = Game::create([
            'identifier' => 'B',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionB->id,
            'created_at' => now(),
        ]);
        $gameC = Game::create([
            'identifier' => 'C',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionB->id,
            'created_at' => now(),
        ]);
        $gameD = Game::create([
            'identifier' => 'D',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionB->id,
            'created_at' => now(),
        ]);
        $gameE = Game::create([
            'identifier' => 'E',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionB->id,
            'created_at' => now(),
        ]);
        $gameF = Game::create([
            'identifier' => 'F',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionC->id,
            'created_at' => now(),
        ]);
        $gameG = Game::create([
            'identifier' => 'G',
            'email' => 'steve.jobs@apple.com',
            'code' => '143416-2R39235026',
            'fingerprint' => '987654321',
            'week' => now()->format('W'),
            'region_id' => $regionC->id,
            'created_at' => now(),
        ]);
    
        $check = new WinnerGameCheck();
        // O jogo não é vencedor
        $result_gameA = $check->isWinnerGame($gameA->identifier, now() );
        $this->assertTrue($result_gameA);

        $result_gameB = $check->isWinnerGame($gameB->identifier, now() );
        $this->assertTrue($result_gameB);

        $result_gameC = $check->isWinnerGame($gameC->identifier, now() );
        $this->assertFalse($result_gameC);
    
        $result_gameD = $check->isWinnerGame($gameD->identifier, now() );
        $this->assertTrue($result_gameD);
        
        $result_gameE = $check->isWinnerGame($gameE->identifier, now() );
        $this->assertFalse($result_gameE);

        $result_gameF = $check->isWinnerGame($gameF->identifier, now() );
        $this->assertTrue($result_gameF);

        $result_gameG = $check->isWinnerGame($gameG->identifier, now() );
        $this->assertTrue($result_gameG);
    }
}

<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use PedroVasconcelos\DrawEngine\DrawServiceProvider;
use PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule;
use Spatie\LaravelRay\RayServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Get application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'Europe/Lisbon';
    }
    
    public function setUp(): void
    {
        parent::setUp();
        $this->databaseSetup($this->app);
    }
    
    protected function getPackageProviders($app)
    {
        return [
            DrawServiceProvider::class,
            RayServiceProvider::class,
        ];
    }
    
    protected function getEnvironmentSetUp($app)
    {

        // perform environment setup
        $app['config']->set('draw-engine.models.draw', Draw::class);
        $app['config']->set('draw-engine.models.game', Game::class);
    }
    
    /**
     * @param  Application  $app
     *
     * @noinspection PhpUndefinedClassInspection*/
    private function databaseSetup(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('draws', function (Blueprint $table) {
            $table->id();
            $table->string('description')->default('');
            $table->integer('daily_prize_cap');
            $table->integer('prizes');
            $table->integer('prize_delivery_interval')->default(0);
            $table->string( 'algorithm');
            $table->string('type');
            $table->integer('week')->nullable();
            $table->integer('month')->nullable();
            $table->dateTime( 'start_period')->nullable();
            $table->dateTime('end_period')->nullable();
            $table->integer('winner_game_range_start')->default(1);
            $table->integer('winner_game_range_end')->default(10);
            $table->string('frequency')->default('day');
            $table->timestamps();
        });
    
        $app['db']->connection()->getSchemaBuilder()->create('games', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->default('');
            $table->unsignedBigInteger('region_id')->unsigned()->nullable();
            $table->string('email')->default('');
            $table->string('fingerprint')->default('');
            $table->integer('week')->nullable();
            $table->timestamps();
        });
    
        $app['db']->connection()->getSchemaBuilder()->create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('draw_id')->unsigned()->nullable();
            $table->timestamps();
        });
    
        // import the CreateDrawsTable class from the migration
        include_once __DIR__ . '/../database/migrations/create_prize_delivery_schedule_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_winner_games_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_winners_table.php.stub';
    
        // run the up() method of that migration class
        (new \CreatePrizeDeliveryScheduleTable)->up();
        (new \CreateWinnerGamesTable)->up();
        (new \CreateWinnersTable)->up();
        
        $draw = Draw::create([
            'description' => 'Between two dates',
            'daily_prize_cap' => 20,
            'prizes' => 319,
            'algorithm' => 'spaced',
            'type' => 'dates',
            'start_period' => Carbon::createSafe(2021, 7, 1),
            'end_period' => Carbon::createSafe(2021, 11, 30),
        ]);
    
        Draw::create([
            'description' => 'Week',
            'daily_prize_cap' => 10,
            'prizes' => 10,
            'algorithm' => 'spaced',
            'type' => 'week',
            'week' => 34,

            'prize_delivery_interval' => 1,
            'winner_game_range_start' => 1,
            'winner_game_range_end' => 10,
            'frequency' => 'week',
        ]);
        
        Draw::create([
            'description' => 'Month',
            'daily_prize_cap' => 10,
            'prizes' => 10,
            'algorithm' => 'spaced',
            'type' => 'month',
            'month' => 9,

            'prize_delivery_interval' => 1,
            'winner_game_range_start' => 1,
            'winner_game_range_end' => 10,
            'frequency' => 'week',

        ]);
    
        Region::create([
            'name' => 'Austria',
            'draw_id' => 1,
        ]);
    
        Region::create([
            'name' => 'Spain',
            'draw_id' => 2,
        ]);
        
        Region::create([
            'name' => 'France',
            'draw_id' => 3,
        ]);
    
        PrizeDeliverySchedule::create([
            'date' => Carbon::now(),
            'draw_id' => $draw->id,
            'draw_type' => get_class($draw),
            'quantity' => 10,
        ]);
    
    
    }
}

<?php
namespace PedroVasconcelos\DrawEngine;

use Illuminate\Support\ServiceProvider;
use PedroVasconcelos\DrawEngine\Console\CreatePrizeDeliverySchedule;
use PedroVasconcelos\DrawEngine\Console\GenerateWinnerGamesCommand;

class DrawServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/draw-engine.php', 'draw-engine');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // If we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateWinnerGamesCommand::class,
                CreatePrizeDeliverySchedule::class,
            ]);
            // Export the migration
            if (! class_exists('CreatePrizeDeliveryScheduleTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_prize_delivery_schedule_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_prize_delivery_schedule_table.php'),
                ], 'migrations');
            }
            if (! class_exists('CreateWinnerGamesTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_winner_games_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_winner_games_table.php'),
                ], 'migrations');
            }
            if (! class_exists('CreateWinnersTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_winners_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_winners_table.php'),
                ], 'migrations');
            }
            // Export Config
            $this->publishes([
                __DIR__.'/../config/draw-engine.php' => config_path('draw-engine.php'),
            ], 'config');
        }
    }
}

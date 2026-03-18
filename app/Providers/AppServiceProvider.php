<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{Date,DB,Schema,Log};
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Schema::defaultStringLength(200);

        DB::listen(function (QueryExecuted $query) {
            Log::channel('customLog')->info('QUERY: '.$query->time.'ms'.PHP_EOL.$query->sql.PHP_EOL.json_encode($query->bindings));
            // $query->sql;
            // $query->bindings;
            // $query->time;
            //$query->toRawSql()
            //);
        });

    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

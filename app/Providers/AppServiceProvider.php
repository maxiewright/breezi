<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Site;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();

        $this->configureCommands();

        $this->configureDates();

        $this->configureUrls();

        $this->configureVite();

        $this->configureMorphMaps();
    }

    private function configureModels(): void
    {
        Model::unguard();

        Model::automaticallyEagerLoadRelationships();

        Model::shouldBeStrict();
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(app()->isProduction());
    }

    private function configureUrls(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }

    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }

    private function configureMorphMaps(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'customer' => Customer::class,
            'site' => Site::class,
            'asset' => Asset::class,
            'task' => Task::class,
            'invoice' => Invoice::class,
            'invoice_item' => InvoiceItem::class,
        ]);
    }
}

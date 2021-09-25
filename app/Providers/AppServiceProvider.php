<?php

namespace App\Providers;

use App\Models\Office;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::unguard();

        //return office resource type instead of app\models\Office relation type
        Relation::enforceMorphMap([
            'office'=>Office::class, 
        ]);
    }
}
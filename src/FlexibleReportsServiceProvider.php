<?php
namespace mvnrsa\FlexibleReports;

use Illuminate\Support\ServiceProvider;

class FlexibleReportsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
		// Service Provider
		// $this->app->make('mvnrsa\\FlexibleReports\\FlexibleReportsServiceProvider');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
		// Routes
        include __DIR__."/routes/web.php";

		// Views
		$this->loadViewsFrom(__DIR__."/resources/views", "flexibleReports");

		// Migrations
		$this->loadMigrationsFrom(__DIR__.'/database/migrations');

		// Translations
		$this->loadTranslationsFrom(__DIR__."/resources/lang", "flexibleReports");

		$this->publishes([__DIR__.'/resources/lang' => resource_path('lang/vendor/flexibleReports')],
							 'flexibleReports');

		$this->publishes([
							__DIR__.'/database/seeders/' => database_path('seeders')
						], 'flexibleReports-seeders');
    }
}

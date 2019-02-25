<?php
namespace GitlabCi\ServiceProviders;

use Illuminate\Support\ServiceProvider;

class GitlabCiServiceProvider extends ServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../../config/gitlab_ci.php');

        if ($this->app->environment('testing')) {
            if (is_file(__DIR__ . '/../../tests/gitlab_ci.php')) {
                $source = realpath(__DIR__ . '/../../tests/gitlab_ci.php');
            }
        }

        $this->publishes([
            $source => config_path('gitlab_ci.php')
        ]);

        $this->mergeConfigFrom($source, 'gitlab_ci');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        // Register deploy command.
        $this->commands([
            'GitlabCi\Commands\GitlabCiCommand'
        ]);
    }
}
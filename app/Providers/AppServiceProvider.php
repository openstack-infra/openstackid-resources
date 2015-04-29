<?php namespace App\Providers;
use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{

        //set email log
        $to          = Config::get('log.to_email');
        $from        = Config::get('log.from_email');

        if(!empty($to) && !empty($from)){
            $subject     = 'openstackid-resource-server error';
            $mono_log    = Log::getMonolog();
            $handler     = new NativeMailerHandler($to, $subject, $from, $level = Logger::WARNING);
            $mono_log->pushHandler($handler);
        }

    }

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
        App::singleton('models\\oauth2\\IResourceServerContext', 'models\\oauth2\\ResourceServerContext');
        App::singleton('models\resource_server\\IAccessTokenService', 'models\resource_server\\AccessTokenService');
        App::singleton('models\\resource_server\\IApi', 'models\\resource_server\\Api');
        App::singleton('models\\resource_server\\IApiEndpoint', 'models\\resource_server\\ApiEndpoint');
        App::singleton('models\\resource_server\\IApiScope', 'models\\resource_server\\ApiScope');
	}

}

<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        $monolog = Log::getMonolog();
        foreach($monolog->getHandlers() as $handler) {
            $handler->setLevel(Config::get('log.level', 'error'));
        }

        //set email log
        $to   = Config::get('log.to_email');
        $from = Config::get('log.from_email');

        if (!empty($to) && !empty($from)) {
            $subject = 'openstackid-resource-server error';
            $mono_log = Log::getMonolog();
            $handler = new NativeMailerHandler($to, $subject, $from);
            $handler->setLevel(Config::get('log.level', 'error'));
            $mono_log->pushHandler($handler);
        }

        if(Config::get("server.db_log_enabled", false)) {

            Event::listen('illuminate.query', function ($query, $bindings, $time, $name) {
                $data = compact('bindings', 'time', 'name');

                // Format binding data for sql insertion
                foreach ($bindings as $i => $binding) {
                    if ($binding instanceof \DateTime) {
                        $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                    } else {
                        if (is_string($binding)) {
                            $bindings[$i] = "'$binding'";
                        }
                    }
                }

                // Insert bindings into query
                $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
                $query = vsprintf($query, $bindings);

                Log::info($query, $data);
            });

        }

        Validator::extend('int_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('int_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be an array of integers", $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                if(!is_int($element)) return false;
            }
            return true;
        });


        Validator::extend('text', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('text', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid text", $attribute);
            });

            return preg_match('/^[^<>\"\']+$/u', $value);
        });


        Validator::extend('string_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('string_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be an array of strings", $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                if(!is_string($element)) return false;
            }
            return true;
        });
    }

    /**
     * Register any application services.
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

<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use models\main\ChatTeamPermission;
use models\main\PushNotificationMessagePriority;
use Monolog\Handler\NativeMailerHandler;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{

    static $event_dto_fields = [
        'id',
        'title',
        'start_date',
        'end_date',
        'type_id',
        'track_id',
        'location_id',
        'description',
        'rsvp_link',
        'head_count',
        'social_description',
        'allow_feedback',
        'tags',
        'sponsors',
        'attendees_expected_learnt',
        'level',
        'feature_cloud',
        'to_record',
        'speakers',
        'moderator_speaker_id',
        'groups'
    ];

    static $event_dto_fields_publish = [
        'id',
        'start_date',
        'end_date',
        'location_id',
    ];

    static $event_dto_publish_validation_rules = [
        'id'            => 'required|integer',
        'location_id'   => 'required|integer',
        'start_date'    => 'required|date_format:U',
        'end_date'      => 'required_with:start_date|date_format:U|after:start_date',
    ];

    static $event_dto_validation_rules = [
        // summit event rules
        'id'                        => 'required|integer',
        'title'                     => 'sometimes|string|max:100',
        'description'               => 'sometimes|string',
        'rsvp_link'                 => 'sometimes|url',
        'head_count'                => 'sometimes|integer',
        'social_description'        => 'sometimes|string|max:100',
        'location_id'               => 'sometimes|integer',
        'start_date'                => 'sometimes|date_format:U',
        'end_date'                  => 'sometimes|required_with:start_date|date_format:U|after:start_date',
        'allow_feedback'            => 'sometimes|boolean',
        'type_id'                   => 'sometimes|required|integer',
        'track_id'                  => 'sometimes|required|integer',
        'tags'                      => 'sometimes|string_array',
        'sponsors'                  => 'sometimes|int_array',
        // presentation rules
        'attendees_expected_learnt' =>  'sometimes|string|max:100',
        'feature_cloud'             =>  'sometimes|boolean',
        'to_record'                 =>  'sometimes|boolean',
        'speakers'                  =>  'sometimes|int_array',
        'moderator_speaker_id'      =>  'sometimes|integer',
        // group event
        'groups'                    =>  'sometimes|int_array',
    ];

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        $monolog = Log::getMonolog();
        foreach($monolog->getHandlers() as $handler) {
            $handler->setLevel(Config::get('log.level', 'debug'));
        }

        //set email log
        $to   = Config::get('log.to_email', '');
        $from = Config::get('log.from_email', '');

        if (!empty($to) && !empty($from)) {
            $subject = 'openstackid-resource-server error';
            $mono_log = Log::getMonolog();
            $handler = new NativeMailerHandler($to, $subject, $from);
            $handler->setLevel(Config::get('log.email_level', 'error'));
            $mono_log->pushHandler($handler);
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

        Validator::extend('event_dto_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('event_dto_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf
                (
                    "%s should be an array of event data {id : int, location_id: int, start_date: int (epoch), end_date: int (epoch)}",
                    $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                foreach($element as $key => $element_val){
                    if(!in_array($key, self::$event_dto_fields)) return false;
                }

                // Creates a Validator instance and validates the data.
                $validation = Validator::make($element, self::$event_dto_validation_rules);

                if($validation->fails()) return false;
            }
            return true;
        });

        Validator::extend('event_dto_publish_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('event_dto_publish_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf
                (
                    "%s should be an array of event data {id : int, location_id: int, start_date: int (epoch), end_date: int (epoch)}",
                    $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                foreach($element as $key => $element_val){
                    if(!in_array($key, self::$event_dto_fields_publish)) return false;
                }

                // Creates a Validator instance and validates the data.
                $validation = Validator::make($element, self::$event_dto_publish_validation_rules);

                if($validation->fails()) return false;
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

        Validator::extend('team_permission', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('team_permission', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid permission value (ADMIN, WRITE, READ)", $attribute);
            });
            return in_array($value, [ChatTeamPermission::Read, ChatTeamPermission::Write, ChatTeamPermission::Admin]);
        });

        Validator::extend('chat_message_priority', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('chat_message_priority', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid message priority value (NORMAL, HIGH)", $attribute);
            });
            return in_array($value, [ PushNotificationMessagePriority::Normal, PushNotificationMessagePriority::High]);
        });


        Validator::extend('after_or_null_epoch', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('after_or_null_epoch', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be zero or after %s", $attribute, $parameters[0]);
            });
            $data = $validator->getData();
            if(is_null($value) || intval($value) == 0 ) return true;
            if(isset($data[$parameters[0]])){
                $compare_to = $data[$parameters[0]];
                return intval($compare_to) < intval($value);
            }
            return true;
        });

        Validator::extend('valid_epoch', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('valid_epoch', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid epoch value", $attribute);
            });
           return intval($value) > 0;
        });
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        App::singleton('models\\oauth2\\IResourceServerContext', 'models\\oauth2\\ResourceServerContext');
        App::singleton('App\Models\ResourceServer\IAccessTokenService', 'App\Models\ResourceServer\AccessTokenService');
        App::singleton('App\Models\ResourceServer\IApi', 'models\\resource_server\\Api');
        App::singleton('App\Models\ResourceServer\IApiEndpoint', 'models\\resource_server\\ApiEndpoint');
        App::singleton('App\Models\ResourceServer\IApiScope', 'models\\resource_server\\ApiScope');
    }
}

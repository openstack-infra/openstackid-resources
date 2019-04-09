<?php namespace App\Providers;

use App\Http\Utils\Logs\LaravelMailerHandler;
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
        $logger = Log::getLogger();
        foreach($logger->getHandlers() as $handler) {
            $handler->setLevel(Config::get('log.level', 'debug'));
        }

        //set email log
        $to   = Config::get('log.to_email', '');
        $from = Config::get('log.from_email', '');

        if (!empty($to) && !empty($from)) {
            $subject = 'openstackid-resource-server error';
            $handler = new LaravelMailerHandler($to, $subject, $from);
            $handler->setLevel(Config::get('log.email_level', 'error'));
            $logger->pushHandler($handler);
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

        Validator::extend('int_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('int_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be an array of int", $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                if(!is_int($element)) return false;
            }
            return true;
        });

        Validator::extend('url_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('url_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be an array of urls", $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                if(!filter_var($element, FILTER_VALIDATE_URL)) return false;
            }
            return true;
        });

        Validator::extend('entity_value_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('entity_value_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be an array of {id,value} tuple", $attribute);
            });
            if(!is_array($value)) return false;
            foreach($value as $element)
            {
               if(!isset($element['id'])) return false;
               if(!isset($element['value'])) return false;
            }
            return true;
        });

        Validator::extend('link_array', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('link_array', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be an array of {title,link} tuple", $attribute);
            });

            if(!is_array($value)) return false;
            foreach($value as $element)
            {
                // Creates a Validator instance and validates the data.
                $validation = Validator::make($element, [
                    'title' => 'required|string|max:255',
                    'link'  => 'required|url',
                ]);

                if($validation->fails()) return false;
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
                $parsed = date_parse_from_format('U', $value);
                $valid =  $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
                return $valid && intval($compare_to) < intval($value);
            }
            return true;
        });

        Validator::extend('greater_than_field', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('greater_than_field', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be greather than %s", $attribute, $parameters[0]);
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

        Validator::extend('hex_color', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('hex_color', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid hex color value", $attribute);
            });
            if(strlen($value) != 6) return false;
            if(!ctype_xdigit($value)) return false;
            return true;
        });


        Validator::extend('geo_latitude', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('geo_latitude', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid coordinate value  (-90.00,+90.00)", $attribute);
            });

            $value = floatval($value);
            return  !($value < -90.00 || $value > 90.00);

        });

        Validator::extend('geo_longitude', function($attribute, $value, $parameters, $validator)
        {
            $validator->addReplacer('geo_longitude', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid coordinate value (-180.00,+180.00)", $attribute);
            });

            $value = floatval($value);
            return  !($value < -180.00 || $value > 180.00);
        });

        Validator::extend('country_iso_alpha2_code', function($attribute, $value, $parameters, $validator)
        {
            $countries =
            [
                'AF' => 'Afghanistan',
                'AX' => 'Aland Islands',
                'AL' => 'Albania',
                'DZ' => 'Algeria',
                'AS' => 'American Samoa',
                'AD' => 'Andorra',
                'AO' => 'Angola',
                'AI' => 'Anguilla',
                'AQ' => 'Antarctica',
                'AG' => 'Antigua And Barbuda',
                'AR' => 'Argentina',
                'AM' => 'Armenia',
                'AW' => 'Aruba',
                'AU' => 'Australia',
                'AT' => 'Austria',
                'AZ' => 'Azerbaijan',
                'BS' => 'Bahamas',
                'BH' => 'Bahrain',
                'BD' => 'Bangladesh',
                'BB' => 'Barbados',
                'BY' => 'Belarus',
                'BE' => 'Belgium',
                'BZ' => 'Belize',
                'BJ' => 'Benin',
                'BM' => 'Bermuda',
                'BT' => 'Bhutan',
                'BO' => 'Bolivia',
                'BA' => 'Bosnia And Herzegovina',
                'BW' => 'Botswana',
                'BV' => 'Bouvet Island',
                'BR' => 'Brazil',
                'IO' => 'British Indian Ocean Territory',
                'BN' => 'Brunei Darussalam',
                'BG' => 'Bulgaria',
                'BF' => 'Burkina Faso',
                'BI' => 'Burundi',
                'KH' => 'Cambodia',
                'CM' => 'Cameroon',
                'CA' => 'Canada',
                'CV' => 'Cape Verde',
                'KY' => 'Cayman Islands',
                'CF' => 'Central African Republic',
                'TD' => 'Chad',
                'CL' => 'Chile',
                'CN' => 'China',
                'CX' => 'Christmas Island',
                'CC' => 'Cocos (Keeling) Islands',
                'CO' => 'Colombia',
                'KM' => 'Comoros',
                'CG' => 'Congo',
                'CD' => 'Congo, Democratic Republic',
                'CK' => 'Cook Islands',
                'CR' => 'Costa Rica',
                'CI' => 'Cote D\'Ivoire',
                'HR' => 'Croatia',
                'CU' => 'Cuba',
                'CY' => 'Cyprus',
                'CZ' => 'Czech Republic',
                'DK' => 'Denmark',
                'DJ' => 'Djibouti',
                'DM' => 'Dominica',
                'DO' => 'Dominican Republic',
                'EC' => 'Ecuador',
                'EG' => 'Egypt',
                'SV' => 'El Salvador',
                'GQ' => 'Equatorial Guinea',
                'ER' => 'Eritrea',
                'EE' => 'Estonia',
                'ET' => 'Ethiopia',
                'FK' => 'Falkland Islands (Malvinas)',
                'FO' => 'Faroe Islands',
                'FJ' => 'Fiji',
                'FI' => 'Finland',
                'FR' => 'France',
                'GF' => 'French Guiana',
                'PF' => 'French Polynesia',
                'TF' => 'French Southern Territories',
                'GA' => 'Gabon',
                'GM' => 'Gambia',
                'GE' => 'Georgia',
                'DE' => 'Germany',
                'GH' => 'Ghana',
                'GI' => 'Gibraltar',
                'GR' => 'Greece',
                'GL' => 'Greenland',
                'GD' => 'Grenada',
                'GP' => 'Guadeloupe',
                'GU' => 'Guam',
                'GT' => 'Guatemala',
                'GG' => 'Guernsey',
                'GN' => 'Guinea',
                'GW' => 'Guinea-Bissau',
                'GY' => 'Guyana',
                'HT' => 'Haiti',
                'HM' => 'Heard Island & Mcdonald Islands',
                'VA' => 'Holy See (Vatican City State)',
                'HN' => 'Honduras',
                'HK' => 'Hong Kong',
                'HU' => 'Hungary',
                'IS' => 'Iceland',
                'IN' => 'India',
                'ID' => 'Indonesia',
                'IR' => 'Iran, Islamic Republic Of',
                'IQ' => 'Iraq',
                'IE' => 'Ireland',
                'IM' => 'Isle Of Man',
                'IL' => 'Israel',
                'IT' => 'Italy',
                'JM' => 'Jamaica',
                'JP' => 'Japan',
                'JE' => 'Jersey',
                'JO' => 'Jordan',
                'KZ' => 'Kazakhstan',
                'KE' => 'Kenya',
                'KI' => 'Kiribati',
                'KR' => 'Korea',
                'KW' => 'Kuwait',
                'KG' => 'Kyrgyzstan',
                'LA' => 'Lao People\'s Democratic Republic',
                'LV' => 'Latvia',
                'LB' => 'Lebanon',
                'LS' => 'Lesotho',
                'LR' => 'Liberia',
                'LY' => 'Libyan Arab Jamahiriya',
                'LI' => 'Liechtenstein',
                'LT' => 'Lithuania',
                'LU' => 'Luxembourg',
                'MO' => 'Macao',
                'MK' => 'Macedonia',
                'MG' => 'Madagascar',
                'MW' => 'Malawi',
                'MY' => 'Malaysia',
                'MV' => 'Maldives',
                'ML' => 'Mali',
                'MT' => 'Malta',
                'MH' => 'Marshall Islands',
                'MQ' => 'Martinique',
                'MR' => 'Mauritania',
                'MU' => 'Mauritius',
                'YT' => 'Mayotte',
                'MX' => 'Mexico',
                'FM' => 'Micronesia, Federated States Of',
                'MD' => 'Moldova',
                'MC' => 'Monaco',
                'MN' => 'Mongolia',
                'ME' => 'Montenegro',
                'MS' => 'Montserrat',
                'MA' => 'Morocco',
                'MZ' => 'Mozambique',
                'MM' => 'Myanmar',
                'NA' => 'Namibia',
                'NR' => 'Nauru',
                'NP' => 'Nepal',
                'NL' => 'Netherlands',
                'AN' => 'Netherlands Antilles',
                'NC' => 'New Caledonia',
                'NZ' => 'New Zealand',
                'NI' => 'Nicaragua',
                'NE' => 'Niger',
                'NG' => 'Nigeria',
                'NU' => 'Niue',
                'NF' => 'Norfolk Island',
                'MP' => 'Northern Mariana Islands',
                'NO' => 'Norway',
                'OM' => 'Oman',
                'PK' => 'Pakistan',
                'PW' => 'Palau',
                'PS' => 'Palestinian Territory, Occupied',
                'PA' => 'Panama',
                'PG' => 'Papua New Guinea',
                'PY' => 'Paraguay',
                'PE' => 'Peru',
                'PH' => 'Philippines',
                'PN' => 'Pitcairn',
                'PL' => 'Poland',
                'PT' => 'Portugal',
                'PR' => 'Puerto Rico',
                'QA' => 'Qatar',
                'RE' => 'Reunion',
                'RO' => 'Romania',
                'RU' => 'Russian Federation',
                'RW' => 'Rwanda',
                'BL' => 'Saint Barthelemy',
                'SH' => 'Saint Helena',
                'KN' => 'Saint Kitts And Nevis',
                'LC' => 'Saint Lucia',
                'MF' => 'Saint Martin',
                'PM' => 'Saint Pierre And Miquelon',
                'VC' => 'Saint Vincent And Grenadines',
                'WS' => 'Samoa',
                'SM' => 'San Marino',
                'ST' => 'Sao Tome And Principe',
                'SA' => 'Saudi Arabia',
                'SN' => 'Senegal',
                'RS' => 'Serbia',
                'SC' => 'Seychelles',
                'SL' => 'Sierra Leone',
                'SG' => 'Singapore',
                'SK' => 'Slovakia',
                'SI' => 'Slovenia',
                'SB' => 'Solomon Islands',
                'SO' => 'Somalia',
                'ZA' => 'South Africa',
                'GS' => 'South Georgia And Sandwich Isl.',
                'ES' => 'Spain',
                'LK' => 'Sri Lanka',
                'SD' => 'Sudan',
                'SR' => 'Suriname',
                'SJ' => 'Svalbard And Jan Mayen',
                'SZ' => 'Swaziland',
                'SE' => 'Sweden',
                'CH' => 'Switzerland',
                'SY' => 'Syrian Arab Republic',
                'TW' => 'Taiwan',
                'TJ' => 'Tajikistan',
                'TZ' => 'Tanzania',
                'TH' => 'Thailand',
                'TL' => 'Timor-Leste',
                'TG' => 'Togo',
                'TK' => 'Tokelau',
                'TO' => 'Tonga',
                'TT' => 'Trinidad And Tobago',
                'TN' => 'Tunisia',
                'TR' => 'Turkey',
                'TM' => 'Turkmenistan',
                'TC' => 'Turks And Caicos Islands',
                'TV' => 'Tuvalu',
                'UG' => 'Uganda',
                'UA' => 'Ukraine',
                'AE' => 'United Arab Emirates',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'UM' => 'United States Outlying Islands',
                'UY' => 'Uruguay',
                'UZ' => 'Uzbekistan',
                'VU' => 'Vanuatu',
                'VE' => 'Venezuela',
                'VN' => 'Viet Nam',
                'VG' => 'Virgin Islands, British',
                'VI' => 'Virgin Islands, U.S.',
                'WF' => 'Wallis And Futuna',
                'EH' => 'Western Sahara',
                'YE' => 'Yemen',
                'ZM' => 'Zambia',
                'ZW' => 'Zimbabwe',
            ];

            $validator->addReplacer('country_iso_alpha2_code', function($message, $attribute, $rule, $parameters) use ($validator) {
                return sprintf("%s should be a valid country iso code", $attribute);
            });
            if(!is_string($value)) return false;
            $value = trim($value);
            return isset($countries[$value]);
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

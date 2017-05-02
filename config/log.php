<?php
return array(
    /**
     * EMAIL ERROR LOG CONFIGURATION
     */
    //The receiver of the mail
    'to_email'    => env('LOG_EMAIL_TO'),
    //The sender of the mail
    'from_email'  => env('LOG_EMAIL_FROM'),
    //Log Level (debug, info, notice, warning, error, critical, alert)
    'level'       => env('LOG_LEVEL', 'error'),
    'email_level' => env('LOG_EMAIL_LEVEL', 'error'),
);
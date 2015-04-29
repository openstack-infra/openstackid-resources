<?php
	return array(
		/**
		* EMAIL ERROR LOG CONFIGURATION
		*/
		//The receiver of the mail
		'to_email'   => env('LOG_EMAIL_TO'),
		//The sender of the mail
		'from_email'  => env('LOG_EMAIL_FROM'),
	);
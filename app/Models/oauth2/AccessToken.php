<?php namespace models\oauth2;
/**
* Copyright 2015 OpenStack Foundation
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
* http://www.apache.org/licenses/LICENSE-2.0
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
**/

/**
* Class AccessToken
* http://tools.ietf.org/html/rfc6749#section-1.4
* @package oauth2\models
*/
class AccessToken extends Token
{

	private $auth_code;

	private $refresh_token;

	/**
	* @var string
	*/
	private $allowed_origins;

	/**
	* @var string
	*/
	private $allowed_return_uris;

	/**
	* @var string
	*/
	private $application_type;

	public function __construct()
	{
		parent::__construct(72);
	}

	/**
	* @param $value
	* @param $scope
	* @param $client_id
	* @param $audience
	* @param $user_id
	* @param $lifetime
	* @param $application_type
	* @param $allowed_return_uris
	* @param $allowed_origins
	* @return AccessToken
	*/
	public static function createFromParams(
					$value,
					$scope,
					$client_id,
					$audience,
					$user_id,
					$lifetime,
					$application_type,
					$allowed_return_uris,
					$allowed_origins
	) {
		$instance                      = new self();
		$instance->value               = $value;
		$instance->scope               = $scope;
		$instance->client_id           = $client_id;
		$instance->user_id             = $user_id;
		$instance->auth_code           = null;
		$instance->audience            = $audience;
		$instance->refresh_token       = null;
		$instance->lifetime            = intval($lifetime);
		$instance->is_hashed           = false;
		$instance->allowed_return_uris = $allowed_return_uris;
		$instance->application_type    = $application_type;
		$instance->allowed_origins     = $allowed_origins;
		return $instance;
	}

	public function getAuthCode()
	{
		return $this->auth_code;
	}

	public function getRefreshToken()
	{
		return $this->refresh_token;
	}

	public function getApplicationType()
	{
		return $this->application_type;
	}

	public function getAllowedOrigins()
	{
		return $this->allowed_origins;
	}

	public function getAllowedReturnUris()
	{
		return $this->allowed_return_uris;
	}

	public function toJSON()
	{
		return '{}';
	}

	public function fromJSON($json)
	{

	}
}
<?php
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

namespace libs\oauth2;

/**
 * Class OAuth2WWWAuthenticateErrorResponse
 * http://tools.ietf.org/html/rfc6750#section-3
 * @package oauth2\responses
 */
class OAuth2WWWAuthenticateErrorResponse extends OAuth2DirectResponse {

    private $realm;
    private $error;
    private $error_description;
    private $scope;
    private $http_error;

    public function __construct($realm, $error, $error_description, $scope, $http_error){
        parent::__construct($http_error, self::DirectResponseContentType);
        $this->realm             = $realm;
        $this->error             = $error;
        $this->error_description = $error_description;
        $this->scope             = $scope;
        $this->http_error        = $http_error;
    }

    public function getWWWAuthenticateHeaderValue(){
        $value=sprintf('Bearer realm="%s"',$this->realm);
        $value=$value.sprintf(', error="%s"',$this->error);
        $value=$value.sprintf(', error_description="%s"',$this->error_description);
        if(!is_null($this->scope))
            $value=$value.sprintf(', scope="%s"',$this->scope);
        return $value;
    }


    public function getContent()
    {
        $content = array(
            'error' => $this->error,
            'error_description' => $this->error_description
        );
        if(!is_null($this->scope))
            $content['scope'] = $this->scope;

        return $content;
    }

    public function getType()
    {
        return null;
    }
}
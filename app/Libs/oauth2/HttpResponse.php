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


abstract class HttpResponse extends HttpMessage
{
    const HttpOkResponse = 200;
    const HttpErrorResponse = 400;

    protected $http_code;
    protected $content_type;

    public function __construct($http_code, $content_type)
    {
        $this->http_code    = $http_code;
        $this->content_type = $content_type;
    }

    abstract public function getContent();

    public function getHttpCode()
    {
        return $this->http_code;
    }

    protected function setHttpCode($http_code)
    {
        $this->http_code = $http_code;
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    abstract public function getType();

    public function addParam($name, $value)
    {
        $this[$name] = $value;
    }
}
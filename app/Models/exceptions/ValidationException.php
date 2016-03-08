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

namespace models\exceptions;

use Exception;

/**
 * Class ValidationException
 * @package models\exceptions
 */
class ValidationException extends Exception
{
    private $messages;

    public function __construct($message= '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    public function getMessages()
    {
        $this->messages;
    }
}
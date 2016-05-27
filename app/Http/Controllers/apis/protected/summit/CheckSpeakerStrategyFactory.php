<?php namespace App\Http\Controllers;
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

use models\oauth2\IResourceServerContext;

/**
 * Class CheckSpeakerStrategyFactory
 * @package App\Http\Controllers
 */
final class CheckSpeakerStrategyFactory
{
    const Me  = 'me';
    const Own = 'own';

    /**
     * @param string $type
     * @param IResourceServerContext $resource_server_context
     * @return ICheckSpeakerStrategy|null
     */
    public static function build($type, IResourceServerContext $resource_server_context){
        $strategy = null;
        switch(strtolower($type))
        {
            case 'me':
                $strategy = new CheckMeSpeakerStrategy($resource_server_context);
                break;
            default:
                throw new \InvalidArgumentException('not recognized type!');
                break;
        }
        return $strategy;
    }
}
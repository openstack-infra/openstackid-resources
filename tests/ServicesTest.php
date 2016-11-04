<?php

/**
 * Copyright 2016 OpenStack Foundation
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
class ServicesTest extends TestCase
{
    public function testAccessTokenService(){
        $cache          = App::make('libs\utils\ICacheService');
        $token_value    = 'saotdCVPDuLuI6m';
        $cache_lifetime = 300;
        $token_info     = [
            'access_token' => $token_value,
            'scope' => '',
            'client_id' => '',
            'audience' => '',
            'user_id' => '',
            'user_external_id' => '',
            'expires_in' => 10,
            'application_type' => '',
            'allowed_return_uris' => '',
            'allowed_origins'=> ''
        ];
        $cache->storeHash(md5($token_value), $token_info, $cache_lifetime );
        sleep(10);
        $service = App::make('App\Models\ResourceServer\IAccessTokenService');
        $service->get($token_value);
    }
}
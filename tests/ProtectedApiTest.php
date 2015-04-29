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
use Illuminate\Support\Facades\App;
use models\resource_server\IAccessTokenService;
use \models\oauth2\AccessToken;
use Illuminate\Support\Facades\Config;

/**
 * Class AccessTokenServiceStub
 */
class AccessTokenServiceStub implements IAccessTokenService {

    /**
     * @param string $token_value
     * @return AccessToken
     * @throws \libs\oauth2\OAuth2InvalidIntrospectionResponse
     */
    public function get($token_value)
    {
        $url    = Config::get('app.url');
        $parts  = @parse_url($url);
        $realm  = $parts['host'];

        $scopes = array(
            $url.'/public-clouds/read',
            $url.'/private-clouds/read',
            $url.'/consultants/read',
        );

        return AccessToken::createFromParams('123456789',implode(' ', $scopes),'1',$realm,'1',3600,'','','');
    }
}

/**
 * Class ProtectedApiTest
 */
abstract class ProtectedApiTest extends TestCase {

    /**
     * @var string
     */
    protected $access_token;

    public function createApplication(){
        $app = parent::createApplication();
        App::singleton('models\resource_server\IAccessTokenService','AccessTokenServiceStub');
        return $app;
    }

    public function setUp()
    {
        $this->access_token = '123456789';
        parent::setUp();
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }
}
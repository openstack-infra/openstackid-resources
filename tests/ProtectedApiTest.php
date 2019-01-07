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
use Illuminate\Support\Facades\Config;
use models\oauth2\AccessToken;
use App\Models\ResourceServer\IAccessTokenService;
use App\Security\SummitScopes;
use App\Security\OrganizationScopes;
use App\Security\MemberScopes;
/**
 * Class AccessTokenServiceStub
 */
class AccessTokenServiceStub implements IAccessTokenService
{

    /**
     * @param string $token_value
     * @return AccessToken
     * @throws \libs\oauth2\OAuth2InvalidIntrospectionResponse
     */
    public function get($token_value)
    {
        $url   = Config::get('app.url');
        $parts = @parse_url($url);
        $realm = $parts['host'];

        $scopes = array(
            $url . '/public-clouds/read',
            $url . '/private-clouds/read',
            $url . '/consultants/read',
            $url . '/summits/read',
            $url . '/summits/read/all',
            $url . '/summits/write',
            $url . '/summits/write-event',
            $url . '/summits/publish-event',
            $url . '/summits/delete-event',
            $url . '/summits/read-external-orders',
            $url . '/summits/confirm-external-orders',
            $url . '/summits/write-videos',
            $url . '/me/read',
            $url . '/summits/read-notifications',
            $url . '/members/read',
            $url . '/members/read/me',
            $url . '/members/invitations/read',
            $url . '/members/invitations/write',
            $url . '/teams/read',
            $url . '/teams/write',
            $url . '/me/summits/events/favorites/add',
            $url . '/me/summits/events/favorites/delete',
            sprintf(SummitScopes::WriteSpeakersData, $url),
            sprintf(SummitScopes::WriteMySpeakersData, $url),
            sprintf(SummitScopes::WriteAttendeesData, $url),
            sprintf(MemberScopes::WriteMemberData, $url),
            sprintf(MemberScopes::WriteMyMemberData, $url),
            sprintf(SummitScopes::WritePromoCodeData, $url),
            sprintf(OrganizationScopes::WriteOrganizationData, $url),
            sprintf(OrganizationScopes::ReadOrganizationData, $url),
        );

        return AccessToken::createFromParams('123456789', implode(' ', $scopes), '1', $realm, '1','11624', 3600, 'WEB_APPLICATION', '', '');
    }
}

class AccessTokenServiceStub2 implements IAccessTokenService
{

    /**
     * @param string $token_value
     * @return AccessToken
     * @throws \libs\oauth2\OAuth2InvalidIntrospectionResponse
     */
    public function get($token_value)
    {
        $url = Config::get('app.url');
        $parts = @parse_url($url);
        $realm = $parts['host'];

        $scopes = array(
            $url . '/public-clouds/read',
            $url . '/private-clouds/read',
            $url . '/consultants/read',
            $url . '/summits/read',
            $url . '/summits/read/all',
            $url . '/summits/write',
            $url . '/summits/write-event',
            $url . '/summits/publish-event',
            $url . '/summits/delete-event',
            $url . '/summits/read-external-orders',
            $url . '/summits/confirm-external-orders',
            $url . '/summits/write-videos',
            $url . '/summits/write-videos',
            $url . '/me/read',
            $url . '/summits/read-notifications',
            $url . '/members/read',
            $url . '/members/read/me',
            $url . '/members/invitations/read',
            $url . '/members/invitations/write',
            $url . '/teams/read',
            $url . '/teams/write',
            $url . '/me/summits/events/favorites/add',
            $url . '/me/summits/events/favorites/delete',
            sprintf(SummitScopes::WriteSpeakersData, $url),
            sprintf(SummitScopes::WriteMySpeakersData, $url),
            sprintf(SummitScopes::WriteAttendeesData, $url),
            sprintf(Mem::WriteMemberData, $url),
            sprintf(SummitScopes::WritePromoCodeData, $url),
            sprintf(OrganizationScopes::WriteOrganizationData, $url),
            sprintf(OrganizationScopes::ReadOrganizationData, $url),
        );

        return AccessToken::createFromParams('123456789', implode(' ', $scopes), '1', $realm, null,null, 3600, 'SERVICE', '', '');
    }
}
/**
 * Class ProtectedApiTest
 */
abstract class ProtectedApiTest extends \Tests\BrowserKitTestCase
{

    /**
     * @var string
     */
    protected $access_token;

    public function createApplication()
    {
        $app = parent::createApplication();
        App::singleton('App\Models\ResourceServer\IAccessTokenService', 'AccessTokenServiceStub');

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
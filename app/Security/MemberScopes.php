<?php namespace App\Security;
/**
 * Copyright 2018 OpenStack Foundation
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
 * Class MemberScopes
 * @package App\Security
 */
final class MemberScopes
{
    const ReadMemberData    = '%s/members/read';

    const ReadMyMemberData    = '%s/members/read/me';

    const WriteMemberData    = '%s/members/write';

    const WriteMyMemberData    = '%s/members/write/me';
}
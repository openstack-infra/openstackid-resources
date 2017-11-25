<?php
/**
 * Copyright 2017 OpenStack Foundation
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

return
[
    'ssh_user'             => env('SSH_USER', ''),
    'ssh_public_key'       => env('SSH_PUBLIC_KEY', ''),
    'ssh_private_key'      => env('SSH_PRIVATE_KEY', ''),
    'scp_host'             => env('SCP_HOST', ''),
    'scp_remote_base_path' => env('SCP_REMOTE_BASE_PATH', ''),
];
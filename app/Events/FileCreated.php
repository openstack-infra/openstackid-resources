<?php namespace App\Events;
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

use Illuminate\Queue\SerializesModels;

/**
 * Class FileCreated
 * @package App\Events
 */
final class FileCreated extends Event
{
    use SerializesModels;

    /**
     * @var string
     */
    private $local_path;

    /**
     * @var string
     */
    private $file_name;

    /**
     * @var string
     */
    private $folder_name;


    public function __construct($local_path, $file_name, $folder_name)
    {
        $this->local_path  = $local_path;
        $this->file_name   = $file_name;
        $this->folder_name = $folder_name;
    }

    /**
     * @return string
     */
    public function getLocalPath()
    {
        return $this->local_path;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->folder_name;
    }


}
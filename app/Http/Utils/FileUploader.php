<?php namespace App\Http\Utils;
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
use App\Services\Model\IFolderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use models\main\File;
/**
 * Class FileUploader
 * @package App\Http\Utils
 */
final class FileUploader
{
    /**
     * @var IFolderService
     */
    private $folder_service;

    /**
     * @var IBucket
     */
    private $bucket;

    /**
     * FileUploader constructor.
     * @param IFolderService $folder_service
     */
    public function __construct(IFolderService $folder_service, IBucket $bucket){
        $this->folder_service = $folder_service;
        $this->bucket = $bucket;
    }

    /**
     * @param UploadedFile $file
     * @param string $folder_name
     * @param bool $is_image
     * @return File
     */
    public function build(UploadedFile $file, $folder_name, $is_image = false){
        $attachment = new File();
        try {

            $local_path = Storage::putFileAs(sprintf('/public/%s', $folder_name), $file, $file->getClientOriginalName());
            $folder = $this->folder_service->findOrMake($folder_name);

            $attachment->setParent($folder);
            $attachment->setName($file->getClientOriginalName());
            $attachment->setFilename(sprintf("assets/%s/%s", $folder_name, $file->getClientOriginalName()));
            $attachment->setTitle(str_replace(array('-', '_'), ' ', preg_replace('/\.[^.]+$/', '', $file->getClientOriginalName())));
            $attachment->setShowInSearch(true);
            if ($is_image) $attachment->setImage();
            $this->bucket->put($attachment, $local_path);
            $attachment->setCloudMeta('LastPut', time());
            $attachment->setCloudStatus('Live');
            $attachment->setCloudSize(filesize($local_path));

        }
        catch (\Exception $ex){
            Log::error($ex);
            $attachment->setCloudStatu('Error');
        }
        return $attachment;
    }
}
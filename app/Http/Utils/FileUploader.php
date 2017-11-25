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
use App\Events\FileCreated;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use models\main\File;
use models\main\IFolderRepository;

/**
 * Class FileUploader
 * @package App\Http\Utils
 */
final class FileUploader
{
    /**
     * @var IFolderRepository
     */
    private $folder_repository;

    public function __construct(IFolderRepository $folder_repository){
        $this->folder_repository = $folder_repository;
    }

    /**
     * @param UploadedFile $file
     * @param $folder_name
     * @return File
     */
    public function build(UploadedFile $file, $folder_name){
        $attachment = new File();
        $local_path = Storage::putFileAs(sprintf('/public/%s', $folder_name), $file, $file->getClientOriginalName());
        $folder     = $this->folder_repository->getFolderByName($folder_name);
        $attachment->setParent($folder);
        $attachment->setName($file->getClientOriginalName());
        $attachment->setFilename(sprintf("assets/%s/%s",$folder_name, $file->getClientOriginalName()));
        $attachment->setTitle(str_replace(array('-','_'),' ', preg_replace('/\.[^.]+$/', '', $file->getClientOriginalName())));
        $attachment->setShowInSearch(true);
        Event::fire(new FileCreated($local_path, $file->getClientOriginalName(), $folder_name));
        return $attachment;
    }
}
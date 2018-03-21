<?php namespace App\Services\Model;
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
use libs\utils\ITransactionService;
use models\main\File;
use models\main\IFolderRepository;
/**
 * Class FolderService
 * @package App\Services\Model
 */
final class FolderService
    extends AbstractService
    implements IFolderService
{

    /**
     * @var IFolderRepository
     */
    private $folder_repository;

    /**
     * FolderService constructor.
     * @param IFolderRepository $folder_repository
     * @param ITransactionService $tx_service
     */
    public function __construct(IFolderRepository $folder_repository, ITransactionService $tx_service)
    {
        parent::__construct($tx_service);
        $this->folder_repository = $folder_repository;
    }

    /**
     * @param string $folder_name
     * @return File
     */
    public function findOrMake($folder_name)
    {
        return $this->tx_service->transaction(function() use($folder_name){

           $folder = $this->folder_repository->getFolderByFileName($folder_name);
           if(!is_null($folder)) return $folder;

           // create it
            $folder_path = preg_replace('/^\/?(.*)\/?$/', '$1', $folder_name);
            $parts       = explode("/", $folder_path);
            $parent      = null;
            $item        = null;
            $file_name   = null;
            foreach($parts as $part) {
                if(!$part) continue; // happens for paths with a trailing slash
                if(!empty($file_name))
                    $file_name .= '/';
                $file_name .= $part;
                $item = is_null($parent) ?
                    $this->folder_repository->getFolderByName($part) :
                    $this->folder_repository->getFolderByNameAndParent($part, $parent);

                if(!$item) {
                    $item = new File();
                    if(!is_null($parent)){
                        $item->setParent($parent);
                    }
                    else{
                        $file_name = 'assets/'.$file_name;
                    }
                    $item->setFolder();
                    $item->setName($part);
                    $item->setTitle($part);
                    $item->setFilename($file_name);
                    $this->folder_repository->add($item);
                }
                $parent = $item;
            }

            return $item;
        });
    }
}
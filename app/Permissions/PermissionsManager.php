<?php namespace App\Permissions;
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
use models\main\Member;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
/**
 * Class PermissionsManager
 * @package App\Permissions
 */
final class PermissionsManager implements IPermissionsManager
{
    /**
     * @var array
     */
    private $config = [];

    const AllFieldsWillCard = "__all__";

    private function loadConfiguration(){
        if(count($this->config) > 0 ) return;

        $path = sprintf("%s/permissions.yml", dirname(__FILE__));
        $yaml = Yaml::parse(file_get_contents($path));
        if(!is_null($yaml) && count($yaml))
        {
            foreach($yaml as $entities => $entity){
                foreach ($entity as $permissions_per_role){
                    $entity_name = array_keys($entity)[0];
                    $entity_config = [];
                    foreach ($permissions_per_role as $permission_per_role){
                        $role = array_keys($permission_per_role)[0];
                        $entity_config[$role] = [];
                        foreach($permission_per_role as $permissions){
                            foreach($permissions as $permission)
                                $entity_config[$role][] = $permission;
                        }
                    }
                    $this->config[$entity_name] = $entity_config;
                }
            }}

    }
    /**
     * @param Member $current_user
     * @param string $entity_name
     * @param array $data
     * @return bool
     */
    public function canEditFields(Member $current_user, $entity_name, array $data){
        $this->loadConfiguration();
        $groups = $current_user->getGroupsCodes();

        if(!isset($this->config[$entity_name]))
            throw new \InvalidArgumentException(sprintf("entity %s does not has a configuration set", $entity_name));

        $entity_config = $this->config[$entity_name];

        foreach($groups as $group_code) {
            if(!isset($entity_config[$group_code])) continue;
            $allowed_fields = $entity_config[$group_code];
            if(count($allowed_fields) == 0) return false;
            if(in_array(self::AllFieldsWillCard, $allowed_fields)) return true;
            $fields = array_keys($data);
            $diff = array_diff($fields, $allowed_fields);
            if(count($diff) > 0)
                return false;
            return true;
        }
        return false;
    }
}
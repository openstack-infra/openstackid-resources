<?php namespace libs\utils;
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

/**
* Interface ICacheService
* @package utils\services
*/
interface ICacheService
{

	/**
	* Determine if a key exists
	* @param $key
	* @return bool
	*/
	public function exists($key);

	/**
	* Delete a key
	* @param $key
	* @return mixed
	*/
	public function delete($key);

	/**
	* Delete a key
	* @param array $keys
	* @return mixed
	*/
	public function deleteArray(array $keys);

	/**
	* retrieves a hash
	* @param $name
	* @param array $values
	* @return array
	*/
	public function getHash($name, array $values);

	/**
	* save a hash, with an optional time to live
	* @param $name
	* @param array $values
	* @param int $ttl
	* @return mixed
	*/
	public function storeHash($name, array $values, $ttl = 0);

	/**
	* @param $counter_name
	* @param int $ttl
	* @return mixed
	*/
	public function incCounter($counter_name, $ttl = 0);

	/**
	* @param $counter_name
	* @return mixed
	*/
	public function incCounterIfExists($counter_name);

	public function addMemberSet($set_name, $member);

	public function deleteMemberSet($set_name, $member);

	public function getSet($set_name);

	public function getSingleValue($key);

	/**
	* @param $key
	* @param $value
	* @param int $ttl
	* @return mixed
	*/
	public function setSingleValue($key, $value, $ttl = 0);

	/**
	* adds a single value if given keys does not exists, with an optional
	* time to live
	* @param $key
	* @param $value
	* @param int $ttl
	* @return mixed
	*/
	public function addSingleValue($key, $value, $ttl = 0);

	/**
	* Set time to live to a given key
	* @param $key
	* @param $ttl
	* @return mixed
	*/
	public function setKeyExpiration($key, $ttl);

	public function boot();

	/**Returns the remaining time to live of a key that has a timeout.
	* @param string $key
	* @return int
	*/
	public function ttl($key);
}
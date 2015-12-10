<?php
namespace DirectMailTeam\DirectMail;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Bernhard Kraft <kraftb@think-open.at>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * @author		Bernhard Kraft <kraftb@think-open.at>
 *
 * @package 	TYPO3
 * @subpackage 	tx_directmail
 * @version 	$Id:$
 */


/**
 * Storage class which can hold other "ObjectStorage", "QueryResult" objects or Arrays
 * additionally to plain objects. When iterating over this object it is not defined
 * whether the order will be the same as the order in which the objects got added.
 *
 * As the contained objects can use the same index for different objects this class does
 * not implement the "ArrayAccess" interface. This means that this object could contain
 * the same index multiple times - in different containers.
 *
 * So as this class does not implement \ArrayAccess the only way to add new objects is using
 * the "add" (or "attach") methods. The only way to retrieve objects is to iterate over
 * them.
 *
 * So the purpose of this class is surely to only be a container for QueryResults or other
 * ObjectStorage classes being accumulated by queries to repositories, etc.
 */
class NestedStorage implements \Countable, \Iterator {

	/**
	 * This field is only needed to make debugging easier:
	 * If you call current() on a class that implements Iterator, PHP will return the first field of the object
	 * instead of calling the current() method of the interface.
	 * We use this unusual behavior of PHP to return the warning below in this case.
	 *
	 * @var string
	 */
	private $warning = 'You should never see this warning. If you do, you probably used PHP array functions like current() on the TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage. To retrieve the first result, you can use the rewind() and current() methods.';

	/**
	 * An array holding the objects and the stored information. The key of the array items is the
	 * spl_object_hash of the given object.
	 * @see \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 *
	 * @var array
	 */
	protected $storage = array();

	/**
	 * An array holding the keys of other containers (ObjectStorage, QueryResult, NestedStorage, etc.)
	 * which got added to $storage. This array is used so methods like "contains" can more quickly
	 * check sub-storages for the existence of an object.
	 *
	 * @var array
	 */
	protected $containerIndex = array();

	/**
	 * Rewinds the iterator and all containers to the first storage element.
	 *
	 * @return void
	 */
	public function rewind() {
		reset($this->storage);
		foreach ($this->containerIndex as $hash) {
			$this->storage[$hash]->rewind();
		}
	}

	/**
	 * Checks if the array pointer of the storage points to a valid position.
	 *
	 * @return boolean
	 */
	public function valid() {
		$current = current($this->storage);
		if ($current === FALSE) {
			return FALSE;
		}
		if ($this->isContainer($current)) {
			return $current->valid();
		}
		return TRUE;
	}

	/**
	 * Returns the index at which the iterator currently is.
	 *
	 * This is different from the SplObjectStorage as the key in this implementation is the object hash (string).
	 *
	 * @return string The index corresponding to the position of the iterator.
	 */
	public function key() {
		$key = key($this->storage);
		$current = current($this->storage);
		if ($this->isContainer($current)) {
			$key .= '.' . $current->key();
		}
		return $key;
	}

	/**
	 * Returns the current storage entry.
	 *
	 * @return object The object at the current iterator position.
	 */
	public function current() {
		$item = current($this->storage);
		if ($this->isContainer($item)) {
			return $item->current();
		}
		return $item;
	}

	/**
	 * Moves to the next entry.
	 *
	 * @return void
	 */
	public function next() {
		$current = current($this->storage);
		if ($this->isContainer($current)) {
			$current->next();
		} else {
			next($this->storage);
		}
	}

	/**
	 * Returns the number of objects in the storage.
	 *
	 * @return integer The number of objects in the storage.
	 */
	public function count() {
		$count = count($this->storage) - count($this->containerIndex);
		foreach ($this->containerIndex as $hash) {
			$count += count($this->storage[$hash]);
		}
		return $count;
	}

	/**
	 * Adds an object in the storage, and optionaly associate it to some data.
	 *
	 * @param object $object The object to add.
	 * @param mixed $information The data to associate with the object.
	 * @return void
	 */
	public function attach($object) {
		$objectHash = spl_object_hash($object);
		if (is_array($object) && count($object)) {
			// When an array gets passed only an \ArrayIterator will get added to the storage instead of the real object.
			// PHPs memory management will take care not to remove the associated ArrayObject as long as the iterator exists.
			$arrayObject = new \ArrayObject($object);
			$this->storage[$objectHash] = $arrayObject->getIterator();
			$this->containerIndex[$objectHash] = $objectHash;
		} else {
			if ($this->isContainer($object)) {
				if ($object->count()) {
					// Only add containers which contain at least one element
					$this->storage[$objectHash] = $object;
					$this->storage[$objectHash]->rewind();
					$this->containerIndex[$objectHash] = $objectHash;
				}
			} else {
				$this->storage[$objectHash] = $object;
			}
		}
	}

	/**
	 * Dummy method to avoid serialization.
	 *
	 * @throws \RuntimeException
	 * @return void
	 */
	public function serialize() {
		throw new \RuntimeException('An ObjectStorage instance cannot be serialized.', 1267700868);
	}

	/**
	 * Dummy method to avoid unserialization.
	 *
	 * @param string $serialized
	 * @throws \RuntimeException
	 * @return void
	 */
	public function unserialize($serialized) {
		throw new \RuntimeException('A ObjectStorage instance cannot be unserialized.', 1267700870);
	}

	/**
	 * Determine whether the passed object can get seen as container.
	 *
	 * @param object $storage
	 * @return boolean Returns TRUE when the passed object can get seen as container
	 */
	protected function isContainer($object) {
		if ( ($object instanceof \Countable) && ($object instanceof \Iterator) ) {
			return TRUE;
		}
		return FALSE;
	}

}


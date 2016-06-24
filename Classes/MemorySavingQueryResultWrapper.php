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
 * This class implements a wrapper for "QueryResult" objects. It allows to iterate
 * over the retrieved objects in a memory saving way. It does so by modifying the
 * decorated QueryResult object and only retrieving a limited amount of records at
 * a single time.
 */
class MemorySavingQueryResultWrapper implements \Countable, \Iterator {

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
	 * The amount of records retrieved at a single time
	 *
	 * @const integer
	 */
	const partitionSize = 100;

	/**
	 * The decorated QueryResult object
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $queryResult = NULL;

	/**
	 * The query to the current partition
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $currentQueryResult = NULL;

	/**
	 * The index of the partition which is currently served
	 *
	 * @var array
	 */
	protected $partitionIndex = 0;

	public function __construct(\TYPO3\CMS\Extbase\Persistence\QueryResultInterface $queryResult) {
		$this->queryResult = $queryResult;
		$this->next();
	}

	/**
	 * Rewinds the iterator and all containers to the first storage element.
	 *
	 * @return void
	 */
	public function rewind() {
		$this->currentQueryResult = NULL;
		$this->partitionIndex = 0;
		$this->next();
	}

	/**
	 * Checks if the current element is valid.
	 *
	 * @return boolean
	 */
	public function valid() {
		if ($this->currentQueryResult === NULL) {
			return FALSE;
		}
		return $this->currentQueryResult->valid();
	}

	/**
	 * Returns the index at which the iterator currently is.
	 *
	 * @return string The index corresponding to the position of the iterator.
	 */
	public function key() {
		return $this->currentQueryResult->key();
	}

	/**
	 * Returns the current storage entry.
	 *
	 * @return object The object at the current iterator position.
	 */
	public function current() {
		return $this->currentQueryResult->current();
	}

	/**
	 * Moves to the next entry.
	 *
	 * @return void
	 */
	public function next() {
		if ($this->currentQueryResult !== NULL) {
			$this->currentQueryResult->next();
			if ($this->currentQueryResult->valid()) {
				return;
			}
			$this->currentQueryResult = NULL;
			$this->partitionIndex++;
		}
		$tmpQuery = $this->queryResult->getQuery();
		$tmpQuery->setLimit(static::partitionSize);
		$tmpQuery->setOffset(static::partitionSize * $this->partitionIndex);
		$this->currentQueryResult = $tmpQuery->execute();
	}

	/**
	 * Returns the total number of results.
	 *
	 * @return integer The number of objects in the storage.
	 */
	public function count() {
		return $this->queryResult->count();
	}

	/**
	 * Dummy method to avoid serialization.
	 *
	 * @throws \RuntimeException
	 * @return void
	 */
	public function serialize() {
		throw new \RuntimeException('An MemorySavingQueryResultWrapper instance cannot be serialized.', 1450210645);
	}

	/**
	 * Dummy method to avoid unserialization.
	 *
	 * @param string $serialized
	 * @throws \RuntimeException
	 * @return void
	 */
	public function unserialize($serialized) {
		throw new \RuntimeException('A MemorySavingQueryResultWrapper instance cannot be unserialized.', 1450210646);
	}

}


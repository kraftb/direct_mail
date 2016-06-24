<?php
namespace DirectMailTeam\DirectMail\Service;

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


use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Base class for services
 */
class ServiceBase {

	/**
	 * A prefix for language keys
	 *
	 * @const string
	 */
	const languageKeyPrefix = '';

	/**
	 * Format for date strings
	 *
	 * @const string
	 */
	const dateFormat = '%H:%M:%S %d-%m-%Y';

	/**
	 * An object manager instance
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * The logger instance
	 *
	 * @var \DirectMailTeam\DirectMail\Logger
	 * @inject
	 */
	protected $logger = NULL;

	/**
	 * An instance of the extbase persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager = NULL;

	/**
	 * Repository for mail jobs (sys_dmail records)
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\MailJobRepository
	 * @inject
	 */
	protected $mailJobRepository = NULL;

	/**
	 * Repository for mails to be sent
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\SendQueueRepository
	 * @inject
	 */
	protected $sendQueueRepository = NULL;

	/**
	 * Returns the translation for the passed local lang key
	 *
	 * @param string $key: The language key to retrieve
	 * @return string The translated value for the passed key
	 */
	protected function translate($key) {
		return LocalizationUtility::translate(static::languageFile . static::languageKeyPrefix . $key);
	}

	/**
	 * Returns a readable form of a duration in seconds.
	 *
	 * @param integer $duration: A duration in seconds
	 * @return string A string in the form "Xh Ym Zs"
	 */
	protected function formatDuration($duration) {
		$hours = 0;
		$minutes = 0;
		if ($duration >= 3600) {
			$hours = (int)($duration / 3600);
			$duration = $duration % 3600;
		}
		if ($duration >= 60) {
			$minutes = (int)($duration / 60);
			$duration = $duration % 60;
		}
		return ($hours ? $hours . 'h ': '') . ($minutes ? $minutes . 'm ': '') . $duration . 's';
	}

	/**
	 * Formats the passed date.
	 *
	 * @param integer $time: A timestamp
	 * @return string The passed date/time in a format defined by a class constant
	 */
	protected function formatDate($time) {
		return strftime(static::dateFormat, $time);
	}

	/**
	 * Formats the passed number as kByte, MB, GB
	 *
	 * @param integer $bytes: A byte value
	 * @return string The bytes formated into k/M/G units
	 */
	protected function formatBytes($bytes) {
		if ($bytes > 1024*1024*1024) {
			$bytes = $bytes/(float)(1024*1024*1024);
			return sprintf('%.1f GB', $bytes);
		} elseif ($bytes > 1024*1024) {
			$bytes = $bytes/(float)(1024*1024);
			return sprintf('%.1f MB', $bytes);
		} elseif ($bytes > 1024) {
			$bytes = $bytes/(float)(1024);
			return sprintf('%.1f kB', $bytes);
		}
		return $bytes. ' bytes';
	}

	/**
	 * Returns current date.
	 *
	 * @return string The current date in a format defined by a class constant
	 */
	protected function getCurrentDate() {
		return $this->formatDate(time());
	}

}

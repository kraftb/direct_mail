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


use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Logger class
 */
class Logger implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * This variable cummulates all messages being issued during one invocation
	 *
	 * @var array
	 */
	protected $logArray = array();

	/**
	 * This method will store the passed message in the devLog (if enabled)
	 * and add the message to the logArray
	 *
	 * @param string $message: The message to log
	 * @param string $key: A key for the logged message. Usually will be "direct_mail"
	 * @return void
	 */
	public function log($message, $key = 'direct_mail') {
		if (func_num_args() > 2) {
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			$message = vsprintf($message, $args);
		}
		if (TYPO3_DLOG) {
			GeneralUtility::devLog($message, $key);
		}
		$this->logArray[] = array('message' => $message, 'key' => $key);
	}

	/**
	 * Returns all messages logged until now
	 *
	 * @return array All message logged until now. Only the messages, not the keys.
	 */
	public function getLogMessages() {
		$result = array();
		foreach ($this->logArray as $logMessage) {
			$result[] = $logMessage['message'];
		}
		return $result;
	}

}


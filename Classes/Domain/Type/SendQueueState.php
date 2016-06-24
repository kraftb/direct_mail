<?php
namespace DirectMailTeam\DirectMail\Domain\Type;

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
 * Data type for send queue state
 */
class SendQueueState extends \TYPO3\CMS\Core\Type\Enumeration {


	const __default = self::QUEUED;

	/**
	 * Constants reflecting the send queue states
	 */
	const QUEUED = 0;
	const PROCESSING = 1;
	const SENT = 2;

	/**
	 * Constructor for this enumeration type
	 *
	 * @param mixed $state
	 */
	public function __construct($state = NULL) {
		if ($state !== NULL) {
			$state = (int)$state;
		}

		parent::__construct($state);
	}

	/**
	 * Returns the next state of the passed one
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Type\SendQueueState $state: The state for which to return the next one
	 * @return \DirectMailTeam\DirectMail\Domain\Type\SendQueueState The next state of $state
	 */
	public function nextState(\DirectMailTeam\DirectMail\Domain\Type\SendQueueState $state = NULL) {
		$currentClass = get_called_class();
		if ($state === NULL) {
			$stateValue = (int)((string)$this);
		} elseif ($state instanceof \DirectMailTeam\DirectMail\Domain\Type\SendQueueState) {
			$stateValue = (int)((string)$state);
		} else {
			$stateValue = (int)$state;
		}
		switch ($stateValue) {
			case self::QUEUED:
				return new $currentClass(self::PROCESSING);

			case self::PROCESSING:
				return new $currentClass(self::SENT);

			default:
				throw new \Exception('No next state');
		}
	}

	/**
	 * Returns the previous state of the passed one
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Type\SendQueueState $state: The state for which to return the previous one
	 * @return \DirectMailTeam\DirectMail\Domain\Type\SendQueueState The previous state of $state
	 */
	public function previousState(\DirectMailTeam\DirectMail\Domain\Type\SendQueueState $state = NULL) {
		$currentClass = get_called_class();
		if ($state === NULL) {
			$stateValue = (int)((string)$this);
		} elseif ($state instanceof \DirectMailTeam\DirectMail\Domain\Type\SendQueueState) {
			$stateValue = (int)((string)$state);
		} else {
			$stateValue = (int)$state;
		}
		switch ($stateValue) {
			case self::PROCESSING:
				return new $currentClass(self::QUEUED);

			case self::SENT:
				return new $currentClass(self::PROCESSING);

			default:
				throw new \Exception('No previous state');
		}
	}

}


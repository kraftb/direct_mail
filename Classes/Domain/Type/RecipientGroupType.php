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
 * Data type for type of a recipient group
 */
class RecipientGroupType extends \TYPO3\CMS\Core\Type\Enumeration {


	const __default = self::DEFAULT_TYPE;

	const FROM_PAGES = 0;
	const PLAIN_LIST = 1;
	const STATIC_LIST = 2;
	const SPECIAL_QUERY = 3;
	const OTHER_GROUPS = 4;

	/**
	 * Constants reflecting the recipient group types
	 */
	const DEFAULT_TYPE = self::FROM_PAGES;

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

}


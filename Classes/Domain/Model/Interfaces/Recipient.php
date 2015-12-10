<?php
namespace DirectMailTeam\DirectMail\Domain\Model\Interfaces;

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
 * Domain model interface for a direct mail recipient
 */
interface Recipient {

	/**
	 * Returns the email of this recipient
	 *
	 * @return string The email of this recipient
	 */
	public function getEmail();

	/**
	 * Sets the email of this recipient
	 *
	 * @param string The email of this recipient
	 * @return void
	 */
	public function setEmail($email);

	/**
	 * Returns the first name of this recipient
	 *
	 * @return string The first name of this recipient
	 */
	public function getFirstName();

	/**
	 * Sets the first name of this recipient
	 *
	 * @param string The first name of this recipient
	 * @return void
	 */
	public function setFirstName($firstName);

	/**
	 * Returns the last name of this recipient
	 *
	 * @return string The last name of this recipient
	 */
	public function getLastName();

	/**
	 * Sets the last name of this recipient
	 *
	 * @param string The last name of this recipient
	 * @return void
	 */
	public function setLastName($lastName);

	/**
	 * Returns the type of this recipient. Either "plain" for plain data source (CSV, XML, etc.)
	 * or the name of the table fo this recipient. Notice that the "getType" method is part of the
	 * Recipient interface while it is not mandatory to have a "setType" method.
	 *
	 * @return string The type of this recipient
	 */
	public function getType();

}


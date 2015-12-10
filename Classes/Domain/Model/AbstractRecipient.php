<?php
namespace DirectMailTeam\DirectMail\Domain\Model;

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
 * Abstract domain model for a recipient.
 */
abstract class AbstractRecipient extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity implements \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recpient {

	/**
	 * E-Mail of this recipient
	 *
	 * @var string
	 */
	protected $email = '';

	/**
	 * First name of this recipient
	 *
	 * @var string
	 */
	protected $firstName = '';

	/**
	 * Last name of this recipient
	 *
	 * @var string
	 */
	protected $lastName = '';

	/**
	 * The name of this recipient
	 *
	 * @var string
	 */
	protected $name = '';


	/**
	 * Returns the email of this recipient
	 *
	 * @return string The email of this recipient
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email of this recipient
	 *
	 * @param string The email of this recipient
	 * @return void
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Returns the first name of this recipient
	 *
	 * @return string The first name of this recipient
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * Sets the first name of this recipient
	 *
	 * @param string The first name of this recipient
	 * @return void
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
	}

	/**
	 * Returns the last name of this recipient
	 *
	 * @return string The last name of this recipient
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * Sets the last name of this recipient
	 *
	 * @param string The last name of this recipient
	 * @return void
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
	}

	/**
	 * Returns the name of this recipient
	 *
	 * @return string The name of this recipient
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name of this recipient
	 *
	 * @param string The name of this recipient
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

}



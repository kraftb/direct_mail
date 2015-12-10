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
 * Domain model for a "custom" direct mail recpient.l
 * Custom recipients are either "PLAINLIST" recipients or recipients in a table
 * other than tt_address or fe_users.
 */
class CustomRecipient extends \DirectMailTeam\DirectMail\Domain\Model\AbstractRecipient implements \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recpient {

	/**
	 * The type of this recipient
	 *
	 * @var string
	 */
	protected $type = 'plain';


	/**
	 * Returns the type of this recipient
	 *
	 * @return string The type of this recipient
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type of this recipient
	 *
	 * @param string The type of this recipient
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Uses a method "_getProperties" which is only intended for internal use. Maybe change
	 * this method to only return all applicable properties.
	 *
	 * @return array An array representation of this domain object for serialization using "json_encode"
	 */
	public function toArray() {
		return $this->_getProperties();
	}

}


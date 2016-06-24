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
 * Domain model for a direct mail recpient of type "address" (tt_address record)
 */
class Address extends \TYPO3\TtAddress\Domain\Model\Address implements \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient {

	const type = 'tt_address';

	/**
	 * The direct_mail extension adds a field "module_sys_dmail_category" to every tt_address record. This field
	 * contains the mail categories a recipient is subscribed to (via an MM relation).
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Category>
	 */
	protected $selectedCategories = NULL;
	
	/**
	 * Returns the categories the recipients has subscribed to
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Category> The categories the recipient has subscribed to
	 */
	public function getSelectedCatgories() {
		return $this->selectedCategories;
	}

	/**
	 * Sets the categories the recipient has subscribed to
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Category> $selectedCategories: The categories the recipient has subscribed to
	 * @return void
	 */
	public function setSelectedCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $selectedCategories) {
		$this->selectedCategories = $selectedCategories;
	}

	/**
	 * Returns the type of this recipient. Will be "tt_address" for "Address" domain model objects.
	 *
	 * @return string The type of this recipient
	 */
	public function getType() {
		return self::type;
	}

}


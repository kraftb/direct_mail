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
 * Domain model for a mail/subscription category
 */
class Category extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Date of last change of this category
	 *
	 * @var \DateTime
	 */
	protected $modificationDate = NULL;

	/**
	 * Creation date of this category
	 *
	 * @var \DateTime
	 */
	protected $creationDate = NULL;

	/**
	 * The name/title of this category
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Returns the date of last change for this category
	 *
	 * @return \DateTime The modification date of this category
	 */
	public function getModificationDate() {
		return $this->modificationDate;
	}

	/**
	 * Sets the date of last change for this category
	 *
	 * @param \DateTime $modificationDate: The modification date to set in this category object
	 * @return void
	 */
	public function setModificationDate(\DateTime $modificationDate) {
		$this->modificationDate = $modificationDate;
	}

	/**
	 * Returns the creation date of this category
	 *
	 * @return \DateTime The creation date of this category
	 */
	public function getCreationDate() {
		return $this->creationDate;
	}

	/**
	 * Sets the creation date for this category
	 *
	 * @param \DateTime $creationDate: The creation date to set
	 * @return void
	 */
	public function setCreationDate(\DateTime $creationDate) {
		$this->creationDate = $creationDate;
	}

	/**
	 * Returns the name of this category
	 *
	 * @return string The name of this category
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name for this category
	 *
	 * @param string $name: The name for this category
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

}


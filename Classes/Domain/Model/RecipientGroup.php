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
 * Domain model for a recipient group
 */
class RecipientGroup extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Date of last change of this entry
	 *
	 * @var \DateTime
	 */
	protected $modificationDate = NULL;

	/**
	 * The recipient table(s) for this recipient group
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Type\RecipientTableType>
	 */
	protected $recipientTables = NULL;

	/**
	 * The type of this recipient group
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Type\RecipientGroupType
	 */
	protected $type = NULL;

	/**
	 * The title of this recipient group
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * A description for this recipient group
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * A query for this recipient group if it is of type custom query
	 *
	 * @var string
	 */
	protected $query = '';

	/**
	 * A plain list of recipients
	 *
	 * @var string
	 */
	protected $plainRecipientList = '';

	/**
	 * Forma of plain recipient list
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Type\RecipientListFormat
	 */
	protected $plainRecipientListFormat = NULL;

	/**
	 * A list of other recipient groups if type is "OTHER_GROUPS"
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DirectMailTeam\DirectMail\Domain\Model\RecipientGroup>
	 */
	protected $mailGroups = NULL;

	/**
	 * Page from which to retrieve recipients
	 * @todo: Change to a "Page" domain model as soon as a common one is
	 * provided via the extbase extension or another quasi-standard
	 * extension.
	 *
	 * @var string
	 */
	protected $pages = '';

	/**
	 * Whether to include records from subpages of pages specified via "pages" property
	 *
	 * @var boolean
	 */
	protected $pagesRecursive = FALSE;

	/**
	 * The categories of which a recipient in this group must have selected at least one
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\MailCategory>
	 */
	protected $selectedCategories = NULL;

	/**
	 * Returns the date of last change for this entry
	 *
	 * @return \DateTime The modification date of this queue entry
	 */
	public function getModificationDate() {
		return $this->modificationDate;
	}

	/**
	 * Sets the date of last change for this entry
	 *
	 * @param \DateTime $modificationDate: The modification date to se tin this queue entry
	 * @return void
	 */
	public function setModificationDate(\DateTime $modificationDate) {
		$this->modificationDate = $modificationDate;
	}

	/**
	 * Returns the type of this recipient
	 *
	 * @return \DirectMailTeam\DirectMail\Domain\Type\RecipientGroupType The type of this recipient group
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type of this recipient
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Type\RecipientGroupType $type: The type for this recipient group
	 * @return void
	 */
	public function setType(\DirectMailTeam\DirectMail\Domain\Type\RecipientGroupType $type) {
		$this->type = $type;
	}

	/**
	 * Returns the title of this recipient group
	 *
	 * @return string The title of this recipient group
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title for this recipient group
	 *
	 * @param string $title: The title for this recipient group
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the description for this recipient group
	 *
	 * @return string The description for this recipient group
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description for this recipient group
	 *
	 * @param string $description: The description for this recipient group
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Returns the custom query for this recipient group
	 *
	 * @return string The custom query for this recipient group
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Sets the custom query for this recipient group
	 *
	 * @param string $query: The custom query for this recipient group
	 * @return void
	 */
	public function setQuery($query) {
		$this->query = $query;
	}

	/**
	 * Returns the pages from which to retrieve recipient records
	 *
	 * @return string List of pages from which to retrieve recipient records
	 */
	public function getPages() {
		return $this->pages;
	}

	/**
	 * Sets the pages from which to retrieve recipient records
	 *
	 * @param string $pages: The pages from which to retrieve recipient records
	 * @return void
	 */
	public function setPages($pages) {
		$this->pages = $pages;
	}

	/**
	 * Returns the "pages recursive" setting
	 *
	 * @return boolean When TRUE also subpages of "pages" should get checked for recipient records
	 */
	public function getPagesRecursive() {
		return $this->pagesRecursive;
	}

	/**
	 * Sets the "pages recursive" setting
	 *
	 * @param boolean $pagesRecursive: Whether to retrieve recipient records recursively from pages
	 * @return void
	 */
	public function setPagesRecursive($pagesRecursive) {
		$this->pagesRecursive = $pagesRecursive;
	}

	/**
	 * Returns the plain recipient list for this recipient group
	 *
	 * @return string The plain recipient list for this recipient group
	 */
	public function getPlainRecipientList() {
		return $this->plainRecipientList;
	}

	/**
	 * Sets the plain recipient list for this recipient group
	 *
	 * @param string $plainRecipientList: The plain recipient list for this recipient group
	 * @return void
	 */
	public function setPlainRecipientList($plainRecipientList) {
		$this->plainRecipientList = $plainRecipientList;
	}

	/**
	 * Returns the format of the plain recipient list
	 *
	 * @return \DirectMailTeam\DirectMail\Domain\Type\RecipientListFormat The format of the plain recipient list
	 */
	public function getPlainRecipientListFormat() {
		return $this->plainRecipientListFormat;
	}

	/**
	 * Sets the format of the plain recipient list
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Type\RecipientListFormat $plainRecipientListFormat: The format of the plain recipient list
	 * @return void
	 */
	public function setPlainRecpientListFormat(\DirectMailTeam\DirectMail\Domain\Type\RecipientListFormat $plainRecipientListFormat) {
		$this->plainRecipientListFormat = $plainRecipientListFormat;
	}

	/**
	 * Returns other mail groups if this group is a combination of other
	 * groups / has type "OTHER_GROUPS"
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DirectMailTeam\DirectMail\Domain\Model\RecipientGroup> The mail groups included by this group
	 */
	public function getMailGroups() {
		return $this->mailGroups;
	}

	/**
	 * Sets the other mail groups included by this group
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<DirectMailTeam\DirectMail\Domain\Model\RecipientGroup> $mailGroups: The other mail groups
	 * @return void
	 */
	public function setMailGroups(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $mailGroups) {
		$this->mailGroups = $mailGroups;
	}

	/**
	 * Returns the recipient tables for this recipient group
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Type\RecipientTableType> The mail groups included by this group
	 */
	public function getRecipientTables() {
		return $this->recipientTables;
	}

	/**
	 * Sets the other mail groups included by this group
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Type\RecipientTableType> $recipientTables: The recipient tables from which to retrieve records
	 * @return void
	 */
	public function setRecipientTables(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $recipientTables) {
		$this->recipientTables = $recipientTables;
	}

	/**
	 * Returns the categories the recipients must have selected in order to be part of this group
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\MailCategory> The categories the recipient must have selected
	 */
	public function getSelectedCatgories() {
		return $this->selectedCategories;
	}

	/**
	 * Sets the categories the recipients must have selected in order to be part of this group
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\MailCategory> $selectedCategories: The categories the recipient must have selected
	 * @return void
	 */
	public function setSelectedCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $selectedCategories) {
		$this->selectedCategories = $selectedCategories;
	}

}


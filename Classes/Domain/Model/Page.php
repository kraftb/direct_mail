<?php
namespace DirectMailTeam\DirectMail\Domain\Model;

/***************************************************************
*  Copyright notice
*
*  (c) 2014-2015 Bernhard Kraft (kraftb@think-open.at)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Domain model for a TYPO3 page
 *
 * @api
 */
class Page extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var string The title of the page
	 */
	protected $title = '';

	/**
	 * @var string The navigational title of the page
	 */
	protected $navTitle = '';

	/**
	 * @var string The subtitle of the page
	 */
	protected $subTitle = '';

	/**
	 * @var boolean Whether the page is hidden in navigation
	 */
	protected $navHide = FALSE;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page>
	 * @lazy
	 */
	protected $children;

	/**
	 * Returns the title of the page
	 *
	 * @return string The title of the page
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title of the page
	 *
	 * @param string $title: The title of the page
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the navigational title of the page
	 *
	 * @return string The navigational title of the page
	 */
	public function getNavTitle() {
		return $this->navTitle;
	}

	/**
	 * Sets the navigational title of the page
	 *
	 * @param string $navTitle: The navigational title of the page
	 * @return void
	 */
	public function setNavTitle($navTitle) {
		$this->navTitle = $navTitle;
	}

	/**
	 * Returns the subtitle of the page
	 *
	 * @return string The subtitle of the page
	 */
	public function getSubTitle() {
		return $this->subTitle;
	}

	/**
	 * Sets the subtitle of the page
	 *
	 * @param string $subTitle: The subtitle of the page
	 * @return void
	 */
	public function setSubTitle($subTitle) {
		$this->subTitle = $subTitle;
	}

	/**
	 * Returns the subpages of the page
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page>
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Sets the subpages of the page
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page>
	 * @return void
	 */
	public function setChildren(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $children) {
		$this->children = $children;
	}

	/**
	 * Returns whether this page is the one currently rendered by the frontend
	 *
	 * @return boolean
	 */
	public function getIsCurrent() {
		return $GLOBALS['TSFE']->id === $this->getUid();
	}

	/**
	 * Returns whether this page should be hidden in menus
	 *
	 * @return boolean
	 */
	public function getIsHiddenInMenus() {
		return $this->navHide;
	}

}


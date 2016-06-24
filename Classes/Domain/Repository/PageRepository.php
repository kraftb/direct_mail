<?php
namespace DirectMailTeam\DirectMail\Domain\Repository;

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
 * Page repository
 *
 * This implements a repository for TYPO3 pages
 *
 * @api
 */
class PageRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Poor mans constructor
	 *
	 * @return void
	 */
	public function initializeObject() {
		$query = $this->createQuery();
		$querySettings = $query->getQuerySettings();
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * Returns all subpages of the specified parent as required for menus
	 *
	 * @param integer $parent The parent page of the menu to generate
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult<\DirectMailTeam\DirectMail\Domain\Model\Page> The subpages for generating a menu
	 */
	public function findForMenu($parent, $showHiddenInMenu = FALSE) {
		$parent = intval($parent);
		$query = $this->createQuery();

		$constraints = array();	
		$constraints[] = $query->equals('pid', $parent);
		$constraints[] = $query->lessThanOrEqual('doktype', \TYPO3\CMS\Frontend\Page\PageRepository::DOKTYPE_SPACER);
		if (!$showHiddenInMenu) {
			$constraints[] = $query->lessThanOrEqual('nav_hide', 0);
		}

		$query->matching(
				$query->logicalAnd($constraints)
			)
			->setOrderings(array(
				'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
			));
		return $query->execute();
	}

	/**
	 * Retrieves the access information for the passed page object
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Page $page: The page for which to check access
	 * @return array|FALSE The access info
	 */
	public function readPageAccess(\DirectMailTeam\DirectMail\Domain\Model\Page $page) {
		$perms = $GLOBALS['BE_USER']->getPagePermsClause(1);
		return \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess($page->getUid(), $perms);
	}

	/**
	 * Returns all subpages of the specified parent.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Page $parent: The page which to return inclusive all subpages
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page> The subpages for generating a menu
	 */
	public function findWholeBranch(\DirectMailTeam\DirectMail\Domain\Model\Page $parent) {
		$result = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		$result->attach($parent);
		$check = array($parent);
		while ($current = array_pop($check)) {
			$childPages = $this->findByPid($current->getUid());
			if (count($childPages)) {
				foreach ($childPages as $childPage) {
					if (!$result->contains($childPage)) {
						$result->attach($childPage);
						$check[] = $childPage;
					}
				}
			}
		}
		return $result;
	}

}


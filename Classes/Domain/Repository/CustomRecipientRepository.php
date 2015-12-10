<?php
namespace DirectMailTeam\DirectMail\Domain\Repository;

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
 * Repository for custom/plain recipient domain model objects
 */
class CustomRecipientRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Retrieves all custom recipient objects from the given pages which are assigned to any of the passed categories
	 * The table/domain-object must be properly configured via TypoScript. There must be a property "selectedCategories"
	 * configured.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page> $pages: The pages from which to retrieve recipients
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\MailCategory> $categories: The categories of which at least one must be assigned to a recipient
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient>
	 */
	public function findByPagesAndCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $pages, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->matching($query->logicalNot($query->equals('email', '')));
		$query->matching($query->in('pid', $pages));
		if (count($categories)) {
			$query->matching($query->in('selectedCategories', $categories));
		}
		return $query->execute();
	}

}


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
 * Repository for address domain model objects
 */
class AddressRepository extends \TYPO3\TtAddress\Domain\Repository\AddressRepository {

	/**
	 * Retrieves all address records which match any of the passed UIDs
	 *
	 * @param array $uidList: An array with UIDs of tt_address records
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\Address>
	 */
	public function findByUidList(array $uidList) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->matching($query->in('uid', $uidList));
		return $query->execute();
	}

	/**
	 * Retrieves all addres objects from the given pages which are assigned to any of the passed categories
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page> $pages: The pages from which to retrieve recipients
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\MailCategory> $categories: The categories of which at least one must be assigned to a recipient
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\Address>
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


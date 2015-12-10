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


use \DirectMailTeam\DirectMail\Domain\Model\MailJob;

/**
 * Repository for direct mail domain model objects
 */
class MailJobRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Retrieves all jobs which are scheduled for delivery and have not yet finished.
	 *
	 * @return void
	 */
	public function findScheduled() {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$constraints = array();
		$constraints[] = $query->logicalNot($query->equals('scheduled', 0));
		$constraints[] = $query->lessThan('scheduled', time());
		$constraints[] = $query->equals('scheduledEnd', 0);
		$constraints[] = $query->logicalNot($query->in('type', array(MailJob::TYPE_DRAFT_INTERNAL, MailJob::TYPE_DRAFT_EXTERNAL)));
		$query->matching($query->logicalAnd($constraints));
		$query->setOrderings(array('scheduled' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
		return $query->execute();
	}

}


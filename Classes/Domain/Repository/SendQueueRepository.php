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
 * Repository for send queue domain model objects
 */
class SendQueueRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Retrieves all unsent send queue entries for the specified mail job
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The mail job for which to retrieve send queue entries
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\SendQueue> Unsent send queue entries for the specified mail job
	 */
	public function findUnsentByMailJob(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$constraints = array();
		$queuedState = $this->objectManager->get('DirectMailTeam\DirectMail\Domain\Type\SendQueueState', \DirectMailTeam\DirectMail\Domain\Type\SendQueueState::QUEUED);
		$constraints[] = $query->equals('mailJob', $mailJob);
		$constraints[] = $query->equals('sendStatus', $queuedState);
		$query->matching($query->logicalAnd($constraints));
		return $query->execute();
	}


}


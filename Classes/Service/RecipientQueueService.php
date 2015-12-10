<?php
namespace DirectMailTeam\DirectMail\Service;

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


use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Core\Locking\Locker;

/**
 * Service for handling recipient queues
 */
class RecipientQueueService extends \DirectMailTeam\DirectMail\Service\ServiceBase {

	/**
	 * The language file which contains the labels for this controller
	 *
	 * @const string
	 */
	const languageFile = 'LLL:EXT:direct_mail/Resources/Private/Language/locallang_service.xml:';

	/**
	 * A prefix for language keys
	 *
	 * @const string
	 */
	const languageKeyPrefix = 'RecipientQueueService';

	/**
	 * Repository for recipient groups (sys_dmail_group)
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\RecipientGroupRepository
	 * @inject
	 */
	protected $recipientGroupRepository = NULL;

	/**
	 * Repository for recipient groups (sys_dmail_group)
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\RecipientRepository
	 * @inject
	 */
	protected $recipientRepository = NULL;


	/**
	 * This method enques all recipients specified by the $recipientGroups parameter
	 * to get delivered to a copy of the $mailJobUid mailing.
	 *
	 * @param array<int> $recipientGroups: The recipient groups which shall receive the mail
	 * @param integer $mailJobUid: The UID of the mail job which to enqueue
	 * @return integer The number of enqueued recipients
	 */
	public function enqueueRecipients(array $recipientGroups, $mailJobUid) {
		// If supplied with an empty array, quit instantly as there is nothing to do
		if (!count($recipientGroups)){
			return 0;
		}

		// Log start of enqueue method
		$begin = time();
		$logMessage = $this->translate('start_enqueue');
		$logMessage = sprintf($this->dateFormat, $begin);
		$this->logger->log($logMessage);

		$mailJobUid = (int)$mailJobUid;
		$mailJob = $this->mailJobRepository->findByUid($mailJobUid);

		$enqueued = 0;
		foreach ($recipientGroups as $group) {
			$recipientGroup = $this->recipientGroupRepository->findByUid($group);
			$enqueued += $this->enqueueRecipientsInGroup($recipientGroup);
		}

		$finish = time();
		$logMessage = $this->translate('finish_enqueue');
		$duration = $this->getDuration($finish-$begin);
		$logMessage = sprintf($this->dateFormat, $finish, $duration);
		$this->logger->log($logMessage);

		return $enqueued;
	}

	/**
	 * Enqueues all recipients in the passed recipient group for the passed mail job
	 * if the recipient has not already been enqueued.
	 *
	 * @param array<int> $recipientGroups: The recipient groups which shall receive the mail
	 * @param integer $mailJobUid: The UID of the mail job which to enqueue
	 * @return integer The number of enqueued recipients
	 */
	protected function enqueueRecipientsInGroup(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup, \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$enqueued = 0;
		$recipients = $this->recipientRepository->findByRecipientGroup($recipientGroup);
		foreach ($recipients as $recipient) {
			$sendQueueEntry = $this->sendQueueRepository->findByRecipientAndMailJob($recipient, $mailJob);
			if ($sendQueueEntry === NULL) {
				// Queue entry does not already exist. This ensures, that every recipient gets enqueued
				// only once for each mail job. Of course a person could subscribe with the same email
				// twice with two different address/fe_users if the setup is configured to allow this.
				$sendQueueEntry = $this->createSendQueueEntry($recipient, $mailJob);
				$this->sendQueueRepository->add($sendQueueEntry);
				$enqueued++;
			}
		}
		// Persist after handling each recipient group.
		$this->persistanceManager->persistAll();
		return $enqueued;
	}

	/**
	 * Creates a new SendQueue domain object for the passed recipient and mail job
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: The recipient which to enqueue
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The mail job for which to enque the passed recipient
	 */
	protected function createSendQueueEntry(\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient, \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$sendQueueEntry = $this->objectManager()->getEmptyObject('DirectMailTeam\DirectMail\Domain\Model\SendQueue');
		$now = $this->objectManager()->get('DateTime');
		$queuedState = $this->objectManager->get('DirectMailTeam\DirectMail\Domain\Type\SendQueueState', \DirectMailTeam\DirectMail\Domain\Type\SendQueueState::QUEUED);

		$sendQueueEntry->setCreationDate($now);
		$sendQueueEntry->setModificationDate($now);
		$sendQueueEntry->setMailJob($mailJob);
		$sendQueueEntry->setRecipient($recipient);
		$sendQueueEntry->setSendStatus($queuedState);
		return $sendQueueEntry;	
	}

}


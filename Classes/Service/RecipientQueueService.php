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
	const languageKeyPrefix = 'RecipientQueueService.';

	/**
	 * Repository for recipient groups (sys_dmail_group)
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\RecipientGroupRepository
	 * @inject
	 */
	protected $recipientGroupRepository = NULL;

	/**
	 * Repository for recipients. This repository will return different kinds of domain models
	 * depending on the type of recipient (tt_address, fe_users, etc.)
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\RecipientRepository
	 * @inject
	 */
	protected $recipientRepository = NULL;

	/**
	 * The persistence session instance is required so it can get cleansed in a regular
	 * interval to keep the memory footprint low.
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Session
	 * @inject
	 */
	protected $persistenceSession;

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

		// Acquire a lock. Take care only one instance will enqueue recipients.
		$mailJobUid = (int)$mailJobUid;
		$lockName = 'EXT:direct_mail-enqueueRecipients-' . $mailJobUid;
		$locker = GeneralUtility::makeInstance('TYPO3\CMS\Core\Locking\Locker', $lockName, Locker::LOCKING_METHOD_FLOCK);
		if (!$locker->acquireExclusiveLock()) {
			// Couldn't aquire a lock. This most probably means that another task got the
			// lock and this process shouldn't block.
			return 0;
		}

		// Get mailJob while being in locked state. The "queryInfo" must not
		// get changed while being in locked state.
		$this->persistenceManager->clearState();
		$mailJob = $this->mailJobRepository->findByUid($mailJobUid);
		if (!$mailJob instanceof \DirectMailTeam\DirectMail\Domain\Model\MailJob) {
			throw new Exception('Couldn\'t find a mail job (sys_dmail) with UID '.$mailJobUid);
		}

		// Check prerequisites for enqueuing recipients
		$queryInfo = $mailJob->getQueryInfo();
		if (!$queryInfo['useSendingQueue']) {
			throw new Exception('The mail job "' . $mailJobUid . '" does not use the sending queue!');
		}
		if ($queryInfo['enqueuedRecipients']) {
			// Already enqueued recipients.
			return 0;
		}

		// Log start of enqueue method
		$begin = time();
		$logMessage = $this->translate('start_enqueue');
		$date = $this->formatDate($begin);
		$logMessage = sprintf($logMessage, $date, $this->formatBytes(memory_get_usage()), $this->formatBytes(memory_get_peak_usage()));
		$this->logger->log($logMessage);

		$enqueued = 0;
		foreach ($recipientGroups as $group) {
			$recipientGroup = $this->recipientGroupRepository->findByUid($group);
			$enqueued += $this->enqueueRecipientsInGroup($recipientGroup, $mailJob);
		}

		$finish = time();
		$logMessage = $this->translate('finish_enqueue');
		$duration = $this->formatDuration($finish-$begin);
		$date = $this->formatDate($finish);
		$logMessage = sprintf($logMessage, $enqueued, $date, $duration, $this->formatBytes(memory_get_usage()), $this->formatBytes(memory_get_peak_usage()));
		$this->logger->log($logMessage);

		// Update "enqueuedRecipients" to TRUE and persist
		//
		// $mailJob must get retrieved again here. Else the persistence layer can't update it as
		// it has been removed from persistenceSession by the calls to "clearState"
		$mailJob = $this->mailJobRepository->findByUid($mailJob->getUid());
		$queryInfo['enqueuedRecipients'] = TRUE;
		$queryInfo['recipientCount'] = $enqueued;
		$mailJob->setQueryInfo($queryInfo);
		$this->mailJobRepository->update($mailJob);
		$this->persistenceManager->persistAll();

		// Release lock
		$locker->release();

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
		$index = 0;
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
			if (++$index % 100) {
				// Persist new objects after each hundred processed entries.
				$this->persistenceManager->persistAll();
				// To save memory remove all accumulated session data in persistenceManager
				// and persistenceSession.
				$this->persistenceManager->clearState();
			}
		}
		// Persist after handling each recipient group.
		$this->persistenceManager->persistAll();
		return $enqueued;
	}

	/**
	 * Creates a new SendQueue domain object for the passed recipient and mail job
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: The recipient which to enqueue
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The mail job for which to enque the passed recipient
	 */
	protected function createSendQueueEntry(\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient, \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$sendQueueEntry = $this->objectManager->getEmptyObject('DirectMailTeam\DirectMail\Domain\Model\SendQueue');
		$now = $this->objectManager->get('DateTime');
		$queuedState = $this->objectManager->get('DirectMailTeam\DirectMail\Domain\Type\SendQueueState', \DirectMailTeam\DirectMail\Domain\Type\SendQueueState::QUEUED);

		$sendQueueEntry->setCreationDate($now);
		$sendQueueEntry->setModificationDate($now);
		$sendQueueEntry->setMailJob($mailJob);
		$sendQueueEntry->setRecipient($recipient);
		$sendQueueEntry->setSendStatus($queuedState);
		return $sendQueueEntry;	
	}

}


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
 * Service for sending mass mails
 */
class MassMailingService extends \DirectMailTeam\DirectMail\Service\ServiceBase {

	/**
	 * The language file which contains the labels for this controller
	 *
	 * @const string
	 */
	const languageFile = 'LLL:EXT:direct_mail/Resources/Private/Language/locallang_mod2-6.xml:';

	/**
	 * An instance of the TYPO3 registry
	 *
	 * @var \TYPO3\CMS\Core\Registry
	 * @inject
	 */
	protected $registry = NULL;

	/**
	 * Repository for recipients. This repository will return different kinds of domain models
	 * depending on the type of recipient (tt_address, fe_users, etc.)
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\RecipientRepository
	 * @inject
	 */
	protected $recipientRepository = NULL;

	/**
	 * A messageId for all messages being sent
	 *
	 * @var string
	 */
	protected $messageId = '';

	/**
	 * Amount of mail sent in one batch
	 *
	 * @var integer
	 */
	protected $sendPerCycle = 50;

	/**
	 * Whether this service is used from a cronjob (else from within a backend module)
	 *
	 * @var boolean
	 */
	protected $cronjobMode = TRUE;

	/**
	 * When this value is TRUE a notification will get sent when sending a direct mail
	 * has begun/finished.
	 *
	 * @var boolean
	 */
	protected $sendNotification = FALSE;

	/**
	 * Set whether this module is called as cronjob
	 *
	 * @param boolean $cronjobMode: Set to FALSE when sending is invoked from within the backend
	 * @return void
	 */
	public function setCronjobMode($cronjobMode) {
		$this->cronjobMode = $cronjobMode;
	}

	/**
	 * This method has to get called as prior to use of this service
	 *
	 * @return void
	 */
	public function initialize() {
		$this->generateMessageId();
		$this->setLineBreakCharacter();

			// Mailer engine parameter: sendPerCycle
			// Defines amount of message being sent during one invocation of the cron/scheduler job
		if ((int)$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['sendPerCycle']) {
			$this->sendPerCycle = (int)$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['sendPerCycle'];
		}

			// Mailer engine parameter: notificationJob
			// When set a message will get sent when starting/finishing a single direct mail job
		$this->sendNotification = intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['notificationJob']);

		// There is no need to set a custom language as in "Dmailer.php" runcron method.
		// Either the language of the "_cli_scheduler" or "_cli_direct_mail" user is used
		// when invoked via "cli_dispatch.phpsh". When the job is invoked from within the
		// scheduler backend module the current BE users language will get used.

		if ($this->cronjobMode) {
			$this->logger->log('Starting directmail job');
		}

		// This will record the last time when the cron/scheduler job got invoked.
		$this->registry->set('DirectMailTeam\DirectMail', 'sending_start', time());
	}

	/**
	 * Method for processing currently active mass mailing jobs
	 *
	 * @return void
	 */
	public function handleJobs() {
		$start = GeneralUtility::milliseconds();

		// Log invocation of this dmailer run.
		$logMessage = $this->translate('dmailer_invoked_at') . ' ' . $this->getCurrentDate(); 
		$this->logger->log($logMessage);

		// Retrieve jobs to be processed
		$jobs = $this->mailJobRepository->findScheduled();

		if (count($jobs)) {
			// Only process one job per invocation.
			// If the job gets finished it will not get processed again during next invocation.
			$job = $jobs->getFirst();
			if ($this->validJob($job)) {
				$this->handleJob($job);
			} else {
				$this->logger->log($this->translate('dmailer_non_sendqueue_job_encountered'));
			}
		} else {
			$this->logger->log($this->translate('dmailer_nothing_to_do'));
		}

		// Log finishing and duration for this invocation.
		$finish = GeneralUtility::milliseconds();
		$duration = $finish - $start;
		$logMessage = $this->translate('dmailer_ending') . $duration . ' ms';
		$this->logger->log($logMessage);
	}

	/**
	 * Checks if the passed mail job is a job using the send queue and if it's recipients
	 * are already enqueued.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The job to check
	 * @return boolean Returns TRUE when the passed job is a send queue job
	 */
	protected function validJob(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$queryInfo = $mailJob->getQueryInfo();
		if (!$queryInfo['useSendingQueue']) {
			return FALSE;
		}
		if (!$queryInfo['enqueuedRecipients']) {
			// If this is a valid sending queue job but the recipients have not yet been
			// enqueued then call the recipientQueueService  method "enqueueRecipients"
			// and then re-load the mailJob/queryInfo.
			$recipientQueueService = $this->objectManager->get('DirectMailTeam\DirectMail\Service\RecipientQueueService');
			$enqueued = $recipientQueueService->enqueueRecipients($queryInfo['recipientGroups'], $mailJob->getUid());
			if ($enqueued) {
				// If this "validJob" invocation caused recipients to get enqueued then return "FALSE"
				// altough the mailJob should be valid now (have "enqueuedRecipients" set). By returning
				// "FALSE" no mails will get sent during this task run. This ensures the task does not
				// run too long but will only enqueue recipients. The mails will start to get sent during
				// the next invocation of the scheduler task.
				return FALSE;
			}
			$this->persistenceManager->clearState();
			$mailJob = $this->mailJobRepository->findByUid($mailJob->getUid());
			$queryInfo = $mailJob->getQueryInfo();
			if (!$queryInfo['enqueuedRecipients']) {
				// It can be that the recipients are still not enqueued because this task is
				// being undertaken by another process right now and the call to "enqueueRecipients"
				// above has returned prematurely because the other process is holding the lock.
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Processes a single direct mail job
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The job to process
	 * @return void
	 */
	protected function handleJob(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$logMessage = $this->translate('dmailer_sys_dmail_record');
		$logMessage .= ' ' . $mailJob->getUid() . ', \'' . $mailJob->getSubject() . '\' ';
		$logMessage .= $this->translate('dmailer_processed');
		$this->logger->log($logMessage);

		// Sending of the mails itself is still accomplished by the "Dmailer" class.
		// @todo: Refactor sending of mails including template processing, etc.
		$directMailRow = BackendUtility::getRecord('sys_dmail', $mailJob->getUid());
		$this->dmailer = GeneralUtility::makeInstance('DirectMailTeam\DirectMail\Dmailer');
		$this->dmailer->dmailer_prepare($directMailRow);

		if (!$mailJob->getScheduledBegin()) {
			$this->updateJob($mailJob, 'begin');
		}

		$finished = $this->sendToRecipients($mailJob);
	
		if ($finished) {
			$this->updateJob($mailJob, 'end');
		}
	}

	/**
	 * Sends mail job (sys_dmail) to recipients in mail queue
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The mail job (sys_dmail) to send mails for
	 * @return boolean When "TRUE" is returned the last mail of this job has been sent and the job can get marked as finished.
	 */
	protected function sendToRecipients(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$finishedSending = TRUE;
		$queuedItems = $this->sendQueueRepository->findUnsentByMailJob($mailJob);

		$count = 0;
		foreach ($queuedItems as $queuedItem) {
			$isSent = $this->sendToRecipient($queuedItem, $mailJob);
			if ($isSent) {
				$count++;
			}
			if ($count >= $this->sendPerCycle) {
				$finishedSending = FALSE;
				break;
			}
		}

		$logMessage = $this->translate('dmailer_sending_to_recipients');
		$this->logger->log($logMessage, 'direct_mail', $count);

		return $finishedSending;
	}

	/**
	 * Send a single mail to a recipient. The relation of mail + recipient is called
	 * a send queue item.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\SendQueue $queuedItem: The queued mail/recipient
	 * @return boolean Returns TRUE if the mail was sent sucessfully
	 */
	protected function sendToRecipient(\DirectMailTeam\DirectMail\Domain\Model\SendQueue $queuedItem, \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$processingState = $this->objectManager->get('DirectMailTeam\DirectMail\Domain\Type\SendQueueState', \DirectMailTeam\DirectMail\Domain\Type\SendQueueState::PROCESSING);
		$sentState = $processingState->nextState();
		
		if (!$this->advanceQueueItemState($queuedItem, $processingState)) {
			// Could not advance queue item to next state.
			// Maybe it has already been aquired for processing by another task/process/thread.
			return FALSE;
		}

		$recipient = $this->recipientRepository->findByQueueItem($queuedItem);

		// ---------- Legacy code -------- begin -----------------
		// @todo: Sending of mails is still performed using the "Dmailer" class of
		// direct mail. This should get changed by reworking the code which parses
		// the mail template (plain/html) and refactoring the code for sending
		// mails. Recipient categories will have to get taken into account!
		$tKey = $this->getLegacyTableKey($recipient);
		$table = $this->getLegacyTable($recipient);
		$recipientRow = $this->recipientRepository->getPlainRow($recipient);
		$recipientRow['sys_dmail_categories_list'] = $this->dmailer->getListOfRecipentCategories($table, $recipient->getUid());
		$this->dmailer->shipOfMail($mailJob->getUid(), $recipientRow, $tKey);
		// ---------- Legacy code -------- end -------------------

		if (!$this->advanceQueueItemState($queuedItem, $sentState)) {
			// Could not advance queue item to next state.
			// This is a fatal error.
			throw new \Exception('Couldn\'t set queue item to "sent" state!');
		}

		return TRUE;	
	}

	/**
	 * Determines the old-school legacy table key to be used for sending the direct mail
	 * to this recipient. This is required as still the old code is used for delivering
	 * the mail.
	 * @todo: Remove this method when switching to complete new sending code
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: The recipient for which to determine the table key.
	 * @return string The table key, either "t" or "f" for tt_address/fe_users, "P" for plain list or "u" for custom table
	 */
	protected function getLegacyTableKey(\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient) {
		switch (get_class($recipient)) {
			case 'DirectMailTeam\DirectMail\Domain\Model\Address':
				return 't';

			case 'DirectMailTeam\DirectMail\Domain\Model\FrontendUser':
				return 'f';

			case 'DirectMailTeam\DirectMail\Domain\Model\CustomRecipient':
				if ($recipient->getType() === 'plain') {
					return 'P';
				} else {
					return 'u';
				}

			default:
				return 'u';
		}
	}

	/**
	 * Determines the old-school legacy table name to be used.
	 * @todo: Remove this method when switching to complete new sending code
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: The recipient for which to determine the table key.
	 * @return string The table name for the passed recipient
	 */
	protected function getLegacyTable(\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient) {
		switch (get_class($recipient)) {
			case 'DirectMailTeam\DirectMail\Domain\Model\Address':
				return 'tt_address';

			case 'DirectMailTeam\DirectMail\Domain\Model\FrontendUser':
				return 'fe_users';

			case 'DirectMailTeam\DirectMail\Domain\Model\CustomRecipient':
				if ($recipient->getType() === 'plain') {
					return 'PLAINLIST';
				} else {
					return $recipient->getType();
				}

			default:
				return $recipient->getType();
		}
	}

	/**
	 * Advances a queue item inteo the next state for sending. This is done atomically using a
	 * locking mechanism. This ensures that each queue item is surely only processed by one
	 * cronjob/scheduler process.
	 *
	 * This could get implemented more easily using an "UPDATE/WHERE" query and then checking
	 * the affected rows. Instead the extbase approach using locking was choosen. If this proves
	 * to be far less efficient this method could get changed to the much simpler UPDATE/WHERE
	 * variant.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\SendQueue $queuedItem: The queued mail/recipient
	 * @param \DirectMailTeam\DirectMail\Domain\Type\SendQueueState $targetState: The state into which to advance the queue item
	 * @return void
	 */
	protected function advanceQueueItemState(\DirectMailTeam\DirectMail\Domain\Model\SendQueue $queuedItem, \DirectMailTeam\DirectMail\Domain\Type\SendQueueState $targetState) {
		$stateUpdated = FALSE;
		$previousState = $targetState->previousState();

		// Acquire a lock
		$locker = GeneralUtility::makeInstance('TYPO3\CMS\Core\Locking\Locker', 'EXT:direct_mail-advanceQueueItemState', Locker::LOCKING_METHOD_FLOCK);
		$locker->acquire();

		// Clear persistence manager state and re-retrieve the mail job
		$this->persistenceManager->clearState();
		$currentQueuedItem = $this->sendQueueRepository->findByUid($queuedItem->getUid());
		
		if ($currentQueuedItem->getSendStatus()->equals($previousState)) {
			$currentQueuedItem->setSendStatus($targetState);
			$this->sendQueueRepository->update($currentQueuedItem);
			$this->persistenceManager->persistAll();
			$stateUpdated = TRUE;
		}
		
		// Release lock
		$locker->release();

		return $stateUpdated;
	}

	/**
	 * Updates the status (begin/end) of the passed direct mail job (sys_dmail record)
	 * Will take care of proper locking during update. Will also send a notification
	 * mail when the begin/end datetime fields have been updated. For this purpose this
	 * method is split in two parts: "updateJobAndPersist" and "updateJobLogAndNotify".
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The job to update
	 * @param string $status: The status which to set in the mail job.
	 * @return void
	 */
	protected function updateJob(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob, $status) {
		$statusUpdated = $this->updateJobAndPersist($mailJob, $status);
		if ($statusUpdated) {
			$this->updateJobLogAndNotify($mailJob, $status);
		}
	}

	/**
	 * Handles the first part of updating a job: Updates the start/end fields and taking
	 * care of locking
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The job to update
	 * @param string $status: The status which to set in the mail job.
	 * @return void
	 */
	protected function updateJobAndPersist(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob, $status) {
		// Acquire a lock
		$locker = GeneralUtility::makeInstance('TYPO3\CMS\Core\Locking\Locker', 'EXT:direct_mail-updateJob', Locker::LOCKING_METHOD_FLOCK);
		$locker->acquire();

		// Clear persistence manager state and re-retrieve the mail job
		$this->persistenceManager->clearState();
		$currentMailJob = $this->mailJobRepository->findByUid($mailJob->getUid());
		// Eventually update begin/end time.
		if ($status === 'begin' && !$currentMailJob->getScheduledBegin()) {
			$currentMailJob->setScheduledBegin(new \DateTime());
			$statusUpdated = TRUE;
		}
		if ($status === 'end' && !$currentMailJob->getScheduledEnd()) {
			$currentMailJob->setScheduledEnd(new \DateTime());
			$statusUpdated = TRUE;
		}
		
		if ($statusUpdated) {
			$this->mailJobRepository->update($currentMailJob);
			$this->persistenceManager->persistAll();
		}

		// Release lock
		$locker->release();

		return $statusUpdated;
	}

	/**
	 * Handles the second part of updating a job: Logging the result and eventually
	 * sends a notification mail.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The job which got updated
	 * @param string $status: The status which was set in the mail job.
	 * @return void
	 */
	protected function updateJobLogAndNotify(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob, $status) {
		// Determine subject and message for logging and notification mail
		switch ($status) {
			case 'begin':
				$subject = $this->translate('dmailer_mid') . ' ' . $mailJob->getUid() . ' ' . $this->translate('dmailer_job_begin');
				$message = $this->translate('dmailer_job_begin') . ': ' . strftime('%d-%m-%Y, %H:%M:%S');
			break;

			case 'end':
				$subject = $this->translate('dmailer_mid') . ' ' . $mailJob->getUid() . ' ' . $this->translate('dmailer_job_end');
				$message = $this->translate('dmailer_job_end') . ': ' . strftime('%d-%m-%Y, %H:%M:%S');
			break;

			default:
				throw new \Exception('Invalid status for job!');
		}

		// Update log
		$logMessage = $subject . ': ' . $message;
		$this->logger->log($logMessage);

		// Send notification mail
		if ($this->sendNotification) {
			$this->sendNotificationMail($subject, $message, $mailJob);
		}
	}

	/**
	 * Sends a notification mail about start/begin of the job
	 *
	 * @param string $subject: The subject of the notification mail
	 * @param string $message: The message of the notification mail
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The mail job for which to send a notification mail
	 * @return void
	 */
	protected function sendNotificationMail($subject, $message, \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
			/** @var $mail \TYPO3\CMS\Core\Mail\MailMessage */
			$mail = GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');
			$mail->setTo($mailJob->getFromEmail(), $mailJob->getFromName());
			$mail->setFrom($mailJob->getFromEmail(), $mailJob->getFromName());
			$mail->setSubject($subject);
			if (!empty($mailJob->getReplytoEmail())) {
				$mail->setReplyTo($mailJob->getReplytoEmail());
			}
			$mail->setBody($message);
			$mail->send();
	}

	/**
	 * Generates a message id
	 *
	 * @return void
	 */
	protected function generateMessageId() {
		$host = GeneralUtility::getHostname();
		if (!$host || $host == '127.0.0.1' || $host == 'localhost' || $host == 'localhost.localdomain') {
			$host = ($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']) : 'localhost') . '.TYPO3';
		}

		$idLeft = time() . '.' . uniqid();
		$idRight = !empty($host) ? $host : 'swift.generated';
		$this->messageId = $idLeft . '@' . $idRight;
	}

	/**
	 * Sets the line break character to get used for mails
	 *
	 * @return void
	 */
	protected function setLineBreakCharacter() {
		// Default line break for Unix systems.
		$this->linebreak = LF;
		// Line break for Windows. This is needed because PHP on Windows systems
		// send mails via SMTP instead of using sendmail, and thus the linebreak needs to be \r\n.
		if (TYPO3_OS == 'WIN') {
			$this->linebreak = CRLF;
		}
	}


}



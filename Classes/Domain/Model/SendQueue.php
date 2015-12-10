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
 * Domain model for send queue
 * A send queue entry contains a relation between a direct mail and a recipient.
 * When a new direct mail gets scheduled for sending such a queue record is
 * created for each recipient. The scheduler/cronjob service class "MassMailingService"
 * then retrieves each of the queue entries and processes them.
 */
class SendQueue extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Creation date of this send queue entry
	 *
	 * @var \DateTime
	 */
	protected $creationDate = NULL;

	/**
	 * Date of last change of this entry
	 *
	 * @var \DateTime
	 */
	protected $modificationDate = NULL;

	/**
	 * The direct mail message associated with this send queue entry
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Model\MailJob
	 */
	protected $mailJob = NULL;

	/**
	 * The UID of the recipient if it got retrieved from a table
	 *
	 * @var integer
	 */
	protected $recipientUid = 0;

	/**
	 * The table of the recipient if it got retrieved from database
	 *
	 * @var string 
	 */
	protected $recipientTable = '';

	/**
	 * The recipient data if it is plain data
	 *
	 * @var string 
	 */
	protected $recipientData = '';
	
	/**
	 * The send status of this entry
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Type\SendQueueState
	 */
	protected $sendStatus = NULL;


	/**
	 * Returns the creation date of this send queue entry
	 *
	 * @return \DateTime The creation date of this queue entry
	 */
	public function getCreationDate() {
		return $this->creationDate;
	}

	/**
	 * Sets the creation date for this send queue entry
	 *
	 * @param \DateTime $creationDate: The creation date to set
	 * @return void
	 */
	public function setCreationDate(\DateTime $creationDate) {
		$this->creationDate = $creationDate;
	}

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
	 * Returns the direct mail message for this send queue entry
	 *
	 * @return \DirectMailTeam\DirectMail\Domain\Model\MailJob The direct mail of this queue entry
	 */
	public function getMailJob() {
		return $this->mailJob;
	}

	/**
	 * Sets the direct mail message for this send queue entry
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob: The direct mail to set for this queue entry
	 * @return void
	 */
	public function setMailJob(\DirectMailTeam\DirectMail\Domain\Model\MailJob $mailJob) {
		$this->mailJob = $mailJob;
	}

	/**
	 * Returns the recipient UID for this send queue entry
	 *
	 * @return integer The recipient UID of this queue entry
	 */
	public function getRecipientUid() {
		return $this->recipientUid;
	}

	/**
	 * Sets the recipient UID for this send queue entry. Only valid if also a recipient table is set.
	 *
	 * @param integer $recipientUid: The UID of the recipient record for this send queue entry
	 * @return void
	 */
	public function setRecipientUid($recipientUid) {
		$this->recipientUid = $recipientUid;
	}

	/**
	 * Returns the recipient table for this send queue entry
	 *
	 * @return string The recipient table of this queue entry
	 */
	public function getRecipientTable() {
		return $this->recipientTable;
	}

	/**
	 * Sets the recipient table for this send queue entry. Only valid if also a recipient UID is set.
	 *
	 * @param string $recipientTable: The table of the recipient record for this send queue entry
	 * @return void
	 */
	public function setRecipientTable($recipientTable) {
		$this->recipientTable = $recipientTable;
	}

	/**
	 * Returns the recipient data for this send queue entry.
	 * Must not be set at the same time as recipient table+uid.
	 *
	 * When the recipient is a plain-list reciepient (no record)
	 * this property will contain a JSON encoded array of values
	 * which should get thawed to a CustomRecipient domain model
	 * object.
	 *
	 * @return string The recipient data of this queue entry
	 */
	public function getRecipientData() {
		return $this->recipientData;
	}

	/**
	 * Sets the recipient data for this send queue entry.
	 *
	 * @param string $recipientData: The JSON encoded recipient data for this send queue entry
	 * @return void
	 */
	public function setRecipientData($recipientData) {
		$this->recipientData = $recipientData;
	}

	/**
	 * Sets the recipient fields (recipientUid, recipientTable, recipientData) depending on the
	 * passed recipient object.
	 *
	 * This method breaks the strict rule of havin models with only getters and setters. It is
	 * questionable whether this should get accomplished by an appropriate method in the
	 * SendQueueRepository instead.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: The recipient which to set
	 * @return void
	 */
	public function setRecipient(\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient) {
		switch (get_class($recipient)) {
			case 'DirectMailTeam\DirectMail\Domain\Model\Address':
			case 'DirectMailTeam\DirectMail\Domain\Model\FrontendUser':
				$this->setRecipientTable($recipient->getType());
				$this->setRecipientUid($recipient->getUid());
			break;

			case 'DirectMailTeam\DirectMail\Domain\Model\CustomRecipient':
				if ($recipient->getType() === 'plain') {
					// For plain recipients (from CSV, plain list) the recipient data is stored
					// as json_encoded value in the "recipientData" property.
					$this->setRecipientData(json_encode($recipient->toArray()));
				} else {
					// For "userTable" type records the recipient table is stored in the "type" property.
					$this->setRecipientTable($recipient->getType());
					$this->setRecipientUid($recipient->getUid());
				}
			break;

			default:
				if (!$recipient instanceof \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient) {
					throw new \Exception('A recipient must implement the "Recipient" interface');
				}
				$this->setRecipientTable($recipient->getType());
				$this->setRecipientUid($recipient->getUid());
			break;
		}
	}
	
	/**
	 * Returns the send status of this entry
	 *
	 * @return \DirectMailTeam\DirectMail\Domain\Type\SendQueueState The current send status of this send queue entry
	 */
	public function getSendStatus() {
		return $this->sendStatus;
	}

	/**
	 * Sets the send status of this entry
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Type\SendQueueState $sendStatus: The status which will get set
	 * @return void
	 */
	public function setSendStatus(\DirectMailTeam\DirectMail\Domain\Type\SendQueueState $sendStatus) {
		$this->sendStatus = $sendStatus;
	}

}


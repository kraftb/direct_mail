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
 * Domain model for a direct mail
 */
class MailJob extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	const TYPE_PAGE = 0;
	const TYPE_EXTERNAL_URL = 1;
	const TYPE_DRAFT_INTERNAL = 2;
	const TYPE_DRAFT_EXTERNAL = 3;

	/**
	 * Date of last change of this entry
	 *
	 * @var \DateTime
	 */
	protected $modificationDate = NULL;

	/**
	 * The type of this newsletter/direct mail
	 *
	 * @var integer
	 */
	protected $type = 0;

	/**
	 * The page from which this newsletter/direct mail is being generated
	 * @todo: Change to a "Page" domain model as soon as a common one is
	 * provided via the extbase extension or another quasi-standard
	 * extension.
	 *
	 * @var integer
	 */
	protected $page = 0;

	/**
	 * The subject of this direct mail
	 *
	 * @var string
	 */
	protected $subject = '';

	/**
	 * Whether this direct mail has already been sent
	 *
	 * @var boolean
	 */
	protected $isSent = FALSE;
	
	/**
	 * The datetime this mail is scheduled to get sent
	 *
	 * @var \DateTime
	 */
	protected $scheduled = NULL;

	/**
	 * The datetime when delivering this mail was started
	 *
	 * @var \DateTime
	 */
	protected $scheduledBegin = NULL;

	/**
	 * The datetime when delivering this mail has finished
	 *
	 * @var \DateTime
	 */
	protected $scheduledEnd = NULL;


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
	 * Returns the type of this direct mail message
	 *
	 * @return integer The type of this direct mail
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type of this direct mail message
	 *
	 * @param integer $type: The type of this direct mail
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Returns the page being sent by this direct mail message
	 *
	 * @return integer The page (uid) for this direct mail message
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Sets the page being sent by this direct mail message
	 *
	 * @param integer $page: The page (uid) for this direct mail message
	 * @return void
	 */
	public function setPage($page) {
		$this->page = $page;
	}

	/**
	 * Returns the subject of this direct mail message
	 *
	 * @return string The subject for this direct mail message
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets the subject for this direct mail message
	 *
	 * @param string $subject: The subject for this direct mail message
	 * @return void
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Returns the send status of this direct mail message
	 *
	 * @return boolean Will return TRUE when this mail message has already been sent
	 */
	public function getIsSent() {
		return $this->isSent;
	}

	/**
	 * Sets the send status of this direct mail message
	 *
	 * @param boolean $isSent: When TRUE this mail message is marked as already sent
	 * @return void
	 */
	public function setIsSent($isSent) {
		$this->isSent = $isSent;
	}

	/**
	 * Returns the scheduled sending date for this mail
	 *
	 * @return \DateTime The scheduled sending date
	 */
	public function getScheduled() {
		return $this->scheduled;
	}

	/**
	 * Sets the scheduled sending date for this mail
	 *
	 * @param \DateTime $scheduled: The scheduled sending date
	 * @return void
	 */
	public function setScheduled(\DateTime $scheduled) {
		$this->scheduled = $scheduled;
	}

	/**
	 * Returns the actual start of sending date for this mail
	 *
	 * @return \DateTime The actual sending date
	 */
	public function getScheduledBegin() {
		return $this->scheduledBegin;
	}

	/**
	 * Sets the actual start of sending date for this mail
	 *
	 * @param \DateTime $scheduledBegin: The actual sending date
	 * @return void
	 */
	public function setScheduledBegin(\DateTime $scheduledBegin) {
		$this->scheduledBegin = $scheduledBegin;
	}

	/**
	 * Returns the date when sendig of this mail has finished
	 *
	 * @return \DateTime The finishing date for sending this mail
	 */
	public function getScheduledEnd() {
		return $this->scheduledEnd;
	}

	/**
	 * Sets the date when sending this mail has finished
	 *
	 * @param \DateTime $scheduledEnd: The finishing date for sending this mail
	 * @return void
	 */
	public function setScheduledEnd(\DateTime $scheduledEnd) {
		$this->scheduledEnd = $scheduledEnd;
	}

}


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


use \DirectMailTeam\DirectMail\Domain\Type\RecipientGroupType;
use \DirectMailTeam\DirectMail\Domain\Type\RecipientTableType;
use \DirectMailTeam\DirectMail\DirectMailUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class implements a accumulation repository for different types of recipients
 */
class RecipientRepository {

	/**
	 * Repository for tt_address records
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\AddressRepository
	 * @inject
	 */
	protected $addressRepository = NULL;

	/**
	 * Repository for fe_user records
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository = NULL;

	/**
	 * Repository for custom records
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\CustomRecipientRepository
	 * @inject
	 */
	protected $customRecipientRepository = NULL;

	/**
	 * Repository for pages
	 *
	 * @var \DirectMailTeam\DirectMail\Domain\Repository\PageRepository
	 * @inject
	 */
	protected $pageRepository = NULL;

	/**
	 * Data mapper used for manually mapping custom rows to a CustomRecipientModel
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 * @inject
	 */
	protected $dataMapper = NULL;

	/**
	 * Configuration manager instance
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager = NULL;

	/**
	 * An object manager instance
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Poor mans constructor
	 *
	 * @return void
	 */
	public function initializeObject() {
		// Todo: Initialize object (userTable)
	}

	/**
	 * Retrieves a recipient referenced from within a queue item
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\SendQueue $queueItem: The queue item from which to retrieve the recipient
	 * @return \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient The retrieved recipient
	 */
	public function findByQueueItem(\DirectMailTeam\DirectMail\Domain\Model\SendQueue $queueItem) {
		$table = (string)$queueItem->getRecipientTable();
		$uid = (int)$queueItem->getRecipientUid();
		if (strlen($table) && $uid) {
			switch ($table) {
				case 'tt_address':
					return $this->addressRepository->findByUid($uid);

				case 'fe_users':
					return $this->frontendUserRepository->findByUid($uid);

				default:
					return $this->mapCustomRecordFromDatabase($table, $uid);
			}
		} else {
			$data = json_decode($queueItem->getRecipientData());
			// The uid of the queue item gets used as uid of the custom record.
			$data['uid'] = $queueItem->getUid();
			$table = 'plain';
			// @documentation: Using the "type" property of plain records one can specify a custom
			// domain model class to get used for those plain records.
			if ($data['type']) {
				$table = $data['type'];
			} else {
				$data['type'] = 'plain';
			}
			return $this->mapCustomRecord($data, $table);
		}
	}

	/**
	 * Returns the recipients within a recipient group
	 *
	 * This code is mostly a reimplementation of the method "getSingleMailGroup"
	 * found in EXT:direct_mail/Classes/Module/Dmail.php. 
	 *
	 * The retrieval of recipients for the different types of recipientGroups has been
	 * moved to separate methods by the below switch statements. Eventually even separate
	 * classes should get used for each of them.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group whose recipients to retrieve
	 * @return mixed A list of recipient. Could be a QueryResult object or a plain array depending on the group type
	 */
	public function findByRecipientGroup(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup, $dmailer) {
		switch ((string)$recipientGroup->getType()) {
			case RecipientGroupType::FROM_PAGES:
				return $this->findByRecipientGroupFromPage($recipientGroup);

			case RecipientGroupType::PLAIN_LIST:
				return $this->findByRecipientGroupPlainList($recipientGroup);

			case RecipientGroupType::STATIC_LIST:
				return $this->findByRecipientGroupStaticList($recipientGroup);

			case RecipientGroupType::SPECIAL_QUERY:
				return $this->findByRecipientGroupSpecialQuery($recipientGroup);

			case RecipientGroupType::OTHER_GROUPS:
				return $this->findByRecipientGroupOtherGroups($recipientGroup);

			default:
				throw new \Exception('Invalid recipient group type!');
		}
	}

	/**
	 * Returns the recipients within a recipient group using the "From pages" setting
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group whose recipients to retrieve
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient> The found recipients
	 */
	protected function findByRecipientGroupFromPage(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup) {
		$pages = $this->getRecipientPages($recipientGroup);
		$result = $this->objectManager->get('DirectMailTeam\DirectMail\NestedStorage');
		if (!count($pages)) {
			return $result;
		}

		$whichTables = $recipientGroup->getParsedRecipientTables();

		foreach ($whichTables as $recipientTableType) {
			switch ((int)((string)$recipientTableType)) {

				case RecipientTableType::TT_ADDRESS:
					$recipients = $this->addressRepository->findByPagesAndCategories($pages, $recipientGroup->getSelectedCategories());
					break;

				case RecipientTableType::FE_USERS:
					$recipients = $this->frontendUserRepository->findByPagesAndCategories($pages, $recipientGroup->getSelectedCategories());
					break;

				case RecipientTableType::CUSTOM_TABLE:
					$recipients = $this->customRecipientRepository->findByPagesAndCategories($pages, $recipientGroup->getSelectedCategories());
					break;

				case RecipientTableType::FE_GROUPS:
					$recipients = $this->frontendUserRepository->findByGroupsOnPagesAndCategories($pages, $recipientGroup->getSelectedCategories());
					break;

				default:
					throw new \Exception('Invalid recipient table type!');
			}
			$result->attach($recipients);
		}
		return $result;
	}

	/**
	 * Returns an ObjectStorage containing all pages from which to retrieve recipients.
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group for which to determine the pages from which recipients shall get retrieved
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Page> The matching pages
	 */
	protected function getRecipientPages(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup) {
		$pages = $recipientGroup->getPages() ? : $this->id;
		$pages = GeneralUtility::intExplode(',',$pages);
		$result = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		foreach ($pages AS $pageUid) {
			$page = $this->pageRepository->findByUid($pageUid);
			if ($this->pageRepository->readPageAccess($page)) {
				if ($recipientGroup->getPagesRecursive()) {
					$branch = $this->pageRepository->findWholeBranch($page);
					$result->addAll($branch);
				} else {
					$result->add($page);
				}
			}
		}
		return $result;
	}

	/**
	 * Returns the recipients within a recipient group using the "plain list" setting
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group whose recipients to retrieve
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\CustomRecipient> The found recipients
	 */
	protected function findByRecipientGroupPlainList(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup) {
		if ($recipientGroup->getPlainRecipientListFormat()->equals(RecipientListType::CSV))	{
			$recipients = DirectMailUtility::rearrangeCsvValues(DirectMailUtility::getCsvValues($recipientGroup->getPlainRecipientList()), \DirectMailTeam\DirectMail\Module\Dmail::$fieldList);
		} else {
			$recipients = DirectMailUtility::rearrangePlainMails(array_unique(preg_split('|[[:space:],;]+|',$recipientGroup->getPlainRecipientList())));
		}
		$plainList = DirectMailUtility::cleanPlainList($recipients);

		// Assign artificial UIDs for plain list items
		foreach ($plainList as $index => $plainItem) {
			if (!$plainItem['uid']) {
				$plainItem['uid'] = $index;
			}
		}

		$customRecipientClass = 'DirectMailTeam\DirectMail\Domain\Model\CustomRecipient';
		// @hook-findByRecipientPlainList: See @hook-mapCustomRecord
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['customRecipientClasses']['plain'])) {
			$customRecipientClass = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['customRecipientClasses']['plain'];
		}

		// Map the plain items to "CustomRecipient" domain objects
		return $this->dataMapper->map($customRecipientClass, $plainList);
	}
			
	/**
	 * Returns the recipients within a recipient group using the "static list" setting
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group whose recipients to retrieve
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\CustomRecipient> The found recipients
	 */
	protected function findByRecipientGroupStaticList(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup) {
		$result = $this->objectManager->get('DirectMailTeam\DirectMail\NestedStorage');
	
		// @todo: Replace the "getStaticIdList" method by a method which is part of each repository. For this purpose
		// each table "tt_address", "fe_users" should have a field "sys_dmail_group" which (eventually) most not even
		// exist in the database but should only be configured in the extbase TypoScript configuration for each of the
		// mentioned tables (As MM field using the reverse configuration as already defined for sys_dmail_group). Then
		// each recipient having the sys_dmail_group statically assigned via the MM table can easily get retrieved.
		// The problem is, that the different types of recipients (tt_address, fe_users, etc.) can't get retrieved from
		// the "sys_dmail_group" field "static_list" as it is currently not possible to let extbase return an
		// ObjectStorage/QueryResultInterface composed of different object types.

		$uidList = DirectMailUtility::getStaticIdList('tt_address', $recipientGroup->getUid());
		$result->attach($this->addressRepository->findByUidList($uidList));

		$uidList = DirectMailUtility::getStaticIdList('fe_users', $recipientGroup->getUid());
		$result->attach($this->frontendUserRepository->findByUidList($uidList));

		$uidList = DirectMailUtility::getStaticIdList('fe_groups', $recipientGroup->getUid());
		$result->attach($this->frontendUserRepository->findByUidList($uidList));

		if ($this->userTable) {
			$uidList = DirectMailUtility::getStaticIdList($this->userTable, $recipientGroup->getUid());
			$result->attach($this->mapCustomRecordsFromDatabase($this->userTable, $uidList));
		}

		return $result;
	}

	/**
	 * Returns the recipients within a recipient group using the "special query" setting
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group whose recipients to retrieve
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient> The found recipients
	 */
	protected function findByRecipientGroupSpecialQuery(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup) {
		$mailGroup = BackendUtility::getRecord('sys_dmail', $recipientGroup->getUid());
		$mailGroup = $this->update_SpecialQuery($mailGroup);
		$whichTables = intval($mailGroup['whichtables']);
		$queryGenerator = GeneralUtility::makeInstance('DirectMailTeam\DirectMail\MailSelect');
		if ($whichTables&1) {
			$uidList = DirectMailUtility::getSpecialQueryIdList($queryGenerator, 'tt_address', $mailGroup);
			$result = $this->addressRepository->findByUidList($uidList);
		} elseif ($whichTables&2) {
			$uidList = DirectMailUtility::getSpecialQueryIdList($queryGenerator, 'fe_users', $mailGroup);
			$result = $this->frontendUserRepository->findByUidList($uidList);
		} elseif ($this->userTable && ($whichTables&4)) {
			$uidList = DirectMailUtility::getSpecialQueryIdList($queryGenerator, $this->userTable, $mailGroup);
			$result = $this->mapCustomRecordsFromDatabase($this->userTable, $uidList);
		}
		return $result;
	}

	/**
	 * Returns the recipients within a recipient group using the "other groups" setting
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group whose recipients to retrieve
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient> The found recipients
	 */
	protected function findByRecipientGroupOtherGroups(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup) {
		// Recursively retrieve all recipient groups included by $recipientGroup
		$groups = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		$groups->add($recipientGroup);
		$groups = $this->getMailGroupsRecursive($recipientGroup, $groups);
		$groups = $this->removeInclusionTypeGroups($groups);

		$result = $this->objectManager->get('DirectMailTeam\DirectMail\NestedStorage');
		foreach ($groups as $group) {
			$recipients = $this->findByRecipientGroup($group);
			$result->attach($recipients);
		}

		return $result;
	}

	/**
	 * Recursively retrieves all recipient groups included by the passed recipient group
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup: The recipient group from which to retrieve the included recipient groups
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $accumulatedGroups: The currently accumulated groups
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage The passed $accumulatedGroups variable with the new sub groups from $recipientGroup added
	 */
	protected function getMailGroupsRecursive(\DirectMailTeam\DirectMail\Domain\Model\RecipientGroup $recipientGroup, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $currentGroups) {
		$groups = $recipientGroup->getMailGroups();
		foreach ($groups as $group) {
			if (!$currentGroups->contains($group)) {
				$currentGroups->add($group);
				if ($group->getType()->equals(RecipientGroupType::OTHER_GROUPS)) {
					$currentGroups = $this->getMailGroupsRecursive($group, $currentGroups);
				}
			}
		}
		return $currentGroups;
	}

	/**
	 * Remove all groups which are of type "include groups"
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $groups: The groups which to filter
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage All passed groups except "include group" type groups
	 */
	protected function removeInclusionTypeGroups(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $groups) {
		$result = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		foreach ($groups as $group) {
			if (!$group->getType()->equals(RecipientGroupType::OTHER_GROUPS)) {
				$result->add($group);
			}
		}
		return $result;
	}

	/**
	 * Retrieves the plain database record for the passed domain object. If the domain object is 
	 * a CustomRecipient (or any other custom recipient class) then the "_getProperties" method
	 * will get used to map the properties "email", "first_name" and "last_name" into a plain array.
	 * @todo: Compatibility code. Remove this method when switching to complete new sending code
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: A recipient domain object
	 * @return array 
	 */
	public function getPlainRow(\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient) {
		switch (get_class($recipient)) {
			case 'DirectMailTeam\DirectMail\Domain\Model\Address':
				return BackendUtility::getRecord('tt_address', $recipient->getUid());

			case 'DirectMailTeam\DirectMail\Domain\Model\FrontendUser':
				return BackendUtility::getRecord('fe_users', $recipient->getUid());

			case 'DirectMailTeam\DirectMail\Domain\Model\CustomRecipient':
				if ($recipient->getType() === 'plain') {
					return $this->reverseMapCustomRecipient($recipient);
				} else {
					return BackendUtility::getRecord($recipient->getType(), $recipient->getUid());
				}

			default:
				return BackendUtility::getRecord($recipient->getType(), $recipient->getUid());
		}
	}

	/**
	 * Performs a reverse mapping for a "CustomRecipient" domain model. Will only map the properties
	 * "uid", "email", "name", "first_name" and "last_name" to an associative array.
	 * @todo: Compatibility code. Remove this method when switching to complete new sending code
	 *
	 * @param \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient $recipient: A recipient domain object
	 * @return array An array resembling a plain database row
	 */
	protected function reverseMapCustomRecipient(DirectMailTeam\DirectMail\Domain\Model\CustomRecipient $recipient) {
		return array(
			'uid' => $recipient->getUid(),
			'email' => $recipient->getEmail(),
			'first_name' => $recipient->getFirstName(),
			'last_name' => $recipient->getLastName(),
			'name' => $recipient->getName(),
		);
	}

	/**
	 * Maps a list/array of UIDs to a domain object
	 *
	 * @param string $table: The table from which to retrieve a record
	 * @param array $uidList : The UIDs of the records to map
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient> The mapped domain objects
	 */
	protected function mapCustomRecordsFromDatabase($table, array $uidList) {
		$result = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\ObjectStorage');
		foreach ($uidList as $uid) {
			$object = $this->mapCustomRecordFromDatabase($table, $uid);
			$result->add($object);
		}
		return $result;
	}

	/**
	 * Retrieves a database record and maps it to a CustomRecipient domain model object
	 *
	 * @param string $table: The table from which to retrieve a record
	 * @param integer $uid: The uid of the custom record
	 * @return \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient The constructed domain object
	 */
	protected function mapCustomRecordFromDatabase($table, $uid) {
		$record = BackendUtility::getRecord($table, $uid);
		if (!is_array($record)) {
			throw new \Exception('Couldn\'t retrieve record ' . $uid . ' from table "' . $table . '"!');
		}
		$recipient = $this->mapCustomRecord($record, $table);
		$recipient->setType($table);
	}

	/**
	 * Maps a custom record to a CustomRecipient domain model object
	 *
	 * @param array $record: The record (or plain data array) which should get mapped
	 * @param string $table: The table as which to map the record. Will be "plain" for plain recipients
	 * @return \DirectMailTeam\DirectMail\Domain\Model\Interfaces\Recipient The constructed domain object
	 */
	protected function mapCustomRecord($record, $table) {
		$customRecipientClass = 'DirectMailTeam\DirectMail\Domain\Model\CustomRecipient';
		// @hook-mapCustomRecord: Using the key "customRecipientClass" of the configuration extension space a custom
		// domain model class can get configured for different tables. By default the domain model
		// "customRecipient" will get used which required the table to have fields "email", "first_name"
		// and "last_name" - all other fields are not used.
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['customRecipientClasses'][$table])) {
			$customRecipientClass = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['direct_mail']['customRecipientClasses'][$table];
		}
		return array_shift($this->dataMapper->map($customRecipientClass, array($record)));
	}

}


<?php
defined('TYPO3_MODE') or die('Access denied.');

$LL = 'LLL:EXT:direct_mail/Resources/Private/Language/locallang_tca.xml:';

return array(
	'ctrl' => array(
		'label' => 'mail_job',
		'label_alt' => 'recipient_table,recipient_uid',
		'label_alt_force' => TRUE,
		'default_sortby' => 'ORDER BY uid DESC',
		'tstamp' => 'tstamp',
		'title' => $LL . 'tx_directmail_domain_model_sendqueue',
		'readOnly' => TRUE,
//		'hideTable' => TRUE,
		'iconfile' => TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('direct_mail') . 'res/gfx/mail.gif',
	),
	'interface' => array(
		'showRecordFieldList' => 'crdate,mail_job,recipient_uid,recipient_table,recipient_data,send_status',
	),
	'columns' => array(
		'crdate' => array(
			'label' => $LL . 'tx_directmail_domain_model_sendqueue.crdate',
			'config' => array(
				'type' => 'none',
				'cols' => '30',
				'format' => 'datetime',
				'default' => 0
			)
		),
		'mail_job' => array(
			'label' => $LL . 'tx_directmail_domain_model_sendqueue.mail_job',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'sys_dmail',
				'size' => '1',
				'maxitems' => 1,
				'minitems' => 0,
			)
		),
		'recipient_uid' => array(
			'label' => $LL . 'tx_directmail_domain_model_sendqueue.recipient_uid',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'eval' => 'int',
			)
		),
		'recipient_table' => array(
			'label' => $LL . 'tx_directmail_domain_model_sendqueue.recipient_table',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
				'max' => '64',
			)
		),
		'recipient_data' => array(
			'label' => $LL . 'tx_directmail_domain_model_sendqueue.recipient_data',
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '8',
			)
		),
		'send_status' => array(
			'label' => $LL . 'tx_directmail_domain_model_sendqueue.send_status',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array($LL . 'tx_directmail_domain_model_sendqueue.send_status.I.0', '0'),
					array($LL . 'tx_directmail_domain_model_sendqueue.send_status.I.1', '1'),
					array($LL . 'tx_directmail_domain_model_sendqueue.send_status.I.2', '2'),
				),
				'default' => 0,
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'crdate, mail_job, recipient_uid, recipient_table, recipient_data, send_status')
	),
);


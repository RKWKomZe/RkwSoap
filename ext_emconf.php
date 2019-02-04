<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "rkw_soap"
 *
 * Auto generated by Extension Builder 2015-10-17
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'RKW SOAP',
	'description' => 'SOAP Schnittstelle',
	'category' => 'plugin',
	'author' => 'Steffen Kroggel',
	'author_email' => 'developer@steffenkroggel.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '8.7.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.6.0-8.7.99',
			'extbase' => '7.6.0-8.7.99',
			'fluid' => '7.6.0-8.7.99',
		),
		'conflicts' => array(
            'rkw_basics' => '7.6.0-8.7.1',
            'rkw_mailer' => '7.6.0-8.7.0',
            'rkw_order' => '7.6.0-7.6.99',
            'rkw_registration' => '7.6.0-7.6.99'
		),
		'suggests' => array(
            'rkw_basics' => '8.7.2-8.7.99',
            'rkw_mailer' => '8.7.1-8.7.99',
            'rkw_order' => '8.7.0-8.7.99',
            'rkw_registration' => '8.7.0-8.7.99'
		),
	),
);
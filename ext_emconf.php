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

$EM_CONF[$_EXTKEY] = [
	'title' => 'RKW SOAP',
	'description' => 'SOAP API',
	'category' => 'plugin',
	'author' => 'Steffen Kroggel',
	'author_email' => 'developer@steffenkroggel.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '9.5.7',
	'constraints' => [
		'depends' => [
			'typo3' => '9.5.0-10.4.99',
            'core_extended' => '9.5.4-10.4.99',
        ],
		'conflicts' => [
            'rkw_order' => '0.0.0-99.99.99',
		],
		'suggests' => [
            'rkw_basics' => '9.5.0-10.4.99',
            'postmaster' => '9.5.0-10.4.99',
            'rkw_shop' => '9.5.0-10.4.99',
            'fe_register' => '9.5.0-10.4.99'
		],
	],
];

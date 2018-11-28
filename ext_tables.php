<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Register Plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'RKW.' . $_EXTKEY,
	'Rkwsoap',
	'RKW Soap'
);

// Add TypoScripts
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'RKW SOAP');


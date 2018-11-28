<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// load WSDL Library
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rkw_soap') . 'Classes/Libs/wsdl-creator-master/vendor/autoload.php');


// FE-Plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'RKW.' . $_EXTKEY,
    'Rkwsoap',
    array(
        'Soap' => 'soap',

    ),
    // non-cacheable actions
    array(
        'Soap' => 'soap',
    )
);


// set logger
$GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwSoap']['writerConfiguration'] = array(

    // configuration for WARNING severity, including all
    // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
        // add a FileWriter
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            // configuration for the writer
            'logFile' => 'typo3temp/logs/tx_rkwsoap.log'
        )
    ),
);

?>
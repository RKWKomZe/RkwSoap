<?php

namespace RKW\RkwSoap\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class SoapController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SoapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * action WSDL
     *
     * @return void
     */
    public function wsdlAction()
    {

        // check if an url is set
        if ($this->settings['soapServer']['url']) {
            $this->view->assign('url', $this->settings['soapServer']['url']);
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successful WSDL request from IP %s.', $this->getRemoteIp()));

        } else {

            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 300');
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Service unavailable.');
            exit();
            //===
        }
    }
 

    /**
     * action soap
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
     */
    public function soapAction()
    {

        // kill TYPO3 output buffer
        while (ob_end_clean()) ;

        // include authentification for PHP-CGI
        list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

        // get remote addr
        $remoteAddr = $this->getRemoteIp();

        // only WSDL wanted?
        if (isset($_GET['wsdl'])) {
            
            $this->forward('wsdl');
            exit();
            //===
        }

        // check login
        if (
            (! $this->settings['soapServer']['disableSecurityChecks'])
            || (strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->__toString()) === 'production')
        ){
            if (
                ($_SERVER['PHP_AUTH_USER'] != $this->settings['soapServer']['username'])
                || ($_SERVER['PHP_AUTH_PW'] != $this->settings['soapServer']['password'])
                || (
                    ($allowedIps = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['soapServer']['allowedRemoteIpList'], true))
                    && (!in_array($remoteAddr, $allowedIps))
                )
            ) {

                header('WWW-Authenticate: Basic realm="Checking Authentification"');
                header('HTTP/1.0 401 Unauthorized');
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Login failed for user "%s" from IP %s.', $_SERVER['PHP_AUTH_USER'], $remoteAddr));
                exit;
            }
        }

        // check if an url is set
        if ($this->settings['soapServer']['url']) {

            try {

                $options = array(
                    'uri'      => $this->settings['soapServer']['url'] . '/?type=1445105145',
                    'location' => $this->settings['soapServer']['url'],
                    'style'    => SOAP_RPC,
                    'use'      => SOAP_LITERAL,
                );

                $server = new \SoapServer(null, $options);
                $server->setClass('RKW\RkwSoap\Soap\Server');
                $server->handle();

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successful SOAP call from IP %s. Request: %s', $remoteAddr, str_replace(array("\n", "\r"), '', file_get_contents("php://input"))));
                exit();
                //===

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An error occurred. Message: %s. Request: %s', str_replace(array("\n", "\r"), '', $e->getMessage()), str_replace(array("\n", "\r"), '', file_get_contents("php://input"))));
                header('HTTP/1.1 500 Internal Server Error');
                exit();
                //===
            }
        }

        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 300');
        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Service unavailable.');
        exit();
    }


    /**
     * Returns remote IP
     *
     * @return string
     */
    protected function getRemoteIp()
    {

        // proof remote addr
        $remoteAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ips[0]) {
                $remoteAddr = filter_var($ips[0], FILTER_VALIDATE_IP);
            }
        }

        return $remoteAddr;
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}


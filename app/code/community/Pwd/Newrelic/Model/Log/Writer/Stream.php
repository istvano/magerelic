<?php
/**
 * Pwd Newrelic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Pwd
 * @package     Pwd_Newrelic
 * @copyright   Copyright (c) 2013 Bluespell Ltd.
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Implement new logging by sending log information to new relic
 * 
 * @category   Pwd
 * @package    Pwd_Newrelic
 * @author     Core Team info@practicalwebdevelopment.com
 **/

class Pwd_Newrelic_Model_Log_Writer_Stream extends Zend_Log_Writer_Stream {

    /*
     * const EMERG   = 0;  // Emergency: system is unusable
     * const ALERT   = 1;  // Alert: action must be taken immediately
     * const CRIT    = 2;  // Critical: critical conditions
     * const ERR     = 3;  // Error: error conditions
     * const WARN    = 4;  // Warning: warning conditions
     * const NOTICE  = 5;  // Notice: normal but significant condition
     * const INFO    = 6;  // Informational: informational messages
     * const DEBUG   = 7;  // Debug: debug messages
     */
    const DEFAULT_LOG_PRIORITY_LEVEL = 3;

    /**
     * send certain log messages to new relic.
     * @param array $event
     */
    protected function _write($event) {

        parent::_write($event);

        if( Mage::helper('newrelic')->isEnabled() == false || Mage::helper('newrelic')->isLogErrorEnabled() == false) {
            return $this;
        }

        //send only ERR or more critical log to new relic
        try {
            if ( array_key_exists('priority', $event) && intval( $event['priority'] ) <=self::DEFAULT_LOG_PRIORITY_LEVEL ) {
                $name = array_key_exists('priorityName',$event)?$event['priorityName']:$event['priority'];
                Mage::helper('newrelic')->getClient()->noticeError('['.$name.'] =>' .$event['message']);
            }
        } catch (Exception $e) {
            //do nothing to avoid infinite loop
        }

    }

}

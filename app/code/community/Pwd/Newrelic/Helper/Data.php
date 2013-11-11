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
 * Newrelic default helper
 *
 * @category	Pwd
 * @package		Pwd_Newrelic
  * 
 */
class Pwd_Newrelic_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_BASE_PATH   = 'newrelic/';
    const XML_PATH_ENABLE = 'general/enable';
    const XML_PATH_CLIENT = 'general/client';
    const XML_PATH_MONITOR_ADMIN = 'general/monitor_admin';
    const XML_PATH_MONITOR_RUM = 'general/monitor_rum';
    const XML_PATH_NAMED_TRANS = 'general/named_transactions';
    const XML_PATH_CUSTOM_TRACER = 'general/use_custom_tracer';
    const XML_PATH_XMIT = 'general/xmit';
    const XML_PATH_LOG_ERROR = 'general/log_error';


    const XML_PATH_API_APP_NAME = 'api/application_name';
    const XML_PATH_API_LICENSE = 'api/license';

    const XML_PATH_RUM_CUST_EMAIL = 'rum/record_customer_email';
    const XML_PATH_RUM_CUST_NAME = 'rum/record_customer_name';
    const XML_PATH_RUM_CUST_ID = 'rum/record_customer_id';
    const XML_PATH_RUM_USER_ATTRIB = 'rum/record_user_attributes';
    const XML_PATH_RUM_BLACKLIST = 'rum/modules_blacklist';

    public function isEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_ENABLE, $store);
    }


    /**
     * Get the Newrelic client
     * it returns the Debug client if debug flag is set.
     */
    public function getClient($store = null) {
        $client = Mage::getStoreConfig(self::XML_BASE_PATH.self::XML_PATH_CLIENT, $store);
        if ( is_null($client)) {
            return Mage::getSingleton('newrelic/client_config')->getDefaultClient();
        } else {
            return Mage::getSingleton($client);
        }
    }

    public function isAdminMonitored($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_MONITOR_ADMIN, $store);
    }

    public function isRealUserMonitorEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_MONITOR_RUM, $store);
    }

    public function isCustomerEmailRecordEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_RUM_CUST_EMAIL, $store);
    }

    public function isCustomerNameRecordEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_RUM_CUST_NAME, $store);
    }

    public function isCustomerIdRecordEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_RUM_CUST_ID, $store);
    }

    public function getApplicationName($store = null) {
        return Mage::getStoreConfig(self::XML_BASE_PATH.self::XML_PATH_API_APP_NAME, $store);
    }

    public function getLicense($store = null) {
        return Mage::getStoreConfig(self::XML_BASE_PATH.self::XML_PATH_API_LICENSE, $store);
    }

    public function isXmitEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_XMIT, $store);
    }


    public function isUseNamedTransactionEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_NAMED_TRANS, $store);
    }

    public function isRecordUserAttributes($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_RUM_USER_ATTRIB, $store);
    }

    public function isCustomTracerEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_CUSTOM_TRACER, $store);
    }

    public function isLogErrorEnabled($store = null) {
        return Mage::getStoreConfigFlag(self::XML_BASE_PATH.self::XML_PATH_LOG_ERROR , $store);
    }

    public function isTransactionIgnored($moduleName, $store = null) {

        $blacklist = Mage::getStoreConfig(self::XML_BASE_PATH.self::XML_PATH_RUM_BLACKLIST, $store);

        try {
            $ignorelist = explode(',', $blacklist);
            if (is_array($ignorelist) && count($ignorelist) > 0) {
                foreach($ignorelist as $item) {
                    if ( $item == $moduleName ) {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
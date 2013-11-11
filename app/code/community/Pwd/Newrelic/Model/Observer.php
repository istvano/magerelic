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

class Pwd_Newrelic_Model_Observer {


    /**
     * global listening on controller_front_init_before
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function frontInitBefore(Varien_Event_Observer $observer) {
        if( Mage::helper('newrelic')->isEnabled() == false || Mage::helper('newrelic')->isCustomTracerEnabled() == false ) {
            return $this;
        }

        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage::getModel');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage::getSingleton');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage::helper');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage::log');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage::dispatchEvent');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage::init');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage_Core_Model_App::_initCache');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage_Core_Model_Config::loadDb');
        Mage::helper('newrelic')->getClient()->addCustomTracer('Mage_Core_Model_Config::loadModules');
        Mage::helper('newrelic')->getClient()->addCustomTracer('include');
        Mage::helper('newrelic')->getClient()->addCustomTracer('include_once');
        Mage::helper('newrelic')->getClient()->addCustomTracer('require');
        Mage::helper('newrelic')->getClient()->addCustomTracer('require_once');

    }

    /**
     * global listening on controller_action_predispatch
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function preDispatch(Varien_Event_Observer $observer) {


        if( Mage::helper('newrelic')->isEnabled() == false ) {
            return $this;
        }

        Mage::unregister('newrelic_ignore');

        if(Mage::app()->getStore()->isAdmin() == true && !Mage::helper('newrelic')->isAdminMonitored() ) {
            Mage::register('newrelic_ignore', true);
            Mage::helper('newrelic')->getClient()->ignoreTransaction();
            Mage::helper('newrelic')->getClient()->ignoreApdex();
            return $this;
        }

        $request = Mage::getModel('core/url')->getRequest();

        //check to see if we need to ignore this request blacklist / whitelist

        if (Mage::helper('newrelic')->isTransactionIgnored($request->getControllerModule()) === true) {
            Mage::register('newrelic_ignore', true);
            Mage::helper('newrelic')->getClient()->ignoreTransaction();
            Mage::helper('newrelic')->getClient()->ignoreApdex();
            return $this;
        }

        $appName = Mage::helper('newrelic')->getApplicationName();
        if ( !empty($appName) ) {
            Mage::helper('newrelic')->getClient()->setApplicationName(Mage::helper('newrelic')->getApplicationName(),Mage::helper('newrelic')->getLicense(), Mage::helper('newrelic')->isXmitEnabled());
        }

        Mage::helper('newrelic')->getClient()->captureParams(true);

        if( Mage::helper('newrelic')->isRealUserMonitorEnabled() == false ) {
            return $this;
        }

        // Set request specific info
        Mage::helper('newrelic')->getClient()->addCustomParameter('module', $request->getControllerModule());
        Mage::helper('newrelic')->getClient()->addCustomParameter('route', $request->getRouteName());
        Mage::helper('newrelic')->getClient()->addCustomParameter('controller', $request->getControllerName());
        Mage::helper('newrelic')->getClient()->addCustomParameter('action', $request->getActionName());
        Mage::helper('newrelic')->getClient()->addCustomParameter('requestUri', $request->getRequestUri());
        Mage::helper('newrelic')->getClient()->addCustomParameter('storeId', Mage::app()->getStore()->getId());

        //generate named transaction
        if ( Mage::helper('newrelic')->isUseNamedTransactionEnabled() ) {
            $transactionName = $request->getRouteName() . '/' . $request->getControllerName() . '/' . $request->getActionName().' (module: ' . $request->getModuleName() . ')';
            Mage::helper('newrelic')->getClient()->setNameTransaction($transactionName);
        }

        //log user info when avialable

        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if ( Mage::helper('newrelic')->isCustomerNameRecordEnabled() ) {
            Mage::helper('newrelic')->getClient()->addCustomParameter('customer_name', $this->getCustomerName($customer));
        }

        if ( Mage::helper('newrelic')->isCustomerEmailRecordEnabled() ) {
            Mage::helper('newrelic')->getClient()->addCustomParameter('customer_email', $this->getCustomerEmail($customer));
        }

        if ( Mage::helper('newrelic')->isCustomerIdRecordEnabled() ) {
            Mage::helper('newrelic')->getClient()->addCustomParameter('customer_id', $this->getCustomerId($customer));
        }

    }

    /**
     * global listening on controller_action_postdispatch
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function postDispatch(Varien_Event_Observer $observer) {

        if( Mage::helper('newrelic')->isEnabled() == false ) {
            return $this;
        }

        if( Mage::helper('newrelic')->isRealUserMonitorEnabled() == false ) {
            return $this;
        }

        $ignore = Mage::registry('newrelic_ignore');

        if ( $ignore ) {
            return $this;
        }

        $category = Mage::registry('current_category');
        if( !empty($category) ) {
            Mage::helper('newrelic')->getClient()->addCustomParameter('category_name', $category->getName() );
            Mage::helper('newrelic')->getClient()->addCustomParameter('category_id', $category->getId() );
        }

        $product = Mage::registry('current_product');
        if( !empty($product) ) {
            $productSku = $product->getSku();

            Mage::helper('newrelic')->getClient()->addCustomParameter('product_sku', $productSku );
            Mage::helper('newrelic')->getClient()->addCustomParameter('product_name', $product->getName() );
            Mage::helper('newrelic')->getClient()->addCustomParameter('product_id', $product->getId() );

        } else {
            $productSku = "";
        }

        if ( Mage::helper('newrelic')->isRecordUserAttributes() && !empty($productSku)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $userIdentification = $this->getCustomerIdentification($customer);
            $username = Mage::helper('newrelic')->isCustomerNameRecordEnabled()?$this->getCustomerName($customer):'hidden';
            Mage::helper('newrelic')->getClient()->setUserAttributes($userIdentification, $username, $productSku);
        }
    }

    /*
     * Listen to the event core_block_abstract_to_html_after
     * adding Newrelic RUM timing code, please note this just inserts the timing
     * header as the 1st element on the page, unfortunately in magento there is
     * no block before the head nor after_head_start so that we could access this place
     * this call must be before any js is being loaded
     *
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer) {

        if( Mage::helper('newrelic')->isEnabled() == false || Mage::helper('newrelic')->isRealUserMonitorEnabled() == false) {
            return $this;
        }

        // Fetch objects from this event
        $transport = $observer->getEvent()->getTransport();
        $block = $observer->getEvent()->getBlock();

        if( $block->getNameInLayout() != 'head') {
            return false;
        }

        // Add JavaScript to the header, footer is added via layout.xml file
        if($block->getNameInLayout() == 'head') {
            $extraHtml = Mage::helper('newrelic')->getClient()->getBrowserTimingHeader();
            $html = $extraHtml."\n".$transport->getHtml();
            $transport->setHtml($html);
        }

    }


    /**
     * crontab event space listening on default and always
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function executeCron(Varien_Event_Observer $observer) {
        if( Mage::helper('newrelic')->isEnabled() == false ) {
            return $this;
        }

        Mage::helper('newrelic')->getClient()->markBackgroundJob();
    }

    /**
     * return customer identification information depending on config settings. Used to be compliant re: sharing
     * consumer information
     * @param $customer
     * @return string
     */
    private function getCustomerIdentification($customer) {
        $id = 'hidden';
        if (  Mage::helper('newrelic')->isCustomerEmailRecordEnabled() ) {
            $id = $this->getCustomerEmail($customer);
        } else if ( Mage::helper('newrelic')->isCustomerIdRecordEnabled() ) {
            $id = $this->getCustomerId($customer);
        }
        return $id;
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCustomerId($customer) {
        $customerId = trim($customer->getId());
        if(empty($customerId)) $customerId = '-1';
        return $customerId;
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCustomerEmail($customer) {
        $customerEmail = trim($customer->getEmail());
        if(empty($customerEmail)) $customerEmail = 'guest';
        return $customerEmail;
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCustomerName($customer) {
        $customerName = trim($customer->getName());
        if(empty($customerName)) $customerName = 'guest';
        return $customerName;
    }

}
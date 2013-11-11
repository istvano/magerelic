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
 * Abstract Class to implement a new relic client
 *
 * @category	Pwd
 * @package		Pwd_Newrelic
 * @author      Istvan Orban
 */
abstract class Pwd_Newrelic_Model_Client_Abstract {

    protected $_enabled;

    protected $_backgroundjob = false;

    /**
     * Define node
     *
     */
    public function __construct()
    {
        $this->_enabled = Mage::helper('newrelic')->isEnabled()?true:false;
    }

    public function isEnabled() {
        return $this->_enabled;
    }

    /**
     * in new relic call this if the appname is defined
     * if(!empty($appName)) newrelic_set_appname($appName, $license, $xmit);
     */
    public abstract function setApplicationName($name);

    /**
     * in new relic call ->
    newrelic_capture_params(true);
     * @return null
     */
    public abstract function captureParams();

    /**
     *
     * record custom parameters to the call newrelic_add_custom_parameter()
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public abstract function addCustomParameter($name, $value);

    /**
     * newrelic_ignore_transaction();
     * @return $this
     */
    public abstract function ignoreTransaction();

    /**
     * newrelic_ignore_apdex();
     * @return $this
     */
    public abstract function ignoreApdex();

    /**
     * record newrelic_set_user_attributes
     * @param $user
     * @param $account
     * @param $product
     */
    public abstract function setUserAttributes($user, $account, $product);

    /**
     * record newrelic_name_transaction
     * change the name of the transaction
     * @param $name
     */
    public abstract function setNameTransaction($name);

    /**
     * return newrelic_get_browser_timing_header();
     */
    public abstract function getBrowserTimingHeader();

    /**
     * return newrelic_get_browser_timing_footer();
     */
    public abstract function getBrowserTimingFooter();


    /**
     * setting up new relic tracers using newrelic_add_custom_tracer()
     * @param $name
     * @return $this
     */
    public abstract function addCustomTracer($name);

    /**
     *
     * call newrelic_notice_error (message [, exception] )
     *
     * @param $message
     * @param null $exception
     * @return $this
     */
    public abstract function noticeError($message, $exception = null);


    public function isMarkedBackground() {
        return $this->_backgroundjob == true;
    }

    public function markBackgroundJob() {
        $this->_backgroundjob = true;
        return $this;
    }
}
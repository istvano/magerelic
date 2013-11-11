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
 * New relic RUM footer script
 *
 * @category   Pwd
 * @package    Pwd_Newrelic
 * @author     Pwd Team <info@practicalwebdev.com>
 */
class Pwd_Newrelic_Block_Footer extends Mage_Core_Block_Template
{

    /**
     * Render footer tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if( Mage::helper('newrelic')->isEnabled() == false ) {
            return '';
        }

        return Mage::helper('newrelic')->getClient()->getBrowserTimingFooter();
    }
}

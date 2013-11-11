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

class Pwd_Newrelic_Model_Client_Config  extends Mage_Core_Model_Config_Base
{

    const XML_PATH_ENGINES = 'global/pwd_newrelic/clients/';
    const DEFAULT_CLIENT = 'newrelic/client_debug';

    protected $_clients;
    protected $_default;

    /**
     * Define node
     *
     */
    public function __construct()
    {
        parent::__construct(Mage::getConfig()->getNode()->global->pwd_newrelic);
    }

    /**
     * build the clients array from config
     *
     */
    public function getClientsArray()
    {
        if (is_null($this->_clients)) {
            $clients = $this->getNode('clients')->asArray();

            foreach ($clients as $clientCode => $clientConfig) {
                $moduleName = 'newrelic';
                if (isset($clientConfig['@']['module'])) {
                    $moduleName = $clientConfig['@']['module'];
                }
                $translatedLabel = Mage::helper($moduleName)->__($clientConfig['label']);
                $clients[$clientCode]['label'] = $translatedLabel;
            }
            $this->_clients = $clients;
        }
        return $this->_clients;
    }

    public function getDefaultClient() {
        $clients = $this->getClientsArray();

        if ( is_null($this->_default)) {
            foreach ($clients as $clientConfig) {
                if (isset($clientConfig['@']['default'])) {
                    $this->_default = Mage::getSingleton((string)$clientConfig['model']);
                }
            }
        }

        if ( is_null($this->_default)) {
            $this->_default = Mage::getSingleton((string)Pwd_Newrelic_Model_Client_Config::DEFAULT_CLIENT);
        }

        return $this->_default;
    }

}

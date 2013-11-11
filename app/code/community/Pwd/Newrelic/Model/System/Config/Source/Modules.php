<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Istvan Orban
 * Date: 07/11/13
 * Time: 22:26
 * To change this template use File | Settings | File Templates.
 */

class Pwd_Newrelic_Model_System_Config_Source_Modules {

    /**
     * Retrieve page layout options array
     *
     * @return array
     */
    public function toOptionArray($withEmpty = false)
    {
        $options = array();

        $config = Mage::getConfig();
        foreach($config->getNode('modules')->children() as $item) {
            $options[] = array(
                'label' => $item->getName(),
                'value' => $item->getName()
            );
        }

        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label'=>Mage::helper('page')->__('-- Please Select --')));
        }

        return $options;
    }


}
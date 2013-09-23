<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to suporte.developer@buscape-inc.com so we can send you a copy immediately.
 *
 * @category   Buscape
 * @package    Buscape_Sitemap
 * @copyright  Copyright (c) 2010 Buscapé Company (http://www.buscapecompany.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Buscape_Sitemap_Model_Config extends Varien_Object
{
    const XML_PATH          = 'web/buscapemap/';
    
    const XML_PATH_ACTIVE   = 'web/buscapemap/active';
    
    const XML_PATH_ACCOUNT  = 'web/buscapemap/account';
    
    protected $_config = array();
    
    public function getConfigData($key, $storeId = null)
    {
        if (!isset($this->_config[$key][$storeId])) {
            $value = Mage::getStoreConfig(self::XML_PATH . $key, $storeId);
            $this->_config[$key][$storeId] = $value;
        }
        return $this->_config[$key][$storeId];
    }
    
    public function getAccount($store = null)
    {
        if (!$this->hasData('buscapemap_account')) {
            $this->setData('buscapemap_account', $this->getConfigData('account', $store));
        }
        
        return $this->getData('buscapemap_account');
    }
}
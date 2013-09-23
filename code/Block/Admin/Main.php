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

class Buscape_Sitemap_Block_Admin_Main extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_headerText = Mage::helper('buscapemap')->__('Sitemap(s) BuscaPé');
        
        $this->_blockGroup = 'buscapemap';
        
        $this->_controller = 'admin_main';
        
        $this->_addButton('add', array(
            'label'   => Mage::helper('buscapemap')->__('Adicionar Sitemap BuscaPé / Bondfaro'),
            'onclick' => "setLocation('{$this->getUrl('*/*/express', array('type' => 'buscape'))}')",
            'class'   => 'add'
        ));
    }
}
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

class Buscape_Sitemap_Block_Admin_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
 
        $fieldset = $form->addFieldset('new_sitemap', array('legend' => Mage::helper('buscapemap')->__('Sitemap Details')));
 
        $fieldset->addField('filename', 'text', array(
            'name'      => 'filename',
            'title'     => Mage::helper('buscapemap')->__('Filename'),
            'label'     => Mage::helper('buscapemap')->__('Filename'),
            'maxlength' => '250',
            'note'      => Mage::helper('adminhtml')->__('exemplo: sitemap.xml'),
            'required'  => true,
        ));
 
        $fieldset->addField('path', 'text', array(
            'name'      => 'path',
            'title'     => Mage::helper('buscapemap')->__('Path'),
            'label'     => Mage::helper('buscapemap')->__('Path'),
            'maxlength' => '250',
            'note'  	=> Mage::helper('adminhtml')->__('examplo: sitemap/'),
            'required'  => true,
        ));             
        
        $store_array = array("" => "Choose an option" );
        
    	$stores = Mage::app()->getStores();
        
        foreach( $stores as $store )
        {
            $store_array[$store->getId()] = $store->getName();	
        }        
        
        $fieldset->addField('store_id', 'select', array(
            'name'     => 'store_id',
            'title'    => Mage::helper('buscapemap')->__('Store ID'),
            'label'    => Mage::helper('buscapemap')->__('Store ID'),
            'required' => true,
        ))->setValues($store_array);        
		
        $site_model = array(
            "" => "Choose an option",
            "buscape"   => "Buscapé",
            "quebarato" => "QueBarato",
            "bondfaro"  => "Bondfaro" );
        
        $fieldset->addField('site_model', 'select', array(
                'name'     => 'site_model',
                'title'    => Mage::helper('buscapemap')->__('Modelo de XML'),
                'label'    => Mage::helper('buscapemap')->__('Modelo de XML'),
                'required' => true,
        ))->setValues($site_model);	
 
        $form->setMethod('post');
        
        $form->setUseContainer(true);
        
        $form->setId('edit_form');
        
        $form->setAction($this->getUrl('*/*/post'));
 
        $this->setForm($form);
    }
}
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

class Buscape_Sitemap_Block_Admin_Main_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setUseAjax(false);
        $this->setId('buscapemapGrid');
        $this->_controller = 'buscapemap';
    }
 
    protected function _prepareCollection()
    {
        $model      = Mage::getModel('buscapemap/sitemap');
        
        $collection = $model->getCollection();
        
	$this->setCollection($collection);
 
        return parent::_prepareCollection();
    }
 
 
 
    protected function _prepareColumns()
    {
 
        $this->addColumn('sitemap_id', array(
            'header'        => Mage::helper('buscapemap')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'sitemap_id',
            'index'         => 'sitemap_id',
        ));
 
        $this->addColumn('filename', array(
            'header'        => Mage::helper('buscapemap')->__('Nome do Arquivo'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'filename',
            'index'         => 'filename',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));
        
        $this->addColumn('path', array(
            'header'        => Mage::helper('buscapemap')->__('Caminho'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'path',
            'index'         => 'path',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));        
        
        $this->addColumn('link', array(
            'header'        => Mage::helper('buscapemap')->__('Link'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'link',
            'index'         => 'link',
            //'type'          => 'input',
            'truncate'      => 50,
            'escape'        => true,
            'renderer' => 'buscapemap/admin_grid_renderer_link'
        ));

        $this->addColumn('site_model', array(
            'header'        => Mage::helper('buscapemap')->__('Tipo'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'site_model',
            'index'         => 'site_model',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));         
        
        $this->addColumn('last_time_created', array(
            'header'        => Mage::helper('buscapemap')->__('Última data de criação'),
            'align'         => 'left',
            'width'         => '150px',
            'filter_index'  => 'dt.last_time_created',
            'index'         => 'last_time_created',
            'type'          => 'datetime',
            'truncate'      => 50,
            'escape'        => true,
        ));
                
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('buscapemap')->__('Ação'),
                'width'     => '150px',
                'type'      => 'action',
                'getter'	=> 'getSitemapId',
                'actions'   => array(
                    #array(
                    #    'caption' => Mage::helper('buscapemap')->__('Editar'),
                    #    'url'     => array(
                    #        'base'=>'*/*/edit'
                    #     ),
                    #     'field'   => 'id'
                    #),
                    array(
                        'caption' => Mage::helper('buscapemap')->__('Apagar'),
                        'url'     => array(
                            'base'=>'*/*/delete'
                         ),
                         'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('buscapemap')->__('Atualizar XML'),
                        'url'     => array(
                            'base'=>'*/*/generate'
                         ),
                         'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));
 
        return parent::_prepareColumns();
    }
}
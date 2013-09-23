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

class Buscape_Sitemap_AdminController extends Mage_Adminhtml_Controller_Action
{
    
    public function indexAction()
    {
    	$this->loadLayout()
            ->_addContent($this->getLayout()->createBlock('buscapemap/admin_main'))
            ->renderLayout();    		    	
    }
 
    public function deleteAction()
    {    	
        $sitemapId = $this->getRequest()->getParam('id', false);

        try {
        	
            $buscapemap   = Mage::getModel('buscapemap/sitemap')->load($sitemapId);
            
            $d_buscapemap = $buscapemap->getData();

            $io = new Varien_Io_File();
            
            $io->open(array('path' => $d_buscapemap["path"]));
            
            if( $io->fileExists( $d_buscapemap["filename"] ) )
            {
                $io->rm($d_buscapemap["filename"]);
            }
            
            $io->close();
        	
            Mage::getModel('buscapemap/sitemap')->setId($sitemapId)->delete();
           
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('buscapemap')->__('Sitemap deletado com sucesso.'));
            
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

    }
 
    public function newAction()
    {
        $this->loadLayout()
        ->_addContent($this->getLayout()->createBlock('buscapemap/admin_new'))
        ->renderLayout();
    }
 
    public function expressAction()
    {
        $type = $this->getRequest()->getParam('type', false);
        
        if(!$type) {
            
            Mage::getSingleton('adminhtml/session')->addNotice("Tipo do Sitemap não definido.");
            
            return;
        }
        
        $validate = Mage::getModel('buscapemap/sitemap')
                ->getCollection();
        
        $validate->addFilter('filename', "{$type}.xml");
        
        if(count($validate) > 0) {
            
            switch($type) {
                case 'buscape':
                    Mage::getSingleton('adminhtml/session')->addNotice("Sitemap do BuscaPé / Bondfaro já foi criado.");
                break;
                case 'quebarato':
                    Mage::getSingleton('adminhtml/session')->addNotice("Sitemap do QueBarato! já foi criado.");
                break;
                default:
                    Mage::getSingleton('adminhtml/session')->addNotice("Sitemap já foi criado.");
                break;
            }
            
            $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            
            return;
        }
        
        $data = array();
        
        $data['filename'] = "{$type}.xml";
        
        $data['path'] = 'sitemap/';
        
        $data['store_id'] = Mage::app()->getStore()->getStoreId();
        
        $data['site_model'] = "{$type}";
        
        $data["last_time_created"] = date("Y-m-d H:i:s");
        
        $sitemap = Mage::getModel('buscapemap/sitemap')->setData($data);
        
        try {
                    
            $sitemap->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('buscapemap')->__('Sitemap foi alterado com sucesso.'));

            $sitemap->_generateXml($data);

            $this->getResponse()->setRedirect($this->getUrl('*/*/'));

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }
    
    public function postAction()
    {
        if ($data = $this->getRequest()->getPost()) {
        	
            if(strlen($data["path"]) <= 1) {
                
                Mage::getSingleton('adminhtml/session')->addError('Nome do caminho inválido: '.$data["path"].', tente sitemap/');
            } else {
                
                $data["link"] 			   = "/";
                
                $data["last_time_created"] = date("Y-m-d H:i:s");

                $sitemap = Mage::getModel('buscapemap/sitemap')->setData($data);
                
                try {
                    
                    $sitemap->save();
                    
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('buscapemap')->__('Sitemap foi alterado com sucesso.'));

                    $sitemap->_generateXml($data);

                    $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                    
                } catch (Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
        
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }
    
    public function editAction()
    {
        $this->loadLayout();
        
        $this->_addContent($this->getLayout()->createBlock('buscapemap/admin_edit'));
        
        $this->renderLayout();
    }
 
    public function saveAction()
    {
        $sitemapId = $this->getRequest()->getParam('sitemap_id', false);

        $buscapemap   = Mage::getModel('buscapemap/sitemap')->load($sitemapId);
        
        $d_buscapemap = $buscapemap->getData();        
        
        $io = new Varien_Io_File();
        
        $io->open(array('path' => $d_buscapemap["path"]));
        
        if( $io->fileExists( $d_buscapemap["filename"] ) ) {
            
            $io->rm($d_buscapemap["filename"]);
        }
        
        $io->close();        
        
        if($data = $this->getRequest()->getPost()) {
            
            $sitemap = Mage::getModel('buscapemap/sitemap')->load($sitemapId)->addData($data);
            
            try {
                
                $sitemap->setId($sitemapId)->save();
 
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('buscapemap')->__('Site'));
                
                $sitemap->_generateXml($data);
                
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirectReferer();
    }
    
    public function generateAction()
    {
        
    	$sitemapId = $this->getRequest()->getParam('id', false);
        
    	$sitemap   = Mage::getModel('buscapemap/sitemap')->load($sitemapId);    
        
    	$data  = $sitemap->getData();
    	
    	$data["last_time_created"] = date("Y-m-d H:i:s");
        
    	$sitemap->setData($data);
        
    	$sitemap->save();
    	
    	$sitemap->_generateXml($data);
    	
    	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('buscapemap')->__('Sitemap foi gerado com sucesso.'));
    	
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));  	
    }
}
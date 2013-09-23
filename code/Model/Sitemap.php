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

class Buscape_Sitemap_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{

    protected function _construct()
    {
        $this->_init('buscapemap/sitemap');
    }
    
    protected function _getConfig()
    {
        return Mage::getModel('buscapemap/config');
    }
    
    protected function _beforeSave()
    {
        $io = new Varien_Io_File();
        
        $realPath = $io->getCleanPath(Mage::getBaseDir() . '/' . $this->getData('path'));

        $io->setAllowCreateFolders(true);
        
        $io->open(array('path' => $this->getData('path')));
        
        /**
         * Check path is allow
         */
        if (!$io->allowedPath($realPath, Mage::getBaseDir())) {
            Mage::throwException(Mage::helper('buscapemap')->__('Please define correct path'));
        }
        
        /**
         * Check exists and writeable path
         */
        if (!$io->fileExists($realPath, false)) {
            Mage::throwException(Mage::helper('buscapemap')->__('Please create the specified folder "%s" before saving the sitemap.', Mage::helper('core')->htmlEscape($this->getSitemapPath())));
        }

        if (!$io->isWriteable($realPath)) {
            Mage::throwException(Mage::helper('buscapemap')->__('Please make sure that "%s" is writable by web-server.', $this->getSitemapPath()));
        }
        
        /**
         * Check allow filename
         */
        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getFilename())) {
            Mage::throwException(Mage::helper('buscapemap')->__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in the filename. No spaces or other characters are allowed.'));
        }
        
        if (!preg_match('#\.xml$#', $this->getFilename())) {
            $this->setSitemapFilename($this->getFilename() . '.xml');
        } else {
            $this->setSitemapFilename($this->getFilename());
        }

        $this->setSitemapPath(rtrim(str_replace(str_replace('\\', '/', Mage::getBaseDir()), '', $realPath), '/') . '/');

        $this->setLink($this->getPrepareLink());
        
        return parent::_beforeSave();
    }
    
    /**
     * Return full link of xml file
     *
     * @return string
     */
    public function getPrepareLink()
    {
        $baseUrl = Mage::app()->getStore($this->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        
        return $baseUrl . $this->getData('path') . $this->getFilename();
    }
    
    public function _generateXml($data)
    {
        switch($data['site_model']) {
            case 'buscape':
                
                if(is_null($this->_getConfig()->getAccount())) {
                    throw new Exception('Necessário incluir Código da Central de Négócios nas configurações.');
                    return;
                }
                
                $this->xmlBuscape();
            break;
            case 'quebarato':
                $this->xmlQuebarato();
            break;
            default:
                Mage::getSingleton('adminhtml/session')->addNotice("Tipo do XML não definido.");
                return;
            break;
        }
        
        return $this;
    }
    
    public function xmlBuscape()
    {
        $storeId = $this->getStoreId();
        
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        
        /**
         * Generate products sitemap
         */
        $store = Mage::app()->getStore();
        
        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter('type_id',
                    array('in' =>
                        array(
                            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
                    )))
				->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
				->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        
        /*
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id=entity_id', null, 'inner')
            ->joinField('category_path', 'catalog_category_entity', 'path', 'entity_id=category_id', null, 'inner')                
            ->groupByAttribute('entity_id');*/
        
        $i = 1;
        
        $files = 1;
        
        $lines = 2000;
        
        $size = $collection->count();
        
        foreach($collection as $item) {
            
            try {
            
                if($i == 1) {            

                    $io = new Varien_Io_File();

                    $io->setAllowCreateFolders(true);

                    $io->open(array('path' => $this->getData('path')));

                    //$filename = str_replace(".xml", "{$files}.xml", $this->getSitemapFilename());

                    $filename = $this->getSitemapFilename();

                    $this->setSitemapFilename($filename);

                    if ($io->fileExists($this->getSitemapFilename()) && !$io->isWriteable($this->getSitemapFilename())) {
                        Mage::throwException(Mage::helper('buscapemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getSitemapFilename(), $this->getPath()));
                    }

                    if($io->fileExists($this->getSitemapFilename()))
                    {
                        $io->rm($this->getSitemapFilename());
                    }

                    $io->streamOpen($filename);

                    $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>');

                    $io->streamWrite("\n<sitemap>\n");
                }
            
                $product = Mage::getModel("catalog/product")->load($item->getId());
            
                $template = <<<EOT
<produto>
    <descricao>%s</descricao>
    <preco>%s</preco>
    <id_produto>%s</id_produto>
    <codigo_barra>%s</codigo_barra>
    <link_prod>%s</link_prod>
    <imagem>%s</imagem>
    <categ>%s</categ>
    <estoque>%s</estoque>
    <id_filial>%s</id_filial>
</produto>
EOT;
                $xml = sprintf($template,
                    str_replace("&", " ", $product->getName()),
                    str_replace(".", ",", Mage::helper('checkout')->convertPrice($product->getFinalPrice(), false)),
                    $product->getId(),
                    $product->getEan(),
                    $product->getProductUrl(),
                    $product->getImageUrl(),
                    str_replace("/", ":", $product->getCategoryCollection()->getFirstItem()->getPath()),
                    intval($product->getStockItem()->getQty()),   
                    $this->_getConfig()->getAccount()
                );


                $xml .= "\n";

                $io->streamWrite($xml);
            
                //if ($lines == $i || $size == $i) {

                if ($size == $i) {
                    
                    $io->streamWrite("\n</sitemap>\n");
                    
                    $io->streamClose();
                    
                    $i = 1;
                    
                    $files++;
                }

                $i++;
                
            } catch(Exception $e) {
                
                $size--;
                
                Mage::logException($e);
                
                continue;
            }
        }
        
        unset($collection);
        
        // verifica a quantidade de arquivos, caso tenham mais de 1 xml, deve ser criado o HTML e declarado o caminho do(s) xml nele.
        
        /*
         * $io = new Varien_Io_File();
         * $io->setAllowCreateFolders(true);
         * $io->open(array('path' => $this->getData('path')));
         * $filename = str_replace(".xml", "{$files}.xml", $this->getSitemapFilename());

        foreach($listxml as $files)
        {
            $io->streamWrite('<a href="'.$files.'">'.$files.'</a><br />');
        }
        
        $io->streamClose();*/
        
        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        
        $this->save();
    }
    
    public function xmlQuebarato()
    {
        $io = new Varien_Io_File();
        
        $io->setAllowCreateFolders(true);
        
        $io->open(array('path' => $this->getData('path')));
        
        if ($io->fileExists($this->getSitemapFilename()) && !$io->isWriteable($this->getSitemapFilename())) {
            Mage::throwException(Mage::helper('buscapemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $this->getSitemapFilename(), $this->getPath()));
        }
        
        if($io->fileExists($this->getSitemapFilename()))
        {
            $io->rm($this->getSitemapFilename());
        }

        $io->streamOpen($this->getSitemapFilename());
        
        $io->streamWrite("<ad:Ads xmlns:ad='http://www.quebarato.com.br/Ads' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.quebarato.com.br/Ads http://www.quebarato.com.br/Ad.xsd'>");
        
        $storeId = $this->getStoreId();
        
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        
        /**
         * Generate products sitemap
         */
        $collection = Mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->getCollection();
        
        foreach($collection as $item) {
            
            $product = Mage::getModel("catalog/product")->load($item->getId());
            
            $template = <<<EOT
<ad:Ad>
    <ad:Details>
        <ad:Title>%s</ad:Title>
        <ad:Description><![CDATA[
            %s  
        ]]>
        </ad:Description>
        <ad:ItemCondition value='novo' />
        <ad:Price currency='%s' value='%s'/>
    </ad:Details>
    <ad:Address xsi:type='ad:'>
        <ad:zip>%s</ad:zip>
    </ad:Address>
    <ad:Category value='%s' />
    <ad:Pictures>			        	
        <ad:PictureURI>%s</ad:PictureURI>
    </ad:Pictures>
</ad:Ad>
EOT;
            
            try {
                
                $xml .= sprintf($template,
                    str_replace("&", " ", $product->getName()),
                    str_replace("&", " ", $product->getDescription()),
                    Mage::app()->getBaseCurrencyCode(),
                    str_replace(".", ",", Mage::helper('checkout')->convertPrice($product->getFinalPrice(), false)),
                    'valor zip',
                    'categoria',
                    $product->getImageUrl()
                );
                
                $xml .= "\n";

                $io->streamWrite($xml);
            
            } catch(Exception $e) {
                
                continue;
            }
        }
        
        unset($collection);
        
        $io->streamWrite("</ad:Ads>");
        
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        
        $this->save();
    }
}
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

        $io = new Varien_Io_File();

        $io->setAllowCreateFolders(true);

        $io->open(array('path' => $this->getData('path')));

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

        foreach($collection as $item) {
            
            try {
            
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

                $img = '';
                try {
                    $img = $product->getImageUrl();
                } catch (Exception $e) {
                    $img = '';
                }

                $shortDesc = json_decode($product->getShortDescription());
                
                if ($shortDesc->min_price <= 0 || $shortDesc->qty <= 0)
                    Mage::throwException(Mage::helper('buscapemap')->__('Product with no quantity or no price.'));

                $xml = sprintf($template,
                    str_replace("&", " ", $product->getName()),
                    str_replace(".", ",", Mage::helper('checkout')->convertPrice($shortDesc->min_price, false)),
                    $product->getId(),
                    $product->getEan(),
                    $product->getProductUrl(),
                    $img,
                    str_replace("/", ":", $product->getCategoryCollection()->getFirstItem()->getPath()),
                    intval($shortDesc->qty),   
                    $this->_getConfig()->getAccount()
                );

                $xml .= "\n";
                $io->streamWrite($xml);
    
            } catch(Exception $e) {
                Mage::logException($e);
                continue;
            }
        }
        
        $io->streamWrite("\n</sitemap>\n");
        $io->streamClose();

        unset($collection);
        
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
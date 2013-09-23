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

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('buscape_sitemap')} (
      `sitemap_id` int(10) unsigned NOT NULL auto_increment,
      `filename` varchar(250) NOT NULL default '',
      `path` varchar(250) default NULL,
      `link` varchar(250) default NULL,
      `store_id` smallint(5) unsigned NULL,
      `site_model` varchar(250) default NULL,
      `last_time_created` datetime default NULL,  
      PRIMARY KEY  (`sitemap_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
 
$installer->endSetup();
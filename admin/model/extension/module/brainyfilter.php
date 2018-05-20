<?php
require_once DIR_SYSTEM . 'library/sqllib.php';

class ModelExtensionModuleBrainyFilter extends Model 
{
    const TBL_FILTER = 'bf_filter';
    const TBL_PRODUCT_ATTR_VAL = 'bf_product_attribute_value';
    const TBL_ATTR_VAL = 'bf_attribute_value';
    const TBL_ATTR_VAL_BACKUP = 'bf_attribute_value_backup';
    const TBL_TAX_RATE = 'bf_tax_rate';
    const TBL_TMP = 'bf_tmp';
    
    public $attrSeparator = false;
    
    protected $inStock = 7;
    
    protected static $languages = array();

    public function __construct($registry) 
    {
        parent::__construct($registry);
        
        $settings = $this->config->get('brainyfilter_layout_basic');
        if (!is_null($settings)) {
            if (isset($settings['global']['multiple_attributes']) 
                    && $settings['global']['multiple_attributes'] == 1
                    && !empty($settings['global']['attribute_separator'])) {
                $this->attrSeparator = $settings['global']['attribute_separator'];
            }
            if (isset($settings['global']['instock_status_id'])) {
                $this->inStock = $settings['global']['instock_status_id'];
            }
        }
        
        SqlStatement::$DB_PREFIX = DB_PREFIX;
        SqlStatement::$DB_CONNECTOR = $this->db;
    }
    
    public function fillCacheTable()
    {
        $this->fillAttrValueTable();
        
        $this->cacheProductProperties();
    }
    
    public function cacheProductProperties($productIds = array())
    {
        $sql = new SqlStatement();
        
        if (!empty($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array($productIds);
            }
            $this->deleteProductProperties($productIds);
            
            foreach ($productIds as $productId) {
                $this->updateProductAttributeValues($productId);
            }
        } else {
           $this->dropFilterTable();
           $this->createFilterTable();
        }
        
        $langs = $this->getLanguages();
        
        foreach ($langs as $langId) {
            $sql1 = new SqlStatement();
            $sql2 = new SqlStatement();
            $sql3 = new SqlStatement();
            $sql4 = new SqlStatement();
            $sql5 = new SqlStatement();
            $sql6 = new SqlStatement();
            $sql7 = new SqlStatement();
            $sql = new SqlStatement();

            $sql->select(array('product_id', 'manufacturer_id', 'quantity', 'stock_status_id'))
                ->from(array('p' => 'product'))
                ->where('p.status = 1');

            if (is_array($productIds) && count($productIds)) {
                $sql->in('product_id', $productIds);
            }

            $sql1->select(array('gid' => 'CONCAT("a", attribute_id)', 'uid' => 'attribute_value_id', 'p.product_id', 'language_id'))
                ->from(array('a' => self::TBL_PRODUCT_ATTR_VAL))
                ->innerJoin(array('p' => $sql), 'p.product_id = a.product_id')
                ->where('language_id = ?', $langId);

            $sql2->select(array('gid' => 'CONCAT("o", v.option_id)', 'uid' => 'v.option_value_id', 'p.product_id', 'language_id'))
                ->from(array('v' => 'product_option_value'))
                ->innerJoin(array('d' => 'option_value_description'), 'v.option_value_id = d.option_value_id')
                ->innerJoin(array('p' => $sql), 'p.product_id = v.product_id')
                ->where('d.language_id = ?', $langId);

            $sql3->select(array('gid' => 'CONCAT("f", d.filter_group_id)', 'uid' => 'v.filter_id', 'p.product_id', 'language_id'))
                ->from(array('v' => 'product_filter'))
                ->innerJoin(array('d' => 'filter_description'), 'v.filter_id = d.filter_id')
                ->innerJoin(array('p' => $sql), 'p.product_id = v.product_id')
                ->where('d.language_id = ?', $langId);

            $sql4->select(array('gid' => '"m0"', 'uid' => 'manufacturer_id', 'p.product_id', 'language_id' => "'{$langId}'"))
                ->from(array('p' => $sql));

            $sql5->select(array('gid' => '"r0"', 'uid' => 'ROUND(AVG(rating))', 'p.product_id', 'language_id' => "'{$langId}'"))
                ->from(array('p' => $sql))
                ->innerJoin(array('r' => 'review'), 'r.product_id = p.product_id')
                ->where("r.status = '1'")
                ->group(array('p.product_id'));

            $sql6->select(array('gid' => '"s0"', 'uid' => 'IF (p.quantity > 0, "' . $this->inStock . '", p.stock_status_id)', 'p.product_id', 'language_id' => "'{$langId}'"))
                ->from(array('p' => $sql));

            $sql7->select(array('gid' => '"c0"', 'uid' => 'cp.path_id', 'p.product_id', 'language_id' => "'{$langId}'"))
                ->from(array('p' => $sql))
                ->innerJoin(array('pc' => 'product_to_category'), 'p.product_id = pc.product_id')
                ->innerJoin(array('cp' => 'category_path'), 'pc.category_id = cp.category_id');

            $union = new SqlStatement();
            $union->union(array($sql1, $sql2, $sql3, $sql4, $sql5, $sql6, $sql7));

            $sqlx = new SqlStatement();
            $sqlx->select(array('CONCAT( GROUP_CONCAT( CONCAT(gid, "l", uid) SEPARATOR "z " ), "z " )', 'product_id', "language_id"))
                 ->from(array('t' => $union))
                ->group(array('product_id', 'language_id'));

            $ins2 = new SqlStatement();
            $ins2->insertInto(self::TBL_FILTER, $union, array('filter_group', 'filter_id', 'product_id', 'language_id'));

            $this->db->query($ins2);
            
            $this->updateStockCache($productIds);
        }
    }
    
    public function updateStockCache($productIds)
    {
        $sql  = new SqlStatement();
        $sqlx = new SqlStatement();
        
        $sql->clean()
            ->select(array('out_of_stock' => 'IF (SUM( IF(pov.quantity IS NULL, p.quantity, pov.quantity) ) > 0, 0, 1)', 'p.product_id'))
            ->from(array('p' => 'product'))
            ->leftJoin(array('pov' => 'product_option_value'), 'pov.product_id = p.product_id')
            ->group(array('p.product_id'));

        if (is_array($productIds) && count($productIds)) {
            $sql->in('p.product_id', $productIds);
        }

        $sqlx->clean()
            ->update(array('f' => self::TBL_FILTER))
            ->innerJoin(array('t' => $sql), 'f.product_id = t.product_id')
            ->set(array('f.out_of_stock = t.out_of_stock'));

        if (is_array($productIds) && count($productIds)) {
            $sqlx->in('f.product_id', $productIds);
        }

        $this->db->query($sqlx);

        $sql->clean()
            ->select(array('out_of_stock' => 'IF (quantity > 0, 0, 1)', 'product_id', 'gid' => 'CONCAT("o", option_id)', 'option_value_id'))
            ->from(array('product_option_value'));

        if (is_array($productIds) && count($productIds)) {
            $sql->in('product_id', $productIds);
        }

        $sqlx->clean()
            ->update(array('f' => self::TBL_FILTER))
            ->innerJoin(array('t' => $sql), 'f.product_id = t.product_id AND f.filter_group = t.gid AND f.filter_id = t.option_value_id')
            ->set(array('f.out_of_stock = t.out_of_stock'));

        if (is_array($productIds) && count($productIds)) {
            $sqlx->in('f.product_id', $productIds);
        }
            
        $this->db->query($sqlx);
    }
    
    public function deleteProductProperties($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        $sql = new SqlStatement();
        $sql->clean()->delete()->from(self::TBL_FILTER)->in('product_id', $productIds);
        $this->db->query($sql);
    }
    
    protected function updateProductAttributeValues($productId)
    {
        $sql = new SqlStatement();
        
        $sql->delete()->from(self::TBL_PRODUCT_ATTR_VAL)->where('product_id = ?', $productId);
        
        $this->db->query($sql);
        
        $attrValues = $this->addAttributeValues($productId);
        
        $sqlValues = array();
        
        if (count($attrValues)) {
            foreach ($attrValues as $val) {
                $sqlValues[] = array(
                    'product_id'         => $productId, 
                    'attribute_id'       => $val['attribute_id'], 
                    'attribute_value_id' => $val['attribute_value_id'], 
                    'language_id'        => $val['language_id']);
            }
            
            $sql->clean()->insertInto(self::TBL_PRODUCT_ATTR_VAL, $sqlValues);
            
            $this->db->query($sql);
        }
    }
    
    protected function getLanguages()
    {
        if (!count(self::$languages)) {
            $sql = new SqlStatement();
            $sql->select(array('language_id'))->from('language')->where('status = 1');
            $res = $this->db->query($sql);
            if ($res->num_rows) {
                foreach ($res->rows as $row) {
                    self::$languages[] = $row['language_id'];
                }
            }
        }
        
        return self::$languages;
    }
    
    /**
     * Fill out bf_attribute_value DB table by attribute values specified for
     * the given product
     * 
     * @param int $productId Product ID
     * @return mixed
     */
    protected function addAttributeValues($productId)
    {
        
        $sql = new SqlStatement();
        
        // retrieve product attributes
        $sql->select(array('attribute_id', 'language_id', 'text'))
            ->from('product_attribute')
            ->where('product_id = ?', $productId);
        
        $res = $this->db->query($sql);
        
        if (!$res || !count($res->rows)) {
            return array();
        }
        
        $values = array();
        // explode values by separator if necessary
        foreach ($res->rows as $row) {
            $subVals = ($this->attrSeparator) 
                    ? explode($this->attrSeparator, $row['text']) 
                    : array($row['text']);
            foreach ($subVals as $v) {
                $v = preg_replace('/(^[\s]+)|([\s]+$)/us', '', $v);
                $values[] = array(
                    'attribute_id' => $row['attribute_id'],
                    'language_id'  => $row['language_id'],
                    'value'        => $v
                );
            }
        }
        
        $sql->clean()->insertInto(self::TBL_ATTR_VAL, $values)->ignore();
        
        // try to insert all the values into bf_attribute_value DB table
        $this->db->query($sql);
        
        // next step is to get back IDs of the attribute values
        $mv = array();
        foreach ($values as $v) {
            $mv[] = "attribute_id = " . $v['attribute_id'] 
                  . " AND language_id = " . $v['language_id'] 
                  . " AND value = '" . $this->db->escape($v['value']) . "'";
        }
        
        $sql->clean();
        $sql->select(array('attribute_value_id', 'attribute_id', 'language_id'))
            ->from(self::TBL_ATTR_VAL)
            ->multipleWhere($mv);
        
        $res = $this->db->query($sql);
        
        return $res->rows;
    }
    
    public function fillTaxRateTable()
    {
        $this->db->query('TRUNCATE ' . DB_PREFIX . self::TBL_TAX_RATE);
        
        $sql = new SqlStatement();
        
        $sql->select(array(
                    'tr2cg.customer_group_id', 
                    'z2gz.country_id', 
                    'z2gz.zone_id', 
                    'tr1.tax_class_id', 
                    'based',
                    'fixed_rate' => 'SUM( IF( tr2.type = "F", rate, 0 ) )',
                    'percent_rate' => 'SUM(IF(tr2.type = "P", rate, 0))',
                ))
            ->from(array('tr1' => 'tax_rule'))
			->leftJoin(array('tr2' => 'tax_rate'), 'tr1.tax_rate_id = tr2.tax_rate_id')
			->innerJoin(array('tr2cg' => 'tax_rate_to_customer_group'), 'tr2.tax_rate_id = tr2cg.tax_rate_id')
			->leftJoin(array('z2gz' => 'zone_to_geo_zone'), 'tr2.geo_zone_id = z2gz.geo_zone_id')
			->leftJoin(array('gz' => 'geo_zone'), 'tr2.geo_zone_id = gz.geo_zone_id')
            ->group(array('tr2cg.customer_group_id', 'z2gz.country_id', 'z2gz.zone_id', 'tr1.tax_class_id', 'based'));
        
        $sql2 = new SqlStatement();
        $sql2->insertInto(self::TBL_TAX_RATE, $sql, 
            array('customer_group_id', 'country_id', 'zone_id', 'tax_class_id', 'based', 'fixed_rate', 'percent_rate'));
        
        $this->db->query($sql2);
    }
    
    public function createFilterTable()
    {
        $this->dropFilterTable();
        
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . self::TBL_FILTER . '` (
            `product_id` int(11) NOT NULL,
            `filter_group` varchar(10) NOT NULL,
            `filter_id` int(11) NOT NULL,
            `language_id` int(11) NOT NULL,
            `out_of_stock` int(1) NOT NULL,
            KEY `product_id` (`product_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
    }
    
    public function dropFilterTable() 
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_FILTER);
    }
    
    public function createProductAttrValueTable()
    {
        $this->dropProductAttrValueTable();
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . self::TBL_PRODUCT_ATTR_VAL . '` (
            `product_id` int(11) NOT NULL,
            `attribute_id` int(11) NOT NULL,
            `attribute_value_id` int(11) NOT NULL,
            `language_id` int(11) NOT NULL,
            PRIMARY KEY (`product_id`,`attribute_value_id`),
            KEY `product_id` (`product_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
        
    }
    
    public function dropProductAttrValueTable()
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_PRODUCT_ATTR_VAL);
    }
    
    public function createTaxRateTable()
    {
        $this->dropTaxRateTable();
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . self::TBL_TAX_RATE . '` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_group_id` int(11) NOT NULL,
            `country_id` int(11) NOT NULL,
            `zone_id` int(11) NOT NULL,
            `tax_class_id` int(11) NOT NULL,
            `based` varchar(10) NOT NULL,
            `percent_rate` decimal(15,4) NOT NULL,
            `fixed_rate` decimal(15,4) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `customer_group_id` (`customer_group_id`,`country_id`,`zone_id`),
            KEY `tax_class_id` (`tax_class_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
        
    }
    
    public function dropTaxRateTable()
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_TAX_RATE);
    }
    
    public function createAttributeValueTable()
    {
        $this->dropAttributeValueTable();
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . self::TBL_ATTR_VAL . "` (
            `attribute_value_id` int(11) NOT NULL AUTO_INCREMENT,
            `attribute_id` int(11) NOT NULL,
            `language_id` int(11) NOT NULL,
            `value` varchar(200) CHARACTER SET utf8 NOT NULL,
            `sort_order` int(11) NOT NULL,
            PRIMARY KEY (`attribute_value_id`),
            UNIQUE KEY `attribute_id` (`attribute_id`,`language_id`,`value`),
            KEY `sort_order` (`sort_order`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;");
    }
    
    public function createAttributeValueTableBackup()
    {
        $this->dropAttributeValueTableBackup();
        

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . self::TBL_ATTR_VAL_BACKUP . "` (
            `attribute_value_id` int(11) NOT NULL AUTO_INCREMENT,
            `attribute_id` int(11) NOT NULL,
            `language_id` int(11) NOT NULL,
            `value` varchar(200) CHARACTER SET utf8 NOT NULL,
            `sort_order` int(11) NOT NULL,
            PRIMARY KEY (`attribute_value_id`),
            UNIQUE KEY `attribute_id` (`attribute_id`,`language_id`,`value`),
            KEY `sort_order` (`sort_order`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;");
        
        $this->db->query("SET SQL_MODE =  'NO_AUTO_VALUE_ON_ZERO'");
        
        $this->db->query("INSERT INTO  `" . DB_PREFIX . self::TBL_ATTR_VAL_BACKUP . "` 
            SELECT * 
            FROM  `" . DB_PREFIX . self::TBL_ATTR_VAL . "`");
        
    }
    
    protected function createProductAttrValueTmpTable()
    {
        $this->dropProductAttrValueTmpTable();
        
        $this->db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . self::TBL_TMP 
            . '(attribute_id int(11), language_id int(11), '
            . 'value varchar(100) CHARACTER SET utf8, '
            . 'product_id int(11), '
            . 'KEY k(attribute_id, language_id, value)) '
            . ' ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
    }
    
    protected function dropProductAttrValueTmpTable()
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_TMP);
    }
    
    public function dropAttributeValueTable()
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_ATTR_VAL);
    }
    
    public function dropAttributeValueTableBackup()
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_ATTR_VAL_BACKUP);
    }
    
    /**
     * The method updates product attribute values.
     * Use this method when changing to/from multiple attribute mode
     * 
     * @return void
     */
    public function fillAttrValueTable()
    {
        $sql = new SqlStatement();
        
        $this->createAttributeValueTableBackup();
        
        $this->db->query('TRUNCATE ' . DB_PREFIX . self::TBL_ATTR_VAL);
        $this->db->query('TRUNCATE ' . DB_PREFIX . self::TBL_PRODUCT_ATTR_VAL);
        
        $getAttrSql = new SqlStatement();
        $getAttrSql->clean()
            ->select(array('attribute_id', 'language_id', 'value' => 'text', 'product_id'))
            ->distinct()
            ->from('product_attribute');
        
        if ($this->attrSeparator) {
            $page = 0;
            $step = 10000;
            $res = $this->db->query($getAttrSql->limit($step, $step * $page));
        
            while (count($res->rows)) {
                $this->createProductAttrValueTmpTable();

                $values = array();
                foreach ($res->rows as $row) {
                    $expl = explode($this->attrSeparator, $row['value']);
                    foreach ($expl as $val) {
                        $val = preg_replace('/(^[\s]+)|([\s]+$)/us', '', $val);
                        $values[] = array($row['attribute_id'], $row['language_id'], $val, $row['product_id']);
                    }
                }
                $sql->clean()->insertInto(
                        self::TBL_TMP, 
                        $values, 
                        array('attribute_id', 'language_id', 'value', 'product_id'))
                    ->ignore();

                $this->db->query($sql);

                $this->_copyFromTmpTable();

                $this->dropProductAttrValueTmpTable();

                $page ++;
                $res = $this->db->query($getAttrSql->limit($step, $step * $page));
            }
        } else {
            $this->createProductAttrValueTmpTable();
            
            $sel = new SqlStatement();
            
            $sql->clean()->insertInto(
                    self::TBL_TMP, 
                    $sel->select(array('attribute_id', 'language_id', 'value' => 'TRIM(text)', 'product_id'))->from('product_attribute'), 
                    array('attribute_id', 'language_id', 'value', 'product_id'))
                ->ignore();
            
            $this->db->query($sql);

            $this->_copyFromTmpTable();

            $this->dropProductAttrValueTmpTable();
        }
        
        $this->recoverAttributeValueSortOrder();
    }
    
    private function _copyFromTmpTable()
    {
        $sel = new SqlStatement();
        $sql = new SqlStatement();
        $sql->clean()
            ->insertInto(
                self::TBL_ATTR_VAL, 
                $sel->select(array('attribute_id', 'language_id', 'value'))->from(self::TBL_TMP),
                array('attribute_id', 'language_id', 'value'))
            ->ignore();

        $this->db->query($sql);

        $sql->clean()
            ->insertInto(
                self::TBL_PRODUCT_ATTR_VAL, 
                $sel->clean()->select(array('product_id', 'av.attribute_id', 'attribute_value_id', 'av.language_id'))
                    ->from(array('pa' => self::TBL_TMP))
                    ->leftJoin(array('av' => self::TBL_ATTR_VAL), 
                          'pa.value = av.value AND '
                        . 'av.language_id = pa.language_id AND '
                        . 'av.attribute_id = pa.attribute_id')
                    ->where('av.value IS NOT NULL'), 
                array('product_id', 'attribute_id', 'attribute_value_id', 'language_id'))
            ->ignore();

        $this->db->query($sql);
        
    }
    
    public function recoverAttributeValueSortOrder()
    {
        // try to recover attribute values sort order
        $sqlStr = " UPDATE " . DB_PREFIX . self::TBL_ATTR_VAL . " AS a1"
                . " SET sort_order = (SELECT sort_order "
                . "     FROM " . DB_PREFIX . self::TBL_ATTR_VAL_BACKUP . " AS a2 "
                . "     WHERE a1.attribute_id = a2.attribute_id "
                . "         AND a1.language_id = a2.language_id "
                . "         AND a1.value = a2.value)";

        $this->db->query($sqlStr);
        $this->dropAttributeValueTableBackup();
    }
    
    public function getFilters()
    {
        $sql = new SqlStatement();
        $sql->select(array('id' => 'fg.filter_group_id', 'name' => 'fgd.name'))
            ->from(array('fg' => 'filter_group'))
            ->innerJoin(array('fgd' => 'filter_group_description'), 'fg.filter_group_id = fgd.filter_group_id')
            ->where('fgd.language_id = ?', (int)$this->config->get('config_language_id'))
            ->order(array('fg.sort_order'));
        
        $res = $this->db->query($sql);
		
        $filters = array();
        if ($res->num_rows) {
            foreach ($res->rows as $row) {
                $filters[$row['id']] = array(
                    'name' => $row['name']
                );
            }
        }
        
        return $filters;
    }
    
    public function getOptions()
    {
        $sql = new SqlStatement();
        $sql->select(array('id' => 'o.option_id', 'name' => 'od.name'))
            ->from(array('o' => 'option'))
            ->innerJoin(array('od' => 'option_description'), 'o.option_id = od.option_id')
            ->where('od.language_id = ?', (int)$this->config->get('config_language_id'))
            ->order(array('o.sort_order'));
        
        $res = $this->db->query($sql);
        
        $options = array();
        if ($res->num_rows) {
            foreach ($res->rows as $row) {
                $options[$row['id']] = array(
                    'name' => $row['name']
                );
            }
        }
        
        return $options;
    }
    
    public function getAttributes()
    {
        $sql = new SqlStatement();
        $sql->select(array('id' => 'a.attribute_id', 'name' => 'ad.name', 'grp' => 'agd.name'))
            ->from(array('a' => 'attribute'))
            ->innerJoin(array('ad' => 'attribute_description'), 'a.attribute_id = ad.attribute_id')
            ->innerJoin(array('ag' => 'attribute_group'), 'ag.attribute_group_id = a.attribute_group_id')
            ->innerJoin(array('agd' => 'attribute_group_description'), 'agd.attribute_group_id = a.attribute_group_id')
            ->where('agd.language_id = ?', (int)$this->config->get('config_language_id'))
            ->where('ad.language_id = ?', (int)$this->config->get('config_language_id'))
            ->order(array('ag.sort_order', 'a.sort_order'));
        
        $res = $this->db->query($sql);
		
        $attrGroups = array();
        if ($res->num_rows) {
            foreach ($res->rows as $row) {
                $attrGroups[$row['id']] = array(
                    'group' => $row['grp'],
                    'name' => $row['name']
                );
            }
        }
        
        return $attrGroups;
    }
    
    public function getCategories()
    {
        $this->load->model('catalog/category');
        $res = $this->model_catalog_category->getCategories(array('start' => 0, 'limit' => 10000));
        
        if (!count($res)) {
            return array();
        }
        
        $categories = array();
        foreach ($res as $cat) {
            $categories[$cat['category_id']] = $cat;
        }
        
        return $categories;
    }
    
    public function getAttributeValues($attrId)
    {
        $sql = new SqlStatement();
        $sql->select()
            ->from(self::TBL_ATTR_VAL)
            ->where('attribute_id = ?', $attrId)
            ->order(array('sort_order', 'value'));
        
        $res = $this->db->query($sql);
        $values = array();
        if ($res->num_rows) {
            foreach ($res->rows as $row) {
                $lang = $row['language_id'];
                if (!isset($values[$lang])) {
                    $values[$lang] = array();
                }
                unset($row['language_id']);
                $values[$lang][] = $row;
            }
        }
        return $values;
    }
    
    public function changeAttrValuesSortOrder($data) 
    {
        if (is_array($data) && count($data)) {
            foreach ($data as $id => $sort) {
                $sql = ' UPDATE ' . DB_PREFIX . self::TBL_ATTR_VAL 
                     . ' SET `sort_order` = "' . $this->db->escape($sort) . '"'
                     . ' WHERE attribute_value_id = "' . $this->db->escape($id) . '"';
                $this->db->query($sql);
            }
        }
    }
    
    public function getDefaultLayout()
    {
        $sql = new SqlStatement();
        $sql->select(array('layout_id'))
            ->from('layout_route')
            ->where('route = ?', 'extension/module/brainyfilter/filter')
            ->where('store_id = 0');
        
        $res = $this->db->query($sql);
        
        if ($res->num_rows) {
            return $res->row['layout_id'];
        } else {
            return null;
        }
    }
    
    public function addDefaultLayout()
    {
        $defaultLayout = $this->getDefaultLayout();
        if ($defaultLayout) {
            $this->removeDefaultLayout();
        }
        
        $data = array(
            'name' => 'Brainy Filter Layout',
            'layout_route' => array (
                array(
                    'route' => 'extension/module/brainyfilter/filter',
                    'store_id' => '0',
                )
            )
        );
        
        $this->load->model('design/layout');
        $this->model_design_layout->addLayout($data);
    }
    
    public function removeDefaultLayout()
    {
        $defaultLayout = $this->getDefaultLayout();
        if ($defaultLayout) {
            $this->load->model('design/layout');
            $this->model_design_layout->deleteLayout($defaultLayout);
        }
    }
    
    public function detectCategoryLayouts()
    {
        $sql = new SqlStatement();
        $sql->select(array('layout_id'))
            ->from('layout_route')
            ->where('route = "product/category"');
        
        $res = $this->db->query($sql);
        $output = array();
        if ($res->num_rows) {
            foreach ($res->rows as $row) {
                $output[] = $row['layout_id'];
            }
        }
        return $output;
    }
    
    public function editLayoutModule($layoutsData)
    {
        $sql = new SqlStatement();
        $sql->delete()->from('layout_module')->where('code = ?', $layoutsData['code']);
        $this->db->query($sql);
        $sql->clean()->insertInto('layout_module', array($layoutsData));
        $this->db->query($sql);
    }
    
    public function deleteLayoutModule($moduleId) {
        $sql = new SqlStatement();
        $sql->delete()->from('layout_module')->where('code = ?', 'brainyfilter.' . $moduleId);
        $this->db->query($sql);
    }
    
    public function addCustomIndexes()
    {
        $this->db->query('ALTER TABLE ' . DB_PREFIX . 'product_option_value ADD INDEX  bf_product_option_value (  product_id ,  option_value_id )');
    }
    
    public function removeCustomIndexes()
    {
        $res = $this->db->query('SHOW INDEX FROM ' . DB_PREFIX . 'product_option_value WHERE KEY_NAME = "bf_product_option_value"');
        if ($res->num_rows) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'product_option_value DROP INDEX bf_product_option_value');
        }
    }
    
    public function getEnabledLayoutModules()
    {
        $this->load->model('extension/module');
        $sql = new SqlStatement();
        $sql->select(array('code'))->from(array('layout_module'))->where('code LIKE "brainyfilter.%"');
        $res = $this->db->query($sql);
        $output = array();
        if ($res->num_rows) {
            foreach($res->rows as $row) {
                $moduleId = (int)substr($row['code'], 13);
                $output[] = (int)$moduleId;
            }
        }
        return $output;
    }
    
    public function enableMod($enable = true)
    {
        $this->load->model('extension/modification');
        $mod = $this->model_extension_modification->getModificationByCode('brainyfilter');
        if ($mod) {
            if ((bool)$mod['status'] !== $enable) {
                if ($enable) {
                    $this->model_extension_modification->enableModification($mod['modification_id']);
                } else {
                    $this->model_extension_modification->disableModification($mod['modification_id']);
                }
            }
            return true;
        }
        return false;
    }
    
    public function updateMod($data)
    {
        $this->load->model('extension/modification');
        $mod = $this->model_extension_modification->getModificationByCode('brainyfilter');
        if ($mod) {
            $set = array();
            if (isset($data['status'])) {
                $set[] = "status = '" . (int)$data['status'] . "'";
            }
            if (isset($data['xml'])) {
                $set[] = "xml = '" . $this->db->escape($data['xml']) . "'";
            }
            if (count($set)) {
                $this->db->query("UPDATE " . DB_PREFIX . "modification SET " 
                        . implode(',', $set) 
                        . " WHERE modification_id = '" . (int)$mod['modification_id'] . "'");
                
            }
            return true;
        }
        return false;
    }
}
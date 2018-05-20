<?php
require_once DIR_SYSTEM . 'library/sqllib.php';

final class ModelExtensionModuleBrainyFilter extends Model 
{
    
    const TBL_FILTER           = 'bf_filter';
    const TBL_PRODUCT_ATTR_VAL = 'bf_product_attribute_value';
    const TBL_ATTR_VAL         = 'bf_attribute_value';
    const TBL_TAX_RATE         = 'bf_tax_rate';
    const TBL_TMP_PRODUCT      = 'bf_tmp_product';
    
    /**
     * Catalog top-category ID
     * @var int
     */
    private $topCategory = null;
    
    /**
     * Catalog sub-category ID
     * @var int 
     */
    private $subCategory = null;
    
    private $specialsOnly = false;

    /**
     * Search query by product names
     * @var string
     */
    private $searchNameString = '';
    
    /**
     * Search query by product tags
     * @var string
     */
    private $searchTagString = '';
    
    /**
     * Search query by product descriptions
     * @var string
     */
    private $searchDescriptionString = '';
    
    /**
     * List of all selected filters with the following items:
     * <ul>
     * <li><b>filters</b> - Array</li>
     * <li><b>price</b> - stdClass with properties <i>min</i> and <i>max</i></li>
     * <li><b>search</b> - search string</li>
     * <li><b>manufacturer</b> - Manufacturer ID. The parameter will trigger filtering of all attributes by manufacturer id.
     * It is set in case manufacturer_id GET parameter exsists</li>
     * </ul>
     * @see ModelModuleBrainyFilter::getConditions() - getter
     * @property price
     * @property filters
     * @property search
     * @var stdClass 
     */
    private $conditions  = null;
    
    /**
     * Aggregated array of selected attributes, filters, options, manufacturers, stock statuses and ratings.
     * <pre>
     * array(
     *      [group ID, e.g. a157, o3, s7] => array([values])
     * )
     * </pre>
     * @var array
     */
    private $aggregate   = array();
    
    /**
     * Customer group ID
     * @var int
     */
    private $customerGroupId = null;
    
    /**
     * Product limit per page 
     * @var int 
     */
    public $productsLimit = 20;
    
    /**
     * Product list offset
     * @var int 
     */
    public $productsStart = 0;

    /**
     * Product list sort order
     * @var array
     */
    public $sortBy = null;
    
    public $order = 'ASC';
    
    /**
     * Cache data which designed to prevent execution of similar sql queries in
     * case of multiple modules per page
     * @var array
     */
    private static $_cache = array( 'sliders' => array() );
    
    /**
     * Whether to hide out of stock products or not. In case the property is set to TRUE,
     * the filter will take into account stock status per each product option
     * @var boolean
     */
    private static $HIDE_OUT_OF_STOCK = false;
    
    private static $TMP_TBL_EXISTS = false;
    
    private static $SKIP_TMP_TABLE = false;
    
    /**
     * Filter binary masks
     * <pre>
     * array(
     *    filter group id => array(
     *        2^n,
     *        sume of all the selected filters excluding the current
     *    )
     * )
     * </pre>
     * @var array
     */
    private $filterMasks = array();
    
    private $_currency;
    
    /**
	 * Constructor
	 *
	 * @param array $registry 
	 */
    public function __construct($registry) {
        parent::__construct($registry);
        SqlStatement::$DB_PREFIX = DB_PREFIX;
        SqlStatement::$DB_CONNECTOR = $this->db;
        $this->_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        
        $bfSettings = $this->config->get('brainyfilter_layout_basic');
        self::$HIDE_OUT_OF_STOCK = (bool)$bfSettings['global']['hide_out_of_stock'];
        
        $this->conditions = new stdClass();
        $this->conditions->filters = array();
        $this->conditions->price = null;
        $this->conditions->manufacturer = null;
        $this->conditions->search = '';
		
        // fill out the conditions property
        $this->_parseBFilterParam();
        
        
        if (isset($this->request->get['manufacturer_id']) && !empty($this->request->get['manufacturer_id'])) {
            $this->conditions->manufacturer = (int) $this->request->get['manufacturer_id'];
            $this->conditions->filters['m0'][0] = (int) $this->request->get['manufacturer_id'];
            $this->aggregate['m0'] = array();
            $this->aggregate['m0'][0] = (int) $this->request->get['manufacturer_id'];
        }

        self::$SKIP_TMP_TABLE = (bool)$bfSettings['global']['postponed_count'] 
                && !$this->registry->get('bf_force_tmp_table_creation')
                && empty($this->conditions->filters);
        
        if (count($this->aggregate)) {
            foreach ($this->aggregate as $group => $values) {
                if (empty($values)) {
                    unset($this->aggregate[$group]);
                }
            }
        }
        
        $this->_calcFilterMasks();
        
        $this->customerGroupId = ($this->customer->isLogged()) 
                ? $this->customer->getGroupId()
                : $this->config->get('config_customer_group_id');
        
    }
    
    /**
     * Calculate filter binary masks.
     * @see $filterMasks
     */
    private function _calcFilterMasks()
    {
        $i = 0;
        $masks = array();
        foreach ($this->aggregate as $group => $values) {
            $masks[$group] = pow(2, $i);
            $i ++;
        }
        
        foreach ($masks as $group => $mask) {
            $exclude = $masks;
            unset($exclude[$group]);
            $this->filterMasks[$group] = array(
                $mask,
                array_sum($exclude)
            );
        }
    }
    
    /**
     * Parse BrainyFilter Param
	 * <br />
     * The method explodes bfilter GET parameter to the list of selected filters
     * and fills out $this->conditions and $this->aggregate properties
     * 
     * @return void
     */
    private function _parseBFilterParam()
    {
        if (!isset($this->request->get['bfilter'])) {
            
            return;
        }
		$bfilter = $this->request->get['bfilter'];

		$params = explode(';', $bfilter);
        
        foreach ($params as $param) {
            if (!empty($param)) {
                $p = explode(':', $param);
                $pName  = $p[0];
                $pValue = $p[1];
                if ($pName === 'price') 
                {
                    $p = explode('-', $pValue);
                    $price = new stdClass();
                    $price->min = null;
                    $price->max = null;
                    $price->inputMin = null;
                    $price->inputMax = null;
                    if ((int)$p[0] > 0) {
                        $price->min = $this->currency->convert($p[0], $this->_currency, $this->config->get('config_currency'));
                        $price->inputMin = $p[0];
                    }
                    if ((int)$p[1] > 0) {
                        $price->max = $this->currency->convert($p[1], $this->_currency, $this->config->get('config_currency'));
                        $price->inputMax = $p[1];
                    }
                    $this->conditions->price = $price;
                } 
                elseif ($pName === 'search') 
                {
                    $this->conditions->search = $pValue;
                    $this->searchNameString = $pValue;
                    $this->searchTagString = $pValue;
                    $this->searchDescriptionString = $pValue;
                } 
                else 
                {
                    if (strpos($pValue, '-') !== false) 
                    {
                        $p = explode('-', $pValue);
                        $range = $this->_getSliderIntermediateValues($pName, $p[0], $p[1]);
                        if (!empty($range)) {
                            $this->conditions->filters[$pName] = array('min' => $p[0], 'max' => $p[1]);
                            $this->aggregate[$pName] = $range;
                        }
                    } 
                    else 
                    {
                        $this->conditions->filters[$pName] = explode(',', $pValue);
                        $this->aggregate[$pName] = explode(',', $pValue);
                    }
                }
            }
            
        }
    }
    
    /**
     * Get Slider Intermediate Values<br>
     * The method converts minimum and maximum limits into
     * range of filter IDs
     * @param type $id Filter group ID (first letter of type + integer ID, e.g. "a156")
     * @param type $min minimum sort order number
     * @param type $max maximum sort order number
     * @return array Array of filter value IDs
     */
    private function _getSliderIntermediateValues($id, $min, $max)
    {
        if (isset(self::$_cache['sliders']["$id-$min-$max"])) {
            return self::$_cache['sliders']["$id-$min-$max"];
        }
        $type = substr($id, 0, 1);
        $numb = (int)substr($id, 1);
        $sql = new SqlStatement();
        
        if ($type === 'a') {
            $sql->select(array('id' => 'attribute_value_id'))
                ->from(self::TBL_ATTR_VAL)
                ->where('attribute_id = ?', array($numb))
                ->where('language_id = ?', (int)$this->config->get('config_language_id'));
        } elseif ($type === 'o') {
            $sql->select(array('id' => 'option_value_id'))
                ->from('option_value')
                ->where('option_id = ?', array($numb));
        } elseif ($type === 'f') {
            $sql->select(array('id' => 'filter_id'))
                ->from('filter')
                ->where('filter_group_id = ?', array($numb));
        } else {
            return;
        }
        
        $sql->order(array('sort_order'));
        
        if (!empty($min) && $min !== 'na') {
            $sql->where('sort_order >= ?', (int)$min);
        }
        if (!empty($max) && $max !== 'na') {
            $sql->where('sort_order <= ?', (int)$max);
        }
        
        $res = $this->db->query($sql);
        
        $output = array();
        if ($res->num_rows) {
            foreach ($res->rows as $row) {
                $output[] = $row['id'];
            }
        }
        self::$_cache['sliders']["$id-$min-$max"] = $output;
        
        return $output;
    }
    
    /**
     * Prepare Search Conditions<br>
     * The method assembles WHERE conditions for filtering by search query string
     * 
     * @return array
     */
    private function _prepareSearchConditions()
    {
        $search = array();
        
        if (!empty($this->searchNameString)) {
            $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $this->searchNameString)));
            $nameCond = array();
            foreach ($words as $word) {
                $nameCond[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
            }
            $search = array('(' . implode(' AND ', $nameCond) . ')');
            
            $search[] = array('LCASE(p.model) = ?', $this->searchNameString);
            $search[] = array('LCASE(p.sku) = ?', $this->searchNameString);
            $search[] = array('LCASE(p.upc) = ?', $this->searchNameString);
            $search[] = array('LCASE(p.ean) = ?', $this->searchNameString);
            $search[] = array('LCASE(p.jan) = ?', $this->searchNameString);
            $search[] = array('LCASE(p.isbn) = ?', $this->searchNameString);
            $search[] = array('LCASE(p.mpn) = ?', $this->searchNameString);
            
        }
        if (!empty($this->searchTagString)) {
            $search[] = array('pd.tag LIKE "%' . $this->db->escape($this->searchTagString) . '%"');
        }
        if (!empty($this->searchDescriptionString)) {
            $search[] = array('pd.description LIKE "%' . $this->db->escape($this->searchDescriptionString) . '%"');
        }
        
        return $search;
    }
    
    /**
     * Set Data
     * 
     * @param array $data initial data for the model
     * @return void 
     */
    public function setData($data = array())
    {
        if (isset($data['filter_category_id'])) {
            if (isset($data['filter_sub_category']) && $data['filter_sub_category']) { 
                $this->subCategory = (int)$data['filter_category_id'];
            } else {
                $this->topCategory = (int)$data['filter_category_id'];
            }
        }
        if (isset($data['filter_name']) && empty($this->searchNameString)) {
            $this->searchNameString = utf8_strtolower($data['filter_name']);
        }
        if (isset($data['filter_tag']) && empty($this->searchTagString)) {
            $this->searchTagString = utf8_strtolower($data['filter_tag']);
        }
        if (isset($data['filter_description']) && empty($this->searchDescriptionString)) {
            $this->searchDescriptionString = utf8_strtolower($data['filter_name']);
        }
        if (empty($this->conditions->filters['m0'])) {
            if (isset($data['filter_manufacturer_id'])
                    && !empty($data['filter_manufacturer_id'])) {
                $this->conditions->filters['m0'][0] = (int) $data['filter_manufacturer_id'];
                $this->aggregate['m0'] = array();
                $this->aggregate['m0'][0] = (int) $data['filter_manufacturer_id'];
            // hack - in order to pass the parameter to our model the global GET variable 
            // is created, since there is no more convenient way to do this thougth
            // product/category controller and product model
            } elseif (isset($this->request->get['manufacturer_id']) 
                    && !empty($this->request->get['manufacturer_id'])) {
                $this->conditions->filters['m0'][0] = (int) $this->request->get['manufacturer_id'];
                $this->aggregate['m0'] = array();
                $this->aggregate['m0'][0] = (int) $this->request->get['manufacturer_id'];
            }
        }
        
        if (isset($data['filter_specials_only'])) {
            $this->specialsOnly = (bool)$data['filter_specials_only'];
        }
        
        if (isset($data['limit'])) {
            $this->productsLimit = $data['limit'];
        }
        if (isset($data['start'])) {
            $this->productsStart = $data['start'];
        }
		
        $this->order  = (isset($data['order']) && strtoupper($data['order']) == 'DESC') ? 'DESC' : 'ASC';
        $this->sortBy = (isset($data['sort'])) ? $data['sort'] : null;
    }
    
    /**
     * Get Conditions
     * <br>
     * Getter for the private property $conditions
     * @return stdClass
     */
    public function getConditions()
    {
        return $this->conditions;
    }
    
    /**
     * Returns current shipment country ID and zone ID.
     * Shipment location can affect tax amount and, as a knok-on effect, product price.
     * 
     * @property countryId
     * @property zoneId
     * @return \stdClass 
     */
    private function _getShipmentLocation()
    {
        $location = new stdClass();
        $location->countryId = null;
        $location->zoneId    = null;
        
        if (isset($this->session->data['shipping_address']['country_id'])) 
        {
            $location->countryId = (int)$this->session->data['shipping_address']['country_id'];
        } 
        elseif ($this->config->get('config_tax_default') == 'shipping') 
        {
            $location->countryId = (int)$this->config->get('config_country_id');
        }
        
        if (isset($this->session->data['shipping_address']['zone_id'])) 
        {
            $location->zoneId = (int)$this->session->data['shipping_address']['zone_id'];
        } 
        elseif ($this->config->get('config_tax_default') == 'shipping') 
        {
            $location->zoneId = (int)$this->config->get('config_zone_id');
        }
        
        return $location;
    }
    
    /**
     * Returns current payment country ID and zone ID.
     * Payment location can affect tax amount and, as a knok-on effect, product price.
     * 
     * @property countryId
     * @property zoneId
     * @return \stdClass 
     */
    private function _getPaymentLocation()
    {
        $location = new stdClass();
        $location->countryId = null;
        $location->zoneId    = null;
        
        if (isset($this->session->data['payment_address']['country_id'])) 
        {
            $location->countryId = (int)$this->session->data['payment_address']['country_id'];
        } 
        elseif ($this->config->get('config_tax_default') == 'payment') 
        {
            $location->countryId = (int)$this->config->get('config_country_id');
        }
        
        if (isset($this->session->data['payment_address']['zone_id'])) 
        {
            $location->zoneId = (int)$this->session->data['payment_address']['zone_id'];
        } 
        elseif ($this->config->get('config_tax_default') == 'payment') 
        {
            $location->zoneId = (int)$this->config->get('config_zone_id');
        }
        
        return $location;
    }
    
    /**
     * Apply Price Filter Condition<br>
     * The method joins all the tables, which are necessary for price calculation.
     * 
     * @param SqlStatement $sql
     * @return void
     */
    private function _applyPriceFilterCondition($sql)
    {
        if (!$this->specialsOnly) {
            $sql->select()
                ->leftJoin(array('pd2' => 'product_discount'), "pd2.product_id = p.product_id 
                        AND pd2.quantity = '1'
                        AND (pd2.date_start = '0000-00-00' OR pd2.date_start < NOW())
                        AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())
                        AND pd2.customer_group_id = '{$this->customerGroupId}'")
                ->leftJoin(array('ps' => 'product_special'), "ps.product_id = p.product_id 
                        AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
                        AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                        AND ps.customer_group_id = '{$this->customerGroupId}'");

            $minPrice = 'IF(MIN(ps.price) IS NOT NULL, MIN(ps.price), IF(MIN(pd2.price) IS NOT NULL, MIN(pd2.price), p.price))';
        } else {
            $minPrice = 'ps.price';
        }
        if ($this->config->get('config_tax')) {
            $shipment = $this->_getShipmentLocation();
            $payment  = $this->_getPaymentLocation();
            $taxConditions = array();
            $defCountryId  = (int)$this->config->get('config_country_id');
            $defZoneId     = (int)$this->config->get('config_zone_id');
            $taxConditions[] = "(based = 'store' AND country_id = '" . $defCountryId . "' AND zone_id IN ('0', '" . $defZoneId . "'))";

            if ($shipment->countryId || $shipment->zoneId) {
                $taxConditions[] = "(based = 'shipping' AND country_id = '{$shipment->countryId}' AND zone_id IN ('0', '{$shipment->zoneId}'))";
            }

            if ($payment->countryId || $payment->zoneId) {
                $taxConditions[] = "(based = 'payment' AND country_id = '{$payment->countryId}' AND zone_id IN ('0', '{$payment->zoneId}'))";
            }

            $taxSql = new SqlStatement();
            
            $taxSql->select(array('fixed_tax' => 'SUM(fixed_rate)', 'percent_tax' => 'SUM(percent_rate)', 'tax_class_id'))
                ->from(array(self::TBL_TAX_RATE))
                ->where('customer_group_id = ?', $this->customerGroupId)
                ->multipleWhere($taxConditions)
                ->group(array('tax_class_id'));

            $sql->leftJoin(array('tx' => $taxSql), 'p.tax_class_id = tx.tax_class_id');
            $sql->select(array('actual_price' => '(' . $minPrice . ' * (1 + IFNULL(percent_tax, 0)/100) + IFNULL(fixed_tax, 0))'));
        } else {
            $sql->select(array('actual_price' => '(' . $minPrice . ')' ));
        }
    }
    
    /**
     * 
     * @return \SqlStatement
     */
    private function _prepareProductQuery()
    {
        $sql = new SqlStatement();

        $sql->select(array('p.product_id', 'p.sort_order'))
            ->from(array('p' => 'product'))
            ->innerJoin(array('p2s' => 'product_to_store'), 'p2s.product_id = p.product_id')
            ->innerJoin(array('f' => self::TBL_FILTER), 'p.product_id = f.product_id')
            ->where('p2s.store_id = ?', (int)$this->config->get('config_store_id'))
            ->group(array('p.product_id'));

        if ($this->subCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->innerJoin(array('cp' => 'category_path'), 'cp.category_id = p2c.category_id')
                ->where('cp.path_id = ?', array($this->subCategory));
        }
        elseif ($this->topCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->where('p2c.category_id = ?', array($this->topCategory));
        }
        
        if ($this->specialsOnly)
        {
            $sql->innerJoin(array('ps' => 'product_special'), "ps.product_id = p.product_id 
                    AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
                    AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                    AND ps.customer_group_id = '{$this->customerGroupId}'");
        }

        if ($this->conditions->manufacturer) {
            $sql->where('p.manufacturer_id = ?', array($this->conditions->manufacturer));
        }

        $searchConditions = $this->_prepareSearchConditions();
        if (count($searchConditions)) {
            $sql->innerJoin(array('pd' => 'product_description'), 'pd.product_id = p.product_id')
                ->multipleWhere($searchConditions, 'OR')
                ->where('pd.language_id = ?', (int)$this->config->get('config_language_id'));
        }

        if ( self::$HIDE_OUT_OF_STOCK ) {
            $sql->where('f.out_of_stock = 0');
        }
        
        return $sql;
    }
    
    /**
     * Fill temporary DB table with the results of soft filtering.
     * By soft filtering is meant that products, which don't match one of the
     * selected filter, are also included in the table.
     */
    private function _fillTmpTable($sql) 
    {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . self::TBL_TMP_PRODUCT);
        $this->db->query('CREATE TEMPORARY TABLE ' . DB_PREFIX . self::TBL_TMP_PRODUCT . ' (PRIMARY KEY (`product_id`)) ' . $sql);
        self::$TMP_TBL_EXISTS = true;
    }
    
    /**
     * Prepare Filter Query<br>
     * It is a core method for assembling the filtering SQL query.
     * 
     * @return \SqlStatement
     */
    private function _prepareFilterQuery($filterByPrice = true)
    {
        $sql = $this->_prepareProductQuery();
        
        $sql->select(array('match_filters' => '"1"'));
        
        if (!empty($this->aggregate)) {
            $where = array();
            $cases = array();
            foreach ($this->aggregate as $group => $vals) {
                $where[] = '(f.filter_group = "' . $this->db->escape($group) . '" AND filter_id IN (' . implode(',', $vals) . '))';
                $cases[] = " WHEN '" . $this->db->escape($group) . "' THEN {$this->filterMasks[$group][0]} ";
            }
            if (count($this->aggregate) > 1) 
            {
                $cnt = count($this->aggregate);
                $sql->select(array(
                            'c' => 'COUNT(DISTINCT filter_group)',
                            'match_filters' => 'IF( COUNT(DISTINCT filter_group) = ' . $cnt . ', 1, 0 )',
                            'match_filters_mask' => 'SUM( DISTINCT CASE filter_group ' . implode("\n", $cases) . ' END )'
                        ))
                        ->multipleWhere($where)
                        ->having('c >= ?', $cnt - 1);
            }
            else
            {
                $sql->select(array(
                    'match_filters' => 'if (SUM(if(' . $where[0] . ', 1, 0)) > 0, 1, 0)'
                ));
            }
        }

        if (!self::$SKIP_TMP_TABLE || $this->conditions->price) {
            $this->_applyPriceFilterCondition($sql);
        }
        
        if (!self::$SKIP_TMP_TABLE) 
        {
            if (!self::$TMP_TBL_EXISTS) {
                $this->_fillTmpTable($sql);
            }
            
            $sql = new SqlStatement();
            $sql->select(array('p.*'))
                ->from(array('p' => self::TBL_TMP_PRODUCT))
                ->where('match_filters = 1');
        } else {
            $sql->having('match_filters = 1');
        }
        
        if ($filterByPrice) 
        {
            if (self::$SKIP_TMP_TABLE) {
                if ($this->conditions->price && $this->conditions->price->min) {
                    $sql->having("actual_price >= ?", $this->conditions->price->min);
                }
                if ($this->conditions->price && $this->conditions->price->max) {
                    $sql->having("actual_price <= ?", $this->conditions->price->max);
                }
            } else {
                if ($this->conditions->price && $this->conditions->price->min) {
                    $sql->where("actual_price >= ?", $this->conditions->price->min);
                }
                if ($this->conditions->price && $this->conditions->price->max) {
                    $sql->where("actual_price <= ?", $this->conditions->price->max);
                }
            }
        }
        
        return $sql;
    }


    /**
     * Prepare Query String For Category
	 * <br />
     * The method applies BrainyFilter conditions to the query for products.
     * It is injected to the ModelCatalogProduct::getProducts() via vQmod/ OCmod
     * 
     * @return string SQL query string
     */
    public function prepareQueryForCategory()
    {
        $sql = $this->_prepareFilterQuery()->limit($this->productsLimit, $this->productsStart);
        
        $sql->innerJoin(array('pd' => 'product_description'), 'pd.product_id = p.product_id')
            ->where('pd.language_id = ?', (int)$this->config->get('config_language_id'));
        
        switch ($this->sortBy) 
        {
            case 'pd.name' : {
                $sql->order(array("LCASE(pd.name) {$this->order}"));
                break;
            }
            case 'p.model' : {
                $sql->select(array('pp.model'))->order(array("LCASE(pp.model) {$this->order}"))
                    ->innerJoin(array('pp' => 'product'), 'pp.product_id = p.product_id');
                break;
            }
            case 'p.quantity' : {
                $sql->select(array('pp.quantity'))->order(array("pp.quantity {$this->order}", ))
                    ->innerJoin(array('pp' => 'product'), 'pp.product_id = p.product_id');
                break;
            }
            case 'p.price' : {
                if (self::$SKIP_TMP_TABLE) {
                    $this->_applyPriceFilterCondition($sql);
                }
                $sql->order(array("actual_price {$this->order}"));
                break;
            }
            case 'rating' : {
                $sql->leftJoin(array('f' => self::TBL_FILTER), 'f.product_id = p.product_id')
                    ->where('f.filter_group = "r0"')
                    ->order(array("f.filter_id {$this->order}"));
                break;
            }
            case 'p.sort_order' : {
                $sql->select(array('pp.sort_order'))->order(array("pp.sort_order {$this->order}"))
                    ->innerJoin(array('pp' => 'product'), 'pp.product_id = p.product_id');
                break;
            }
            case 'p.date_added' : {
                $sql->select(array('pp.date_added'))->order(array("pp.date_added {$this->order}"))
                    ->innerJoin(array('pp' => 'product'), 'pp.product_id = p.product_id');
                break;
            }
        }
        
        if ($this->sortBy !== 'pd.name') {
            $sql->order(array("pd.name {$this->order}"));
        } 
        
        return (string)$sql;
    }
    
	/**
     * Prepare Query For Total
	 * <br />
     * Generates query string for calculation of total amount of found products
     * @return string SQL query string
     */
    public function prepareQueryForTotal()
    {
        $sub = $this->_prepareFilterQuery();
        $sql = new SqlStatement();
        $sql->select(array('total' => 'COUNT(*)'))->from(array('t' => $sub));

        return (string)$sql;
    }
    
    /**
     * Calculates amount of products per each filter
     * <br>
     * Returns Array with the following structure
     * <pre>
     * array(
     *      array( [first letter of type + group ID] => array( [value] => array( [products count] ) ),
     *      ....
     * )
     * </pre>
     * @return array 
     */
    public function calculateTotals()
    {
        $sql = clone $this->_prepareFilterQuery();
        $sql->clean('select')
            ->select(array('val' => 'COUNT(*)', 'filter_group', 'filter_id'))
            ->innerJoin(array('f' => self::TBL_FILTER), 'p.product_id = f.product_id')
            ->where('f.language_id = ?', (int)$this->config->get('config_language_id'))
            ->in('f.filter_group', array_keys($this->aggregate), true)
            ->group(array('filter_group', 'filter_id'));
        
        if ( self::$HIDE_OUT_OF_STOCK ) {
            $sql->where('f.out_of_stock = 0');
        }
            
        $res = $this->db->query($sql);
        $totalsIn = $res->rows;
        $totalsOut = array();
        
        if (count($this->aggregate)) 
        {
            $sql->clean()
                ->select(array('val' => 'COUNT(*)', 'filter_group', 'filter_id'))
                ->from(array('tp' => self::TBL_TMP_PRODUCT))
                ->innerJoin(array('f' => self::TBL_FILTER), 'f.product_id = tp.product_id')
                ->where('f.language_id = ?', (int)$this->config->get('config_language_id'))
                ->where('tp.match_filters < 1')
                ->group(array('filter_group', 'filter_id'));
            
            if ($this->conditions->price && $this->conditions->price->min) {
                $sql->where("actual_price >= ?", $this->conditions->price->min);
            }
            if ($this->conditions->price && $this->conditions->price->max) {
                $sql->where("actual_price <= ?", $this->conditions->price->max);
            }
            if ( self::$HIDE_OUT_OF_STOCK ) {
                $sql->where('f.out_of_stock = 0');
            }
            
            if (count($this->aggregate) > 1) {
                $where = array();
                foreach ($this->aggregate as $group => $vals) {
                    $where[] = "(tp.match_filters_mask = {$this->filterMasks[$group][1]} AND f.filter_group = '" . $this->db->escape($group) . "')";
                }
                $sql->multipleWhere($where);
            } else {
                $sql->in('f.filter_group', array_keys($this->aggregate));
            }

            $res = $this->db->query($sql);
            $totalsOut = $res->rows;
        }
        
        $total = array_merge($totalsIn, $totalsOut);
        $output = array();
        
        if (count($total)) {
            foreach ($total as $row) {
                if (!isset($output[$row['filter_group']])) {
                    $output[$row['filter_group']] = array();
                }
                $output[$row['filter_group']][$row['filter_id']] = $row['val'];
            }
        }

        return $output;
    }
    
    /**
     * Get MIN/MAX category price
	 * <br />
     * The method calculates min/max price taking into account special offers, 
     * discounts and taxes
     * 
     * @return array Associative array with min and max fields
     */
    public function getMinMaxCategoryPrice()
    {
        if (isset(self::$_cache['minmaxprice'])) {
            return self::$_cache['minmaxprice'];
        }

        $sql = $this->_prepareFilterQuery(false);
        
        $sql->clean('select')->clean('group')
            ->select(array(
                'min' => "MIN(actual_price)",
                'max' => "MAX(actual_price)",
            ));
        
        
        $res = $this->db->query($sql);
        
        self::$_cache['minmaxprice'] = $res->row;
        
        return $res->row;
    }

    /**
     * Get Attributes
     * 
     * @return array Returns array of existed attributes in the given category 
     * and all their values
     */
    public function getAttributes()
    {
        if (isset(self::$_cache['attributes'])) {
            return self::$_cache['attributes'];
        }
        $prodSql = new SqlStatement();
        $prodSql->select()
                ->from(array('p' => 'product'), array('p.*'));
        if ($this->subCategory) 
        {
            $prodSql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->innerJoin(array('cp' => 'category_path'), 'cp.category_id = p2c.category_id')
                ->where('cp.path_id = ?', array($this->subCategory));
        }
        elseif ($this->topCategory) 
        {
            $prodSql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->where('p2c.category_id = ?', array($this->topCategory));
        }
        if ($this->conditions->manufacturer)
        {
            $prodSql->where('p.manufacturer_id = ?', array($this->conditions->manufacturer));
        }
            
        $sql = new SqlStatement();
        $sql->select(array(
                'group_id'   => 'a.attribute_group_id', 
                'attr_id'    => 'av.attribute_id', 
                'val_id'     => 'av.attribute_value_id', 
                'group_name' => 'agd.name',
                'attr_name'  => 'ad.name',
                'val_sort'   => 'av.sort_order',
                'av.value',
            ))
            ->from(array('af' => self::TBL_PRODUCT_ATTR_VAL))
            ->innerJoin(array('p' => $prodSql), 'af.product_id = p.product_id')
            ->innerJoin(array('av' => self::TBL_ATTR_VAL), 'af.attribute_value_id = av.attribute_value_id')
            ->innerJoin(array('a' => 'attribute'), 'a.attribute_id = av.attribute_id')
            ->innerJoin(array('ad' => 'attribute_description'), 'ad.attribute_id = a.attribute_id')
            ->innerJoin(array('ag' => 'attribute_group'), 'ag.attribute_group_id = a.attribute_group_id')
            ->innerJoin(array('agd' => 'attribute_group_description'), 'agd.attribute_group_id = a.attribute_group_id')
            ->innerJoin(array('ps' => 'product_to_store'), 'p.product_id = ps.product_id')
            ->where('agd.language_id = ?', (int)$this->config->get('config_language_id'))
            ->where('ad.language_id = ?', (int)$this->config->get('config_language_id'))
            ->where('av.language_id = ?', (int)$this->config->get('config_language_id'))
            ->where('ps.store_id = ?', (int)$this->config->get('config_store_id'))
            ->where('p.status = 1')
            ->group(array('av.attribute_value_id'))
            ->order(array('ag.sort_order', 'ag.attribute_group_id', 'a.sort_order', 'ad.name', 'av.sort_order', 'av.value'));
        
        $res = $this->db->query($sql);
        
        $output = array();
        
        if (count($res->rows)) {
            foreach ($res->rows as $row) {
                    $r = array(
                        'name' => $row['value'],
                        'id' => $row['val_id'],
                        'sort' => $row['val_sort'],
                    );

                    if (!isset($output[$row['attr_id']])) {
                        $output[$row['attr_id']] = array(
                            'name' => $row['attr_name'],
                            'group_id' => $row['group_id'],
                            'group' => $row['group_name'],
                            'values' => array()
                        );
                    }
                    $output[$row['attr_id']]['values'][] = $r;
            }
        }
        self::$_cache['attributes'] = $output;

        return $output;
    }
    
    /**
     * Get Manufacturers
	 * <br />
     * Retrieves a list of manufacturers for the given category ID
     * 
     * @param array $data Input parameters
     * @return mixed Array of manufacturers for the given category ID if found. 
     * Otherwise returns FALSE
     */
	public function getManufacturers()
	{
        if (isset(self::$_cache['manufacturers'])) {
            return self::$_cache['manufacturers'];
        }
        $sql = new SqlStatement();
        $sql->select(array('id' => 'm.manufacturer_id', 'm.name'))
            ->distinct()
            ->from(array('m' => 'manufacturer'))
            ->innerJoin(array('p' => 'product'), 'm.manufacturer_id = p.manufacturer_id')
            ->innerJoin(array('m2s' => 'manufacturer_to_store'), 'm.manufacturer_id = m2s.manufacturer_id')
            ->where('p.status = 1')
            ->where('p.date_available <= NOW()')
            ->where('m2s.store_id = ?', (int) $this->config->get('config_store_id'))
            ->order(array('m.sort_order', 'm.name')); 
        
        if ($this->subCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->innerJoin(array('cp' => 'category_path'), 'cp.category_id = p2c.category_id')
                ->where('cp.path_id = ?', array($this->subCategory));
        }
        elseif ($this->topCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->where('p2c.category_id = ?', array($this->topCategory));
        }

		$query = $this->db->query($sql);
        self::$_cache['manufacturers'] = $query->rows;
        
        return $query->rows;
	}
    
    /**
     * Get Stock Statuses
     * 
     * @return array Returns array of existed stock statuses
     */
	public function getStockStatuses()
	{
        if (isset(self::$_cache['stock_statuses'])) {
            return self::$_cache['stock_statuses'];
        }
		$sql = new SqlStatement();
        $sql->select(array('id' => 'stock_status_id', 'name'))
                ->from('stock_status')
                ->where('language_id = ?', (int) $this->config->get('config_language_id'));
		
		$query = $this->db->query($sql);
		
        self::$_cache['stock_statuses'] = $query->rows;
        
        return $query->rows;
	}
    
    /**
     * Get Options
     * 
     * @return array Returns array of existed options in the given category 
     * and all their values
     */
	public function getOptions()
	{
        if (isset(self::$_cache['options'])) {
            return self::$_cache['options'];
        }
		$output = array();
        
        $sql = new SqlStatement();
        
        $columns = array('namegroup' => 'od.name', 'ovd.name', 'ovd.option_value_id', 'pov.option_id', 'ov.image', 'ov.sort_order');
        
        $sql->select($columns)
            ->from(array('p' => 'product'))
            ->innerJoin(array('p2s' => 'product_to_store'), 'p.product_id = p2s.product_id')
            ->innerJoin(array('pov' => 'product_option_value'), 'p.product_id = pov.product_id')
            ->innerJoin(array('od' => 'option_description'), 'pov.option_id = od.option_id')
            ->innerJoin(array('ovd' => 'option_value_description'), 'pov.option_value_id = ovd.option_value_id')
            ->innerJoin(array('o' => 'option'), 'pov.option_id = o.option_id')
            ->innerJoin(array('ov' => 'option_value'), 'pov.option_value_id = ov.option_value_id')
            ->where('p.status = 1')
            ->where('p.date_available <= NOW()')
            ->where('ovd.language_id = ?', (int) $this->config->get('config_language_id'))
            ->where('od.language_id = ?', (int) $this->config->get('config_language_id'))
            ->where('p2s.store_id = ?', (int) $this->config->get('config_store_id'))
            ->group(array('pov.option_value_id'))
            ->order(array('o.sort_order', 'ov.sort_order', 'od.name', 'ovd.name')); 
 
        if ($this->subCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->innerJoin(array('cp' => 'category_path'), 'cp.category_id = p2c.category_id')
                ->where('cp.path_id = ?', array($this->subCategory));
        }
        elseif ($this->topCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->where('p2c.category_id = ?', array($this->topCategory));
        }
        if ($this->conditions->manufacturer)
        {
            $sql->where('p.manufacturer_id = ?', array($this->conditions->manufacturer));
        }
		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {

            $r = array(
                'name' => $row['name'],
                'id' => $row['option_value_id'],
                'sort' => $row['sort_order']
            );
            
            if (isset($row['image'])) {
                $r['image'] = $row['image'];
            }
            if (!isset($output[$row['option_id']])) {
                $output[$row['option_id']] = array(
                    'name' => $row['namegroup'],
                    'values' => array()
                );
            }
            $output[$row['option_id']]['values'][] = $r;
        }
        self::$_cache['options'] = $output;
        
        return $output;
	}
    
    /**
     * Get Filters
     * 
     * @return array Returns array of existed filters in the given category 
     * and all their values
     */
    public function getFilters()
    {
        if (isset(self::$_cache['filters'])) {
            return self::$_cache['filters'];
        }
        $sql = new SqlStatement();
        
        $sql->select(array('namegroup' => 'fgd.name', 'fd.name', 'f.filter_id', 'fg.filter_group_id', 'f.sort_order'))
            ->from(array('p' => 'product'))
            ->innerJoin(array('pf' => 'product_filter'), 'p.product_id = pf.product_id')
            ->innerJoin(array('f' => 'filter'), 'f.filter_id = pf.filter_id')
            ->innerJoin(array('fd' => 'filter_description'), 'fd.filter_id = pf.filter_id')
            ->innerJoin(array('fg' => 'filter_group'), 'fg.filter_group_id = fd.filter_group_id')
            ->innerJoin(array('fgd' => 'filter_group_description'), 'fd.filter_group_id = fgd.filter_group_id')
            ->innerJoin(array('p2s' => 'product_to_store'), 'p.product_id = p2s.product_id')
            ->where('p.status = 1')
            ->where('p.date_available <= NOW()')
            ->where('fd.language_id = ?', (int) $this->config->get('config_language_id'))
            ->where('fgd.language_id = ?', (int) $this->config->get('config_language_id'))
            ->where('p2s.store_id = ?', (int) $this->config->get('config_store_id'))
            ->group(array('f.filter_id'))
            ->order(array('fg.sort_order', 'f.sort_order', 'fgd.name', 'fd.name'));
        
        if ($this->subCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->innerJoin(array('cp' => 'category_path'), 'cp.category_id = p2c.category_id')
                ->where('cp.path_id = ?', array($this->subCategory));
        }
        elseif ($this->topCategory) 
        {
            $sql->innerJoin(array('p2c' => 'product_to_category'), 'p.product_id = p2c.product_id')
                ->where('p2c.category_id = ?', array($this->topCategory));
        }
        if ($this->conditions->manufacturer)
        {
            $sql->where('p.manufacturer_id = ?', array($this->conditions->manufacturer));
        }
        $query = $this->db->query($sql);
        
        $output = array();
        
		foreach ($query->rows as $row) {

            $r = array(
                'name' => $row['name'],
                'id' => $row['filter_id'],
                'sort' => $row['sort_order']
            );
            
            if (!isset($output[$row['filter_group_id']])) {
                $output[$row['filter_group_id']] = array(
                    'name' => $row['namegroup'],
                    'values' => array()
                );
            }
            $output[$row['filter_group_id']]['values'][] = $r;
        }
        self::$_cache['filters'] = $output;
        
        return $output;
    }
    
    public function getCategories()
    {
        $sql = new SqlStatement();
        
        $sql->select(array('cd.name', 'id' => 'c.category_id', 'pid' => 'c.parent_id'))
            ->from(array('c' => 'category'))
            ->innerJoin(array('cd' => 'category_description'), 'c.category_id = cd.category_id')
            ->where('cd.language_id = ?', (int) $this->config->get('config_language_id'))
            ->order(array('c.parent_id', 'c.sort_order', 'LCASE(cd.name)'));
        
        $res = $this->db->query($sql);
        $output = array();
        foreach ($res->rows as $row) {
            $output[$row['id']] = array(
                'name' => $row['name'],
                'pid'  => $row['pid'],
            );
        }
        return $output;
    }
}
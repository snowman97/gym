<?php  
class ControllerExtensionModuleBrainyFilter extends Controller {
    private $_data = array();
    private $_currency;
    
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
    }
    
	public function index($moduleSettings) 
    {
        // the following break point is set in order to prevent extra call of this action
        // while filtering via AJAX. The chain represented below is a reason of this.
        // ajaxfilter() action -> Category controller -> column left modules -> index() action
        if (isset($this->request->get['route']) && $this->request->get['route'] === 'extension/module/brainyfilter/ajaxfilter') {
            return;
        }
        
        $settings = $this->_getSettings($moduleSettings['bf_layout_id']);
        
		$data = $this->_prepareFilterInitialData();
        
        if (isset($this->request->get['route']) && $this->request->get['route'] === 'product/category'
                && isset($settings['categories'][$data['filter_category_id']])) {
            return;
        }
        
		$this->language->load('extension/module/brainyfilter');
        $this->language->load('product/category');
        $isMijoShop = class_exists('MijoShop') && defined('JPATH_MIJOSHOP_OC');
        
        if ($isMijoShop) {
            MijoShop::get('base')->addHeader(JPATH_MIJOSHOP_OC . '/catalog/view/javascript/brainyfilter.js', false);
        } else {
            $this->document->addScript('catalog/view/javascript/brainyfilter.js');
        }
		
		if(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/brainyfilter.css')) {
        	$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template'). '/stylesheet/brainyfilter.css');
    	}else{
    		$this->document->addStyle('catalog/view/theme/default/stylesheet/brainyfilter.css');
    	}
		
		$this->_data['base'] = preg_replace('/https?:\/\/[^\/]+/', '', $this->config->get('config_url'));

		$this->load->model('extension/module/brainyfilter');
        $model = new ModelExtensionModuleBrainyFilter($this->registry);
        
		$this->_data['path'] = (isset($this->request->get['path'])) ? $this->request->get['path'] : "";

		$model->setData($data);
        
        $conditions = $model->getConditions();
        
        $this->_data['selected'] = array();
        foreach ($conditions->filters as $guid => $values) {
            $this->_data['selected'][$guid] = $values;
        }
        
		$this->_data['heading_title']        = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
		$this->_data['default_value_select'] = $this->language->get('default_value_select');
		$this->_data['lang_price']           = $this->language->get('price_header');
		$this->_data['lang_categories']      = $this->language->get('categories_header');
		$this->_data['lang_search']          = $this->language->get('entry_search');
		$this->_data['lang_submit']          = $this->language->get('submit');
		$this->_data['min_max']              = $this->language->get('min_max');
		$this->_data['reset']                = $this->language->get('reset');
		$this->_data['lang_show_more']       = $this->language->get('entry_show_more');
		$this->_data['lang_show_less']       = $this->language->get('entry_show_less');
		$this->_data['lang_block_title']     = $this->language->get('entry_block_title');
		$this->_data['lang_empty_slider']    = $this->language->get('empty_slider_value');
        $this->_data['lang_empty_list']      = $this->language->get('text_empty');
        
		$this->_data['limit_height']         = $settings['behaviour']['limit_height']['enabled'];
		$this->_data['limit_height_opts']    = $settings['behaviour']['limit_height']['height'];
		
		$this->_data['sliding']              = $settings['behaviour']['limit_items']['enabled'];
		$this->_data['slidingOpts']          = $settings['behaviour']['limit_items']['number_to_show'];
		$this->_data['slidingMin']           = $settings['behaviour']['limit_items']['number_to_hide'];
        
        $basicSettings  = $this->config->get('brainyfilter_layout_basic');
        $postponedCount = $basicSettings['global']['postponed_count'] && empty($conditions->filters)
                && (!isset($conditions->manufacturer) || array($conditions->manufacturer) === $this->filters['m0']);
        $this->_data['postponedCount'] = $postponedCount;
        
        $lang = (int)$this->config->get('config_language_id');
        if (!empty($settings["behaviour"]["filter_name"][$lang])) {
           $this->_data['lang_block_title'] = $settings["behaviour"]["filter_name"][$lang];
        }elseif (!empty($basicSettings["behaviour"]["filter_name"][$lang])) {
           $this->_data['lang_block_title'] = $basicSettings["behaviour"]["filter_name"][$lang];
        }
        $filters = array();
        $secSettings = $settings['behaviour']['sections'];
        if ($secSettings['attribute']['enabled']) {
            $arr = $model->getAttributes();
            $this->_applySettings($arr, 'attributes', $settings);
            if (count($arr)) {
                $filters[] = array(
                    'type'  => 'attribute',
                    'order' => (int)$settings['behaviour']['sort_order']['attribute'],
                    'array' => $arr,
                    'collapsed' => (bool)$secSettings['attribute']['collapsed'],
                );
            }
        }
		if ($secSettings['option']['enabled']) {
            $arr = $model->getOptions();
            $this->_applySettings($arr, 'options', $settings);
            if (count($arr)) {
                $filters[] = array(
                    'type'  => 'option',
                    'order' => (int)$settings['behaviour']['sort_order']['option'],
                    'array' => $arr,
                    'collapsed' => (bool)$secSettings['option']['collapsed'],
                );
            }
		}
		if ($secSettings['filter']['enabled']) {
            $arr = $model->getFilters();
            $this->_applySettings($arr, 'filters', $settings);
            if (count($arr)) {
                $filters[] = array(
                    'type'  => 'filter',
                    'order' => (int)$settings['behaviour']['sort_order']['filter'],
                    'array' => $arr,
                    'collapsed' => (bool)$secSettings['filter']['collapsed'],
                );
            }
		}
		if ($secSettings['manufacturer']['enabled']) {
            $arr = $model->getManufacturers();
            if (count($arr)) {
                $filters[] = array(
                    'type'  => 'manufacturer',
                    'order' => (int)$settings['behaviour']['sort_order']['manufacturer'],
                    'array' => array('0' => array(
                        'name'   => $this->language->get('manufacturers'),
                        'type'   => $secSettings['manufacturer']['control'],
                        'values' => $arr,
                    )),
                    'collapsed' => (bool)$secSettings['manufacturer']['collapsed'],
                );
            }
		}
		if ($secSettings['stock_status']['enabled']) {
            $arr = $model->getStockStatuses();
            if (count($arr)) {
                $filters[] = array(
                    'type'  => 'stock_status',
                    'order' => (int)$settings['behaviour']['sort_order']['stock_status'],
                    'array' => array('0' => array(
                        'name'   => $this->language->get('stock_status'),
                        'type'   => 'checkbox',
                        'values' => $arr
                    )),
                    'collapsed' => (bool)$secSettings['stock_status']['collapsed'],
                );
            }
		}
        if ($secSettings['rating']['enabled']) {
            $filters[] = array(
                'type'  => 'rating',
                'order' => (int)$settings['behaviour']['sort_order']['rating'],
                'array' => array('0' => array(
                        'name' => $this->language->get('rating'),
                        'values' => array(
                            array('id' => 1, 'name' => '1'), 
                            array('id' => 2, 'name' => '2'), 
                            array('id' => 3, 'name' => '3'), 
                            array('id' => 4, 'name' => '4'), 
                            array('id' => 5, 'name' => '5')),
                        'type' => 'checkbox')),
                'collapsed' => (bool)$secSettings['rating']['collapsed'],
            );
        }
        if ($secSettings['price']['enabled']) {
            if ($postponedCount) {
                $minMax = array('min' => 0, 'max' => '0');
            } else {
                $minMax = $model->getMinMaxCategoryPrice($data);
            }
			$min = floor($this->currency->format($minMax['min'], $this->_currency, '', false));
			$max = ceil($this->currency->format($minMax['max'], $this->_currency, '', false));
//            if ($minMax['max'] > 0) {
                $filters[] = array(
                    'type' => 'price',
                    'control' => $secSettings['price']['control'],
                    'order' => (int)$settings['behaviour']['sort_order']['price'],
                    'collapsed' => (bool)$secSettings['price']['collapsed'],
                    'min' => $min,
                    'max' => $max,
                );
//            }
        }
        if ($secSettings['search']['enabled']) {
            $filters[] = array(
                'type' => 'search',
                'order' => (int)$settings['behaviour']['sort_order']['search'],
                'collapsed' => (bool)$secSettings['search']['collapsed'],
            );
        }
        if ($secSettings['category']['enabled']) {
            $arr = $model->getCategories();
            $this->_sortSubCategories($arr);
            $this->_filterSubCategories($arr, $data['filter_category_id']);
            if (count($arr)) {
                $filters[] = array(
                    'type'  => 'category',
                    'control' => $secSettings['category']['control'],
                    'order' => (int)$settings['behaviour']['sort_order']['category'],
                    'values' => $arr,
                    'collapsed' => (bool)$secSettings['category']['collapsed'],
                );
            }
        }
		$this->_data['priceMin'] = isset($min) ? $min : 0;
		$this->_data['priceMax'] = isset($max) ? $max : 0;
		$this->_data['lowerlimit'] = isset($conditions->price->inputMin) ? $conditions->price->inputMin : $this->_data['priceMin'];
		$this->_data['upperlimit'] = isset($conditions->price->inputMax) ? $conditions->price->inputMax : $this->_data['priceMax'];
        $this->_data['bfSearch'] = $conditions->search;
        
        // sort filter sections
        usort($filters, array(__CLASS__, '_sortProperties'));

        $this->_data['filters'] = $filters;
        
        if ($this->currency->getsymbolleft($this->_currency)) {
			$this->_data['currency_symbol']  = $this->currency->getsymbolleft($this->_currency);
			$this->_data['cur_symbol_side']  = 'left';
		} else {
			$this->_data['currency_symbol']  = $this->currency->getsymbolright($this->_currency);
			$this->_data['cur_symbol_side']  = 'right';
		}
        if (!isset($this->request->get['route']) 
                || ($this->request->get['route'] !== 'product/category' 
                 && $this->request->get['route'] !== 'product/search'
                 && $this->request->get['route'] !== 'extension/module/brainyfilter/filter')) {
            $this->_data['redirectToUrl'] = $this->url->link('extension/module/brainyfilter/filter', (isset($this->request->get['path'])) ? 'path=' . $this->request->get['path'] : '');
        } else {
            $this->_data['redirectToUrl'] = '';
        }
        $this->_data['currentPath'] = isset($this->request->get['path']) ? $this->request->get['path'] : false;
        $this->_data['currentRoute'] = isset($this->request->get['route']) ? $this->request->get['route'] : '';
        $this->_data['manufacturerId'] = isset($this->request->get['manufacturer_id']) ? $this->request->get['manufacturer_id'] : '';
                
		$this->_data['settings']  = $settings;
        $this->_data['layout_id'] = $moduleSettings['bf_layout_id'];
        $this->_data['layout_position'] = $moduleSettings['position'];
        
        if (!$postponedCount && $settings['behaviour']['product_count']) {
            $this->_data['totals'] = $model->calculateTotals();
        }

        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            return $this->load->view('extension/module/brainyfilter', $this->_data);
        } else {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/brainyfilter.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/module/brainyfilter.tpl', $this->_data);
            } else {
                return $this->load->view('default/template/module/brainyfilter.tpl', $this->_data);
            }
        }
	}
	
	private static function _sortProperties($a, $b)
	{
		return $a['order'] - $b['order'];
	}
	
	public function ajaxfilter()
	{
        $this->registry->set('bf_force_tmp_table_creation', true);
		$data = $this->_prepareFilterInitialData();

        $json = array();
        $route = $this->_getRequestParam('curRoute');
        if ($route && $this->_getRequestParam('withContent')) {
            $this->request->get['route'] = $route;
            $this->load->controller($route, $data);
            $json['products'] = $this->response->getOutput();
        }

		$this->load->model('extension/module/brainyfilter');
        $model = new ModelExtensionModuleBrainyFilter($this->registry);
        
        $model->setData($data);
		
        if ((bool)$this->_getRequestParam('count', false)) {
            $json['brainyfilter'] = $model->calculateTotals();
		}
        if ((bool)$this->_getRequestParam('price', false)) {
			$minMax = $model->getMinMaxCategoryPrice();
			$min = floor($this->currency->format($minMax['min'], $this->_currency, '', false));
			$max = ceil($this->currency->format($minMax['max'], $this->_currency, '', false));
            $json['min'] = $min;
            $json['max'] = $max;
		}
		
		$isMijoShop = class_exists('MijoShop') && defined('JPATH_MIJOSHOP_OC');
		if ($isMijoShop) {
			header('Content-Type: application/json');
			die(json_encode($json));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
    
    /**
     * Action for default view. Applying of Brainy Filter from all pages except
     * product/category and product/search will cause redirection to the action.
     */
    public function filter()
    {
        $data = $this->_prepareFilterInitialData();
        
        $this->load->controller('product/category', $data);
    }

	private function _prepareFilterInitialData()
	{
		$categoryId = false;
        if ($this->_getRequestParam('category_id')) {
            $categoryId = $this->_getRequestParam('category_id');
        } elseif(isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
			$categoryId = array_pop($parts);
		}
		
        if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_product_limit');
		}
        
		$settings = $this->config->get('brainyfilter_layout_basic');
        
		$data = array(
			'filter_category_id' => $categoryId,
            'filter_name' => $this->_getRequestParam('search', ''),
            'filter_tag'  => $this->_getRequestParam('tag', $this->_getRequestParam('search', '')),
            'filter_description'  => $this->_getRequestParam('description', ''),
            'filter_sub_category' => $this->_getRequestParam('sub_category', (bool)$settings['global']['subcategories_fix']),
            'filter_manufacturer_id' => $this->_getRequestParam('manufacturer_id', 0),
            'filter_specials_only' => $this->_getRequestParam('route') === 'product/special' || $this->_getRequestParam('curRoute') === 'product/special' ,
            'limit' => $limit,
		);

		return $data;
	}
    
    private function _getRequestParam($name, $default = null)
    {
        if (isset($this->request->get[$name])) {
            return $this->request->get[$name];
        }
        return $default;
    }
    
    
    private function _applySettings(&$filters, $type, $settings)
    {
        if (!is_array($filters) || !count($filters)) {
            return;
        }
        $secSettings = isset($settings[$type]) ? $settings[$type] : null;
        $defSettings = $settings["{$type}_default"];
        foreach ($filters as $k => $f) {
            if (   (!$defSettings['enable_all'] && !isset($secSettings[$k]['enabled']))
                || (isset($secSettings[$k]['enabled']) && !$secSettings[$k]['enabled']) ) {
                unset($filters[$k]);
            } else {
                $f['type'] = isset($secSettings[$k]['control']) ? $secSettings[$k]['control'] : $defSettings['control'];
                if (isset($secSettings[$k]['mode']) || isset($defSettings['mode'])) {
                    $f['mode'] = isset($secSettings[$k]['mode']) ? $secSettings[$k]['mode'] : $defSettings['mode'];
                }
                if (in_array($f['type'], array('slider', 'slider_lbl', 'slider_lbl_inp'))) {
                    $values = array();
                    foreach ($f['values'] as $val) {
                        $values[] = array('n' => $val['name'], 's' => $val['sort']);
                    }
                    $f['values'] = $values;
                    $f['min'] = array_shift($values);
                    $f['max'] = array_pop($values);
                }
                $filters[$k] = $f;
            }
        }
    }
    
    private function _sortSubCategories(&$arr)
    {
        $links = array();
        foreach ($arr as $catId => $cat) {
            $links[$catId] = & $arr[$catId];
        }
        foreach ($arr as $id => $cat) {
            $parent = $cat['pid'];
            $level = 0;
            $arr[$id]['parents'] = array();
            while(isset($arr[$parent]) && $level < count($arr)) {
                $arr[$id]['parents'][] = $parent;
                $parent = $arr[$parent]['pid'];
                $level ++;
            }
            $arr[$id]['level'] = $level;
        }
        foreach ($arr as $catId => $cat) {
            if (isset($links[$cat['pid']])) {
                $links[$cat['pid']]['children'][$catId] = $arr[$catId];
                unset($arr[$catId]);
                $links[$catId] = & $links[$cat['pid']]['children'][$catId];
            }
        }
        
        $arr = $this->_convertSubCategoriesToArray($arr);
        
        return $arr;
    }
    
    private function _convertSubCategoriesToArray($arr) {
        $out = array();
        if (count($arr)) {
            foreach ($arr as $id => $item) {
                $children = isset($item['children']) 
                        ? $this->_convertSubCategoriesToArray($item['children'])
                        : array();
                unset($item['children']);
                $item['id'] = $id;
                $out[] = $item;
                if (count($children)) {
                    $out = array_merge($out, $children);
                }
            }
        }
        return $out;
    }
    
    private function _filterSubCategories(&$arr, $topCat) {
        foreach ($arr as $k => $item) {
            $parId = array_search($topCat, $item['parents']);
            if ($parId === false) {
                unset($arr[$k]);
            } else {
                $arr[$k]['level'] -= count($item['parents']) - $parId;
            }
        }
    }
    
    private function _getSettings($layoutId)
    {
        $bfSettings = array();
        if ($this->config->get('brainyfilter_layout_basic')) {
            $bfSettings['basic'] = $this->config->get('brainyfilter_layout_basic');
        }
        
        $settings = self::_arrayReplaceRecursive($bfSettings['basic'], $this->config->get('brainyfilter_layout_' . $layoutId));
        
        return $settings;
    }

    /**
     * An alternative of PHP native function array_replace_recursive(), which is designed
     * to bring similar functionality for PHP versions lower then 5.3. <br>
     * <b>Note</b>: unlike PHP native function the method holds only two arrays as parameters.
     * @param array $array An original array
     * @param array $array1 Replacement
     * @return array
     */
    private static function _arrayReplaceRecursive($array, $array1)
    {
        if (is_array($array1) && count($array1)) {
            foreach ($array1 as $key => $value) {
                if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
                    $array[$key] = array();
                }

                if (is_array($value)) {
                    $value = self::_arrayReplaceRecursive($array[$key], $value);
                }
                $array[$key] = $value;
            }
        }
        return $array;
    }
    
    public function cron()
    {
        $settings = $this->config->get('brainyfilter_layout_basic');
        
        $key = (isset($settings['global']['cron_secret_key'])) ? $settings['global']['cron_secret_key'] : null;
        
        $getKey = (isset($this->request->get['key'])) ? $this->request->get['key'] : null;
        
        if (!$key || $key !== $getKey) {
            die('unauthorized');
        }
        
        require_once 'admin/model/module/brainyfilter.php';
        
        $model = new ModelExtensionModuleBrainyFilter($this->registry);
        $model->fillTaxRateTable();
        $model->fillCacheTable();
        
        die('done');
    }
}

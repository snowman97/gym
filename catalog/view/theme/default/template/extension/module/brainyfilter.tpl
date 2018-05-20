<?php
$isHorizontal = $layout_position === 'content_top' || $layout_position === 'content_bottom';
$isResponsive = (bool) $settings['style']['responsive']['enabled'];
$responsivePos = $settings['style']['responsive']['position'] === 'right' ? 'bf-right' : 'bf-left';

if (!function_exists('totalsDecorator')) {
    function totalsDecorator($groupId, $val, $totals = array(), $selected = array()) {
        if (!isset($totals[$groupId][$val]) && !isset($selected[$groupId])) {
            return '';
        }

        $total = isset($totals[$groupId][$val]) ? $totals[$groupId][$val] : 0;
        $addPlusSign = isset($selected[$groupId]);

        return '<span class="bf-count ' . (!$total ? 'bf-empty' : '') . '">' . ($addPlusSign ? '+' : '') . $total . '</span>';
    }
}
if (!function_exists('isEmptyStock')) {
    function isEmptyStock($groupId, $val, $postponedCount, $totals = array(), $selected = array()) {
        $inStock = $postponedCount || (isset($totals[$groupId][$val]) && $totals[$groupId][$val]);
        $inSelected = isset($selected[$groupId]) && in_array($val, $selected[$groupId]);
        return !$inStock && !$inSelected;
    }
}

?>
<style type="text/css">
    .bf-responsive.bf-active.bf-layout-id-<?php echo $layout_id;?> .bf-check-position {
        top: <?php echo (int)$settings['style']['responsive']['offset']; ?>px;
    }
    .bf-responsive.bf-active.bf-layout-id-<?php echo $layout_id;?> .bf-btn-show, 
    .bf-responsive.bf-active.bf-layout-id-<?php echo $layout_id;?> .bf-btn-reset {
        top: <?php echo (int)$settings['style']['responsive']['offset']; ?>px;
    }
    .bf-layout-id-<?php echo $layout_id;?> .bf-btn-show {
    <?php if (isset($settings['style']['resp_show_btn_color']['val'])) : ?>
        background: <?php echo $settings['style']['resp_show_btn_color']['val']; ?>;
    <?php endif; ?>
    }
    .bf-layout-id-<?php echo $layout_id;?> .bf-btn-reset {
    <?php if (isset($settings['style']['resp_reset_btn_color']['val'])) : ?>
        background: <?php echo $settings['style']['resp_reset_btn_color']['val']; ?>;
    <?php endif; ?>
    }
    .bf-layout-id-<?php echo $layout_id;?> .bf-attr-header{
       <?php echo isset($settings['style']['block_header_background']['val'])  ? 'background: '.$settings['style']['block_header_background']['val'].';':''; ?> 
       <?php echo isset($settings['style']['block_header_text']['val']) ? 'color: '.$settings['style']['block_header_text']['val'].';':''; ?> 
    }
    .bf-layout-id-<?php echo $layout_id;?> .bf-count{
        <?php echo isset($settings['style']['product_quantity_background']['val']) ? 'background: '.$settings['style']['product_quantity_background']['val'].';':''; ?> 
       <?php echo isset($settings['style']['product_quantity_text']['val']) ? 'color: '.$settings['style']['product_quantity_text']['val'].';':''; ?> 
    }
   .bf-layout-id-<?php echo $layout_id;?> .ui-widget-header {
        <?php echo isset($settings['style']['price_slider_area_background']['val']) ? 'background: '.$settings['style']['price_slider_area_background']['val'].';':''; ?> 
   }
   .bf-layout-id-<?php echo $layout_id;?> .ui-widget-content {
         <?php echo isset($settings['style']['price_slider_background']['val']) ? 'background: '.$settings['style']['price_slider_background']['val'].';':''; ?> 
         <?php echo isset($settings['style']['price_slider_border']['val']) ? 'border:1px solid '.$settings['style']['price_slider_border']['val'].';':''; ?> 
   }
.bf-layout-id-<?php echo $layout_id;?> .ui-state-default {
         <?php echo isset($settings['style']['price_slider_handle_background']['val']) ? 'background: '.$settings['style']['price_slider_handle_background']['val'].';':''; ?> 
         <?php echo isset($settings['style']['price_slider_handle_border']['val']) ? 'border:1px solid '.$settings['style']['price_slider_handle_border']['val'].';':''; ?> 
   }
  .bf-layout-id-<?php echo $layout_id;?> .bf-attr-group-header{
        <?php echo isset($settings['style']['group_block_header_background']['val']) ? 'background: '.$settings['style']['group_block_header_background']['val'].';':''; ?> 
       <?php echo isset($settings['style']['group_block_header_text']['val']) ? 'color: '.$settings['style']['group_block_header_text']['val'].';':''; ?> 
  }
  <?php if ($settings['behaviour']['hide_empty']) : ?>
  .bf-layout-id-<?php echo $layout_id;?> .bf-row.bf-disabled, 
  .bf-layout-id-<?php echo $layout_id;?> .bf-horizontal .bf-row.bf-disabled {
      display: none;
  }
  <?php endif; ?>
</style>
<?php if (count($filters)) : ?>
<div class="bf-panel-wrapper<?php if($isResponsive) : ?> bf-responsive<?php endif; ?> <?php echo $responsivePos; ?> bf-layout-id-<?php echo $layout_id;?>">
    <div class="bf-btn-show"></div>
    <a class="bf-btn-reset" onclick="BrainyFilter.reset();"></a>
    <div class="box bf-check-position <?php if ($isHorizontal) : ?>bf-horizontal<?php endif; ?>">
        <div class="box-heading"><?php echo $lang_block_title; ?><?php if ($isHorizontal) : ?><a class="bf-toggle-filter-arrow"></a><input type="reset" class="bf-buttonclear" onclick="BrainyFilter.reset();" value="<?php echo $reset; ?>" /><?php endif; ?></div>
        <div class="brainyfilter-panel box-content <?php if ($settings['submission']['hide_panel']) : ?>bf-hide-panel<?php endif; ?>">
            <form class="bf-form 
                    <?php if ($settings['behaviour']['product_count']) : ?> bf-with-counts<?php endif; ?> 
                    <?php if ($sliding) : ?> bf-with-sliding<?php endif; ?>
                    <?php if ($settings['submission']['submit_type'] === 'button' && $settings['submission']['submit_button_type'] === 'float') : ?> bf-with-float-btn<?php endif; ?>
                    <?php if ($limit_height) : ?> bf-with-height-limit<?php endif; ?>"
                  data-height-limit="<?php echo $limit_height_opts; ?>"
                  data-visible-items="<?php echo $slidingOpts; ?>"
                  data-hide-items="<?php echo $slidingMin; ?>"
                  data-submit-type="<?php echo $settings['submission']['submit_type']; ?>"
                  data-submit-delay="<?php echo (int)$settings['submission']['submit_delay_time']; ?>"
                  data-resp-max-width="<?php echo (int)$settings['style']['responsive']['max_width']; ?>"
                  data-resp-collapse="<?php echo (int)$settings['style']['responsive']['collapsed']; ?>"
                  data-resp-max-scr-width ="<?php echo (int)$settings['style']['responsive']['max_screen_width']; ?>"
                  method="get" action="index.php">
                <?php if ($currentRoute === 'product/search') : ?>
                <input type="hidden" name="route" value="product/search" />
                <?php else : ?>
                <input type="hidden" name="route" value="product/category" />
                <?php endif; ?>
                <?php if ($currentPath) : ?>
                <input type="hidden" name="path" value="<?php echo $currentPath; ?>" />
                <?php endif; ?>
                <?php if ($manufacturerId) : ?>
                <input type="hidden" name="manufacturer_id" value="<?php echo $manufacturerId; ?>" />
                <?php endif; ?>

                <?php foreach ($filters as $i => $section) : ?>
                        
                    <?php if ($section['type'] == 'price') : ?>
                        <?php $sliderType = $section['control'] === 'slider_lbl_inp' ? 3 : ($section['control'] === 'slider_lbl' ? 2 : 1); ?>
                        <?php $inputType  = in_array($sliderType, array(1, 3)) ? 'text' : 'hidden'; ?>
                        <div class="bf-attr-block bf-price-filter <?php if ($isHorizontal && isset($filters[$i + 1]) && $filters[$i + 1]['type'] === 'search') : ?>bf-left-half<?php endif; ?>">
                        <div class="bf-attr-header<?php if ($section['collapsed']) : ?> bf-collapse<?php endif; ?><?php if (!$i) : ?> bf-w-line<?php endif; ?>">
                            <?php echo $lang_price; ?><span class="bf-arrow"></span>
                        </div>
                        <div class="bf-attr-block-cont">
                            <div class="bf-price-container box-content bf-attr-filter">
                                <?php if (in_array($sliderType, array(1, 3))) : ?>
                                <div class="bf-cur-symb">
                                    <span class="bf-cur-symb-left"><?php echo $currency_symbol; ?></span>
                                    <input type="text" class="bf-range-min" name="bfp_price_min" value="<?php echo $lowerlimit; ?>" size="4" />
                                    <span class="ndash">&#8211;</span>
                                    <span class="bf-cur-symb-left"><?php echo $currency_symbol; ?></span>
                                    <input type="text" class="bf-range-max" name="bfp_price_max" value="<?php echo $upperlimit; ?>" size="4" /> 
                                </div>
                                <?php else : ?>
                                <input type="hidden" class="bf-range-min" name="bfp_price_min" value="<?php echo $lowerlimit; ?>" />
                                <input type="hidden" class="bf-range-max" name="bfp_price_max" value="<?php echo $upperlimit; ?>" /> 
                                <?php endif; ?>
                                <div class="bf-price-slider-container <?php if($sliderType === 2 || $sliderType === 3): ?>bf-slider-with-labels<?php endif; ?>">
                                    <div class="bf-slider-range" data-slider-type="<?php echo $sliderType; ?>"></div>
                                </div>
                            </div>
                        </div>
                        </div>
                
                    <?php elseif ($section['type'] == 'search') : ?>
                
                        <div class="bf-attr-block bf-keywords-filter <?php if ($isHorizontal && isset($filters[$i + 1]) && $filters[$i + 1]['type'] === 'price') : ?>bf-left-half<?php endif; ?>">
                        <div class="bf-attr-header<?php if ($section['collapsed']) : ?> bf-collapse<?php endif; ?><?php if (!$i) : ?> bf-w-line<?php endif; ?>">
                            <?php echo $lang_search; ?><span class="bf-arrow"></span>
                        </div>
                        <div class="bf-attr-block-cont">
                            <div class="bf-search-container bf-attr-filter">
                                <div>
                                    <input type="text" class="bf-search" name="bfp_search" value="<?php echo $bfSearch; ?>" /> 
                                </div>
                            </div>
                        </div>
                        </div>
                        
                    <?php elseif ($section['type'] == 'category') : ?>
                        
                        <div class="bf-attr-block">
                        <div class="bf-attr-header<?php if ($section['collapsed']) : ?> bf-collapse<?php endif; ?><?php if (!$i) : ?> bf-w-line<?php endif; ?>">
                            <?php echo $lang_categories; ?><span class="bf-arrow"></span>
                        </div>
                        <div class="bf-attr-block-cont">
                            <?php $groupUID = 'c0'; ?>

                            <?php if ($section['control'] == 'select') : ?>
                            <div class="bf-attr-filter bf-attr-<?php echo $groupUID; ?> bf-row">
                                <div class="bf-cell">
                                    <select name="<?php echo "bfp_{$groupUID}"; ?>">
                                        <option value="" class="bf-default"><?php echo $default_value_select; ?></option>
                                        <?php foreach ($section['values'] as $cat) : $catId = $cat['id'] ?>
                                            <?php $isSelected = isset($selected[$groupUID]) && in_array($catId, $selected[$groupUID]); ?>
                                            <option value="<?php echo $catId; ?>" class="bf-attr-val" <?php if ($isSelected) : ?>selected="true"<?php endif; ?>>
                                                <?php echo str_repeat('-', $cat['level']) . ' ' . $cat['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php else : ?>
                                <?php foreach ($section['values'] as $cat) : $catId = $cat['id']; ?>
                                <div class="bf-attr-filter bf-attr-<?php echo $groupUID; ?> bf-row <?php if (isset($totals) && isEmptyStock($groupUID, $catId, $postponedCount, $totals, $selected) && $settings['behaviour']['hide_empty']): ?>bf-disabled<?php endif; ?>">
                                    <span class="bf-cell bf-c-1">
                                        <input id="bf-attr-<?php echo $groupUID . '_' . $catId . '_' . $layout_id; ?>"
                                               data-filterid="bf-attr-<?php echo $groupUID . '_' . $catId; ?>"
                                               type="<?php echo $section['control']; ?>" 
                                               name="<?php echo "bfp_{$groupUID}"; ?><?php if ($section['control'] === 'checkbox') { echo "_{$catId}"; } ?>"
                                               value="<?php echo $catId; ?>" 
                                               <?php if (isset($selected[$groupUID]) && in_array($catId, $selected[$groupUID])) : ?>checked="true"<?php endif; ?> />
                                    </span>
                                    <span class="bf-cell bf-c-2 bf-cascade-<?php echo $cat['level']; ?>">
                                        <span class="bf-hidden bf-attr-val"><?php echo $catId; ?></span>
                                        <label for="bf-attr-<?php echo $groupUID . '_' . $catId . '_' . $layout_id; ?>">
                                            <?php echo $cat['name']; ?>
                                        </label>
                                    </span>
                                    <span class="bf-cell bf-c-3"><?php if (isset($totals)) echo totalsDecorator($groupUID, $catId, $totals, $selected); ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        </div>
                
                    <?php else : ?>
                        
                        <?php $curGroupId = null; ?>
                        <?php foreach ($section['array'] as $groupId => $group) : ?>
                            <?php if (isset($group['group_id']) && $settings['behaviour']['attribute_groups']) : ?>
                                <?php if ($curGroupId != $group['group_id']) : ?>
                                    <div class="bf-attr-group-header"><?php echo $group['group']; ?></div>
                                    <?php $curGroupId = $group['group_id']; ?>
                                <?php endif; ?>
                                
                            <?php endif; ?>
                            <?php $groupUID = substr($section['type'], 0, 1) . $groupId; ?>
                            <div class="bf-attr-block<?php if (in_array($group['type'], array('slider', 'slider_lbl', 'slider_lbl_inp'))) : ?> bf-slider<?php endif; ?>">
                            <div class="bf-attr-header<?php if ($section['collapsed']) : ?> bf-collapse<?php endif; ?><?php if (!$i) : ?> bf-w-line<?php endif; ?>">
                                <?php echo $group['name']; ?><span class="bf-arrow"></span>
                            </div>
                            <div class="bf-attr-block-cont">
                                <?php $group['type'] = isset($group['type']) ? $group['type'] : 'checkbox'; ?>
                                
                                <?php if ($group['type'] == 'select') : ?>
                                
                                    <div class="bf-attr-filter bf-attr-<?php echo $groupUID; ?> bf-row">
                                        <div class="bf-cell">
                                            <select name="<?php echo "bfp_{$groupUID}"; ?>">
                                                <option value="" class="bf-default"><?php echo $default_value_select; ?></option>
                                                <?php foreach ($group['values'] as $value) : ?>
                                                    <?php $isSelected = isset($selected[$groupUID]) && in_array($value['id'], $selected[$groupUID]); ?>
                                                    <option value="<?php echo $value['id']; ?>" class="bf-attr-val" <?php if ($isSelected) : ?>selected="true"<?php endif; ?>
                                                        <?php if(!isset($totals[$groupUID][$value['id']]) && !$isSelected): ?>
                                                            disabled="disabled"
                                                        <?php endif; ?>
                                                        <?php if (isset($totals[$groupUID][$value['id']]) && !$isSelected) : ?>
                                                            data-totals="<?php echo (isset($totals[$groupUID][$value['id']]) ? $totals[$groupUID][$value['id']] : 0); ?>"
                                                        <?php endif; ?>>
                                                        <?php echo $value['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                
                                <?php elseif (in_array($group['type'], array('slider', 'slider_lbl', 'slider_lbl_inp'))) : ?>
                                
                                <div class="bf-attr-filter bf-attr-<?php echo $groupUID; ?> bf-row">
                                    <div class="bf-cell">
                                        <div class="bf-slider-inputs">
                                            <?php $isMinSet = isset($selected[$groupUID]['min']); ?>
                                            <?php $isMaxSet = isset($selected[$groupUID]['max']); ?>
                                            <?php $sliderType = $group['type'] === 'slider_lbl_inp' ? 3 : ($group['type'] === 'slider_lbl' ? 2 : 1); ?>
                                            <input type="hidden" name="bfp_min_<?php echo $groupUID; ?>" value="<?php echo $isMinSet ? $selected[$groupUID]['min'] : 'na'; ?>" class="bf-attr-min-<?php echo $groupUID; ?>" data-min-limit="<?php echo $group['min']['s']; ?>" />
                                            <input type="hidden" name="bfp_max_<?php echo $groupUID; ?>" value="<?php echo $isMaxSet ? $selected[$groupUID]['max'] : 'na'; ?>" class="bf-attr-max-<?php echo $groupUID; ?>" data-max-limit="<?php echo $group['max']['s']; ?>" /> 
                                            <?php if ($group['type'] !== 'slider_lbl') : ?>
                                            <?php $minLbl = ''; $maxLbl = '';
                                                if ($isMinSet) {
                                                    foreach ($group['values'] as $v) {
                                                        if ($v['s'] == $selected[$groupUID]['min']) {
                                                            $minLbl = $v['n'];
                                                            break;
                                                        }
                                                    } 
                                                }
                                                if ($isMaxSet) {
                                                    foreach ($group['values'] as $v) {
                                                        if ($v['s'] == $selected[$groupUID]['max']) {
                                                            $maxLbl = $v['n'];
                                                            break;
                                                        }
                                                    } 
                                                }
                                            ?>
                                            <input type="text" name="" class="bf-slider-text-inp-min bf-slider-input" value="<?php echo $minLbl; ?>" placeholder="<?php echo $lang_empty_slider; ?>" />
                                            <span class="ndash">&#8211;</span>
                                            <input type="text" name="" class="bf-slider-text-inp-max bf-slider-input" value="<?php echo $maxLbl; ?>" placeholder="<?php echo $lang_empty_slider; ?>" />
                                            <?php endif; ?>
                                        </div>
                                        <div class="bf-slider-container-wrapper <?php if($sliderType === 2 || $sliderType === 3): ?>bf-slider-with-labels<?php endif; ?>">
                                            <div class="bf-slider-container" data-slider-group="<?php echo $groupUID; ?>" data-slider-type="<?php echo $sliderType; ?>"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php elseif ($group['type'] === 'grid') : ?>
                                
                                <div class="bf-attr-filter bf-attr-<?php echo $groupUID; ?> bf-row">
                                    <div class="bf-grid">
                                        <?php foreach ($group['values'] as $value) : ?>
                                        <?php $valueId  = $value['id']; ?>
                                        <div class="bf-grid-item">
                                            <input id="bf-attr-<?php echo $groupUID . '_' . $valueId . '_' . $layout_id; ?>" class="bf-hidden"
                                                    data-filterid="bf-attr-<?php echo $groupUID . '_' . $valueId; ?>"
                                                    type="radio" 
                                                    name="<?php echo "bfp_{$groupUID}"; ?>"
                                                    value="<?php echo $valueId; ?>" 
                                                    <?php if (isset($selected[$groupUID]) && in_array($valueId, $selected[$groupUID])) : ?>checked="true"<?php endif; ?> />
                                            <label for="bf-attr-<?php echo $groupUID . '_' . $valueId . '_' . $layout_id; ?>">
                                                <img src="image/<?php echo $value['image'];?>" alt="<?php echo $value['name']; ?>" />
                                            </label>
                                            <span class="bf-hidden bf-attr-val"><?php echo $valueId; ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <?php else : ?>
                                
                                    <?php foreach ($group['values'] as $value) : ?>
                                    <?php $valueId  = $value['id']; ?>
                                    <div class="bf-attr-filter bf-attr-<?php echo $groupUID; ?> bf-row <?php 
                                    if (isset($totals) && isEmptyStock($groupUID, $valueId, $postponedCount, $totals, $selected) && $settings['behaviour']['hide_empty']):
                                        ?>bf-disabled<?php endif; ?>">
                                        <span class="bf-cell bf-c-1">
                                            <input id="bf-attr-<?php echo $groupUID . '_' . $valueId . '_' . $layout_id; ?>"
                                                   data-filterid="bf-attr-<?php echo $groupUID . '_' . $valueId; ?>"
                                                   type="<?php echo $group['type']; ?>" 
                                                   name="<?php echo "bfp_{$groupUID}"; ?><?php if ($group['type'] === 'checkbox') { echo "_{$valueId}"; } ?>"
                                                   value="<?php echo $valueId; ?>" 
                                                   <?php if (isset($selected[$groupUID]) && in_array($valueId, $selected[$groupUID])) : ?>checked="true"<?php endif; ?> />
                                        </span>
                                        <span class="bf-cell bf-c-2 <?php if ($section['type'] == 'rating') echo 'bf-rating-' . $valueId; ?>">
                                            <span class="bf-hidden bf-attr-val"><?php echo $valueId; ?></span>
                                            <label for="bf-attr-<?php echo $groupUID . '_' . $valueId . '_' . $layout_id; ?>">
                                                <?php if ($section['type'] === 'option') : ?>
                                                    <?php if ($group['mode'] === 'img' || $group['mode'] === 'img_label') : ?>
                                                        <img src="image/<?php echo $value['image'];?>" alt="<?php echo $value['name']; ?>" />
                                                    <?php endif; ?>
                                                    <?php if ($group['mode'] === 'label' || $group['mode'] === 'img_label') : ?>
                                                        <?php echo $value['name']; ?>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <?php echo $value['name']; ?>
                                                <?php endif; ?>
                                            </label>
                                        </span>
                                        <span class="bf-cell bf-c-3"><?php if (isset($totals)) echo totalsDecorator($groupUID, $valueId, $totals, $selected); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                <?php endforeach; ?>
                <?php if (!$isHorizontal || $settings['submission']['submit_type'] == 'button') : ?><div class="bf-buttonclear-box"<?php if ($isHorizontal && $settings['submission']['submit_button_type'] == 'float') : ?>style="display:none;"<?php endif; ?>>
                         <input type="button" value="<?php echo $lang_submit; ?>" class="btn btn-primary bf-buttonsubmit" onclick="BrainyFilter.sendRequest(jQuery(this));BrainyFilter.loadingAnimation();return false;" <?php if ($settings['submission']['submit_button_type'] != 'fix' && $settings['submission']['submit_type'] != 'button' )  : ?>style="display:none;" <?php endif; ?> />
                   <?php if (!$isHorizontal) : ?><input type="reset" class="bf-buttonclear" onclick="BrainyFilter.reset();return false;" value="<?php echo $reset; ?>" /><?php endif; ?>  
                </div><?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script>
var bfLang = {
    show_more : '<?php echo $lang_show_more; ?>',
    show_less : '<?php echo $lang_show_less; ?>',
    empty_list : '<?php echo $lang_empty_list; ?>'
};
BrainyFilter.requestCount = BrainyFilter.requestCount || <?php echo $settings['behaviour']['product_count'] ? 'true' : 'false'; ?>;
BrainyFilter.requestPrice = BrainyFilter.requestPrice || <?php echo $settings['behaviour']['sections']['price']['enabled'] ? 'true' : 'false'; ?>;
BrainyFilter.separateCountRequest = BrainyFilter.separateCountRequest || <?php echo $postponedCount ? 'true' : 'false'; ?>;
BrainyFilter.min = BrainyFilter.min || <?php echo $priceMin; ?>;
BrainyFilter.max = BrainyFilter.max || <?php echo $priceMax; ?>;
BrainyFilter.lowerValue = BrainyFilter.lowerValue || <?php echo $lowerlimit; ?>; 
BrainyFilter.higherValue = BrainyFilter.higherValue || <?php echo $upperlimit; ?>;
BrainyFilter.currencySymb = BrainyFilter.currencySymb || '<?php echo $currency_symbol; ?>';
BrainyFilter.hideEmpty = BrainyFilter.hideEmpty || <?php echo (int)$settings['behaviour']['hide_empty']; ?>;
BrainyFilter.baseUrl = BrainyFilter.baseUrl || "<?php echo $base; ?>";
BrainyFilter.currentRoute = BrainyFilter.currentRoute || "<?php echo $currentRoute; ?>";
BrainyFilter.selectors = BrainyFilter.selectors || {
    'container' : '<?php echo $settings['behaviour']['containerSelector']; ?>',
    'paginator' : '<?php echo $settings['behaviour']['paginatorSelector']; ?>'
};
<?php if ($redirectToUrl) : ?>
BrainyFilter.redirectTo = BrainyFilter.redirectTo || "<?php echo $redirectToUrl; ?>";
<?php endif; ?>
jQuery(function() {
    if (! BrainyFilter.isInitialized) {  
        BrainyFilter.isInitialized = true;
        var def = jQuery.Deferred();
        def.then(function() {
            if('ontouchend' in document && jQuery.ui) {
                jQuery('head').append('<script src="catalog/view/javascript/jquery.ui.touch-punch.min.js"></script' + '>');
            }
        });
        if (typeof jQuery.fn.slider === 'undefined') {
            jQuery.getScript('catalog/view/javascript/jquery-ui.slider.min.js', function(){
                def.resolve();
                jQuery('head').append('<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/jquery-ui.slider.min.css" type="text/css" />');
                BrainyFilter.init();
            });
        } else {
            def.resolve();
            BrainyFilter.init();
        }
    }
});
BrainyFilter.sliderValues = BrainyFilter.sliderValues || {};
<?php if (count($filters)) : ?>
<?php foreach ($filters as $i => $section) : ?>
<?php if (isset($section['array']) && count($section['array'])) : ?>
<?php foreach ($section['array'] as $groupId => $group) : ?>
<?php $groupUID = substr($section['type'], 0, 1) . $groupId; ?>
<?php if (in_array($group['type'], array('slider', 'slider_lbl', 'slider_lbl_inp'))) : ?>
BrainyFilter.sliderValues['<?php echo $groupUID; ?>'] = <?php echo json_encode($group['values']); ?>;
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
</script>
<?php endif; 
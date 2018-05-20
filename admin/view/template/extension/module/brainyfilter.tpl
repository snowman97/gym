<?php echo $header; ?>
<?php echo $column_left; ?>

<div id="content">
    <?php if ($success) : ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (count($error_warning)) : ?>
        <?php foreach ($error_warning as $err) : ?>
            <div class="warning"><?php echo $err; ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="box">
        <div class="heading page-header">
            <div class="container-fluid">
                <h1><?php echo $heading_title; ?></h1>
                <ul class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb) : ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <div class="pull-right">
                    <a onclick="jQuery('[name=action]').val('apply');BF.submitForm();" class="btn btn-success" data-toggle="tooltip" title="<?php echo $lang->button_save; ?>"><i class="fa fa-save"></i></a>
                    <a onclick="BF.submitForm();" class="btn btn-primary" data-toggle="tooltip" title="<?php echo $lang->button_save_n_close; ?>"><span class="icon"></span><i class="fa fa-save"></i></a>
                    <a onclick="location = '<?php echo $cancel; ?>';" class="btn btn-default" data-toggle="tooltip" title="<?php echo $lang->button_cancel; ?>"><i class="fa fa-reply"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- settings block -->
    <form action="<?php echo $action; ?>" method="post" enctype="application/x-www-form-urlencoded" id="form">
        <input type="hidden" name="action" value="save" />
        <input type="hidden" name="bf" value="" />
    </form>
    <form action="" id="bf-form" class="container-fluid">
        <input type="hidden" name="bf[module_id]" value="<?php echo $isNewInstance ? 'new' : $moduleId; ?>" />
        <input type="hidden" name="bf[current_adm_tab]" value="<?php echo $settings['current_adm_tab']; ?>" />
        <!-- main menu -->
        <div id="bf-adm-main-menu">
            <ul class="clearfix">
                <li class="<?php if ($moduleId === 'basic') : ?>selected<?php endif; ?>">
                    <div>
                        <a href="<?php echo $instanceUrl . 'basic'; ?>">
                            <span class="icon basic"></span>
                            <?php echo $lang->top_menu_basic_settings; ?>
                        </a>
                    </div>
                </li>
                <li class="<?php if ($moduleId && $moduleId !== 'basic') : ?>selected<?php endif; ?>">
                    <div class="dropdown">
                        <a id="dLabel" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="icon layouts"></span>
                            <?php echo $lang->top_menu_module_instances; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dLabel">
                            <?php foreach ($modules as $module) : ?>
                            <li>
                                <a href="<?php echo $instanceUrl . $module['module_id']; ?>" <?php if ($moduleId == $module['module_id']) : ?>class="bf-selected"<?php endif; ?>>
                                    <?php echo $module['name']; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?php echo $instanceUrl . 'new'; ?>" <?php if ($moduleId == 'new') : ?>class="bf-selected"<?php endif; ?>>
                                    <i class="fa fa-plus"></i> <?php echo $lang->top_menu_add_new_instance; ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <!-- main menu block end -->
        
        <div id="bf-adm-main-container">
            
            <!-- basic settings container -->
            <div id="bf-adm-basic-settings" class="tab-content" data-group="main" style="display:block">
                <?php if ($moduleId === 'basic') : ?>
                <p class="bf-info"><?php echo $lang->instance_basic_settings_info; ?></p>
                <?php elseif ($moduleId === 'new') : ?>
                <p class="bf-info"><?php echo $lang->instance_new_layout_notice; ?></p>
                <?php endif; ?>
                <div class="bf-panel">
                    <div id="bf-create-instance-alert" class="bf-alert">
                        <?php echo $lang->message_new_instance; ?>
                    </div>
                    <?php if ($moduleId !== 'basic') : ?>
                    <div class="bf-panel-row clearfix">
                        <div class="bf-notice"></div>
                    </div>
                    <?php endif; ?>
                    <div class="tab-content-inner">
                        <?php if ($moduleId !== 'basic') : ?>
                        <div class="bf-panel-row bf-local-settings clearfix">
                            <div class="left">
                                <label for="bf-layout-id"><?php echo $lang->instance_layout; ?></label>
                                <select name="bf[layout_id]" id="bf-layout-id" class="bf-layout-select bf-w195">
                                    <option value="0" selected="selected"><?php echo $lang->select; ?></option>
                                    <?php foreach ($layouts as $id => $layout) : ?>
                                        <option value="<?php echo $id; ?>"><?php echo $layout; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="left">
                                <label for="bf-layout-position"><?php echo $lang->instance_position; ?></label>
                                <select name="bf[layout_position]" id="bf-layout-position" class="bf-layout-position bf-w195">
                                    <option value="content_top"><?php echo $lang->instance_content_top; ?></option>
                                    <option value="content_bottom"><?php echo $lang->instance_content_bottom; ?></option>
                                    <option value="column_left"><?php echo $lang->instance_column_left; ?></option>
                                    <option value="column_right"><?php echo $lang->instance_column_right; ?></option>
                                </select>
                            </div>
                            <div class="left">
                                <label for="bf-layout-sort-order"><?php echo $lang->sort_order; ?></label>
                                <input type="text" name="bf[layout_sort_order]" id="bf-layout-sort-order" class="bf-layout-sort bf-w65" />
                            </div>
                            <div class="left">
                                <span class="bf-label center"><?php echo $lang->enabled; ?></span>
                                <div class="bf-layout-enable yesno">
                                    <span class="bf-switcher">
                                        <input id="bf-layout-off" type="hidden" name="bf[layout_enabled]" value="0" />
                                        <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="left">
                                <span class="bf-label">&nbsp;</span>
                                <a href="<?php echo $removeInstanceAction; ?>" class="bf-remove-layout" onclick="if (!window.confirm(BF.lang.confirm_remove_layout)) return false;"><?php echo $lang->instance_remove; ?></a>
                            </div>

                        </div>
                        <?php endif; ?>
                        <!-- Basic section tabs -->
                        <ul class="tabs vertical clearfix">
                            <?php if ($moduleId !== 'basic') : ?>
                            <li class="tab cat-tab 
                                <?php if (!isset($settings['layout_id']) || !in_array($settings['layout_id'], $category_layouts)) : ?>hidden<?php endif; ?>
                                <?php if ($settings['current_adm_tab'] === 'categories') : ?>selected<?php endif; ?>" data-tab-name="categories" data-target="#bf-categories"><?php echo $lang->tab_categories; ?></li>
                            <?php endif; ?>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'embedding') : ?>selected<?php endif; ?>" data-tab-name="embedding" data-target="#bf-filter-embedding"><?php echo $lang->tab_embedding; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'blocks') : ?>selected<?php endif; ?>" data-tab-name="blocks" data-target="#bf-filter-blocks-display"><?php echo $lang->tab_attributes_display; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'layout') : ?>selected<?php endif; ?>" data-tab-name="layout" data-target="#bf-filter-layout-view"><?php echo $lang->tab_filter_layout_view; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'submission') : ?>selected<?php endif; ?>" data-tab-name="submission" data-target="#bf-data-submission"><?php echo $lang->tab_data_submission; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'attributes') : ?>selected<?php endif; ?>" data-tab-name="attributes" data-target="#bf-attributes"><?php echo $lang->tab_attributes; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'options') : ?>selected<?php endif; ?>" data-tab-name="options" data-target="#bf-options"><?php echo $lang->tab_options; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'filters') : ?>selected<?php endif; ?>" data-tab-name="filters" data-target="#bf-filters"><?php echo $lang->tab_filters; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'responsive') : ?>selected<?php endif; ?>" data-tab-name="responsive" data-target="#bf-responsive"><?php echo $lang->tab_responsive_view; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'style') : ?>selected<?php endif; ?>" data-tab-name="style" data-target="#bf-style"><?php echo $lang->tab_style; ?></li>
                            <?php if ($moduleId === 'basic') : ?>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'global') : ?>selected<?php endif; ?>" data-tab-name="global" data-target="#bf-global-settings"><?php echo $lang->tab_global_settings; ?></li>
                            <?php endif; ?>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'attr_values') : ?>selected<?php endif; ?>" data-tab-name="attr_values" data-target="#bf-attr-values"><?php echo $lang->tab_attr_values; ?></li>
                            <li class="tab <?php if ($settings['current_adm_tab'] === 'help') : ?>selected<?php endif; ?>" data-tab-name="help" data-target="#bf-help"><?php echo $lang->tab_help; ?></li>
                            <li id="refresh-btn-wrapper">
                                <button onclick="BF.refreshDB();return false;" class="bf-button" id="bf-refresh-db">
                                    <span class="icon bf-update"></span><span class="lbl"><?php echo $lang->update_cache; ?></span>
                                </button>
                            </li>
                        </ul>
                        <!-- Containers for Filter Embedding -->
                        <div id="bf-filter-embedding" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->embedding_header; ?></div>
                            <div class="bf-alert" style="display: block;"><?php echo $lang->embedding_warning; ?></div>
                            <p class="bf-info"><?php echo $lang->embedding_description; ?></p>
                            <table class="bf-adm-table">
                                <tr>
                                    <td class="bf-adm-label-td">
                                        <span class="bf-wrapper"><label for="bf-container-selector"><?php echo $lang->embedding_container_selector; ?></label></span>
                                    </td>
                                    <td>
                                        <input style="width: 290px;" type="text" name="bf[behaviour][containerSelector]" value="" id="bf-container-selector" placeholder="<?php echo $basicSettings['behaviour']['containerSelector']; ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td">
                                        <span class="bf-wrapper"><label for="bf-paginator-selector"><?php echo $lang->embedding_paginator_selector; ?></label></span>
                                    </td>
                                    <td>
                                        <input style="width: 290px;" type="text" name="bf[behaviour][paginatorSelector]" value="" id="bf-paginator-selector" placeholder="<?php echo $basicSettings['behaviour']['paginatorSelector']; ?>" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!-- Filter Blocks Display -->
                        <div id="bf-filter-blocks-display" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->filter_blocks_header; ?></div>
                            <p class="bf-info"><?php echo $lang->filter_blocks_descr; ?></p>
                            <table class="bf-adm-table" id="bf-filter-sections">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th class="center"><?php echo $lang->enabled; ?></th>
                                    <th class="bf-collapse-td"><?php echo $lang->collapse; ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($filterBlocks as $filter) : ?>
                                <tr class="bf-sort" data-section="<?php echo $filter['name']; ?>">
                                    <td class="bf-adm-label-td">
                                        <span class="bf-wrapper"><?php echo $filter['label']; ?></span>
                                    </td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-<?php echo $filter['name']; ?>-filter" type="hidden" name="bf[behaviour][sections][<?php echo $filter['name']; ?>][enabled]" value="0" data-disable-adv="section-<?php echo $filter['name']; ?>" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
<!--                                    <td class="center">
                                        <input id="bf-<?php echo $filter['name']; ?>-sort" class="bf-sort-input" type="text" name="bf[behaviour][sort_order][<?php echo $filter['name']; ?>]" />
                                    </td>-->
                                    <td class="center bf-collapse-td">
                                        <input id="bf-<?php echo $filter['name']; ?>-collapse" type="checkbox" name="bf[behaviour][sections][<?php echo $filter['name']; ?>][collapsed]" value="1" data-adv-group="section-<?php echo $filter['name']; ?>" />
                                    </td>
                                    <td>
                                        <?php if (isset($filter['control']) && isset($possible_controls[$filter['name']])) : ?>
                                        <select name="bf[behaviour][sections][<?php echo $filter['name']; ?>][control]" data-adv-group="section-<?php echo $filter['name']; ?>">
                                            <?php foreach ($possible_controls[$filter['name']] as $val => $lbl) : ?>
                                            <option value="<?php echo $val; ?>"><?php echo $lbl; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Filter Layout View -->
                        <div id="bf-filter-layout-view" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->layout_header; ?></div>
                            <div>
                                <table class="bf-adm-table" style="margin-bottom:0;">
                                    <tr>
                                        <th></th>
                                        <th class="center bf-w165"><?php echo $lang->enabled; ?></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td">
                                            <span class="bf-wrapper">
                                            <?php echo $lang->layout_show_attr_groups; ?>
                                            </span>
                                        </td>
                                        <td class="center bf-w165">
                                            <span class="bf-switcher">
                                                <input id="bf-attr-group" type="hidden" name="bf[behaviour][attribute_groups]" value="0" />
                                                <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                            </span>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </table>
                                <table class="bf-adm-table bf-adv-group-cont" style="margin-bottom:0;">
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper">
                                            <?php echo $lang->layout_product_count; ?></span>
                                        </td>
                                        <td class="center bf-w165">
                                            <span class="bf-switcher">
                                                <input id="bf-product-count" type="hidden" name="bf[behaviour][product_count]" value="0" data-disable-adv="hide-empty" />
                                                <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                            </span>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper">
                                            <?php echo $lang->layout_hide_empty_attr; ?></span>
                                        </td>
                                        <td class="center">
                                            <span class="bf-switcher">
                                                <input id="bf-hide-empty" type="hidden" name="bf[behaviour][hide_empty]" value="0" data-adv-group="hide-empty" />
                                                <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                            </span>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </table>
                                <table class="bf-adm-table bf-intersect-cont">
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper">
                                            <?php echo $lang->layout_sliding; ?></span>
                                        </td>
                                        <td class="bf-intersect center bf-w165">
                                            <span class="bf-switcher">
                                                <input id="bf-sliding" type="hidden" name="bf[behaviour][limit_items][enabled]" value="0" data-disable-adv="sliding" />
                                                <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                            </span>
                                        </td>
                                        <td colspan="3" style="padding-top: 5px;padding-bottom: 5px;"> 
                                            <input id="bf-number-to-show" type="text" size="4" name="bf[behaviour][limit_items][number_to_show]" value="" data-adv-group="sliding" />
                                            <label for="bf-number-to-show"><?php echo $lang->layout_sliding_num_to_show; ?></label>
                                            <div class="bf-suboption">
                                                <input id="bf-number-to-hide" type="text" size="4" name="bf[behaviour][limit_items][number_to_hide]" value="" data-adv-group="sliding" /> 
                                                <label for="bf-number-to-hide"><?php echo $lang->layout_sliding_min; ?></label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper">
                                            <?php echo $lang->layout_height_limit; ?></span>
                                        </td>
                                        <td class="bf-intersect center">
                                            <span class="bf-switcher">
                                                <input id="bf-limit-height" type="hidden" name="bf[behaviour][limit_height][enabled]" value="0" data-disable-adv="limit-height" />
                                                <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                            </span>
                                        </td>
                                        <td colspan="3"> 
                                            <input id="bf-limit-height" type="text" size="4" name="bf[behaviour][limit_height][height]" value="" data-adv-group="limit-height" /> 
                                            <label for="bf-limit-height"><?php echo $lang->layout_max_height_limit; ?></label>
                                        </td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                        <!-- Data Submission Tab -->
                        <div id="bf-data-submission" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->submission_header; ?></div>
                            <p class="bf-info"><?php echo $lang->submission_descr; ?></p>
                            <div>
                            <table class="bf-adm-table">
                                <tr>
                                    <th></th>
                                    <th class="center"><?php echo $lang->enabled; ?></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <label for="bf-submit-auto"><?php echo $lang->submission_type_auto; ?></label></span>
                                    </td>
                                    <td class="center">
                                        <input id="bf-submit-auto" type="radio" value="auto" name="bf[submission][submit_type]" data-disable-adv="submit-type" />
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <label for="bf-submit-delay"><?php echo $lang->submission_delay; ?></label></span>
                                    </td>
                                    <td class="center">
                                        <input id="bf-submit-delay" type="radio" value="delay" name="bf[submission][submit_type]" data-disable-adv="submit-type" />
                                    </td>
                                    <td>
                                        <input id="bf-submit-delay-time" type="text" name="bf[submission][submit_delay_time]" value="" size="4" maxlength="4" data-adv-group="submit-type" data-for-val="delay" />
                                        <label for="bf-submit-delay-time"><?php echo $lang->submission_time_in_sec; ?></label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"> 
                                            <label for="bf-submit-btn"><?php echo $lang->submission_type_button; ?></label></span>
                                    </td>
                                    <td class="center"> 
                                        <input id="bf-submit-btn" type="radio" value="button" name="bf[submission][submit_type]" data-disable-adv="submit-type" />
                                    </td>
                                    <td style="padding-top: 5px;padding-bottom: 5px;">
                                        <input id="bf-submit-btn-fixed" type="radio" name="bf[submission][submit_button_type]" value="fix" data-adv-group="submit-type" data-for-val="button" />
                                        <label for="bf-submit-btn-fixed"><?php echo $lang->submission_button_fixed; ?></label>
                                        <div class="bf-suboption">
                                            <input id="bf-submit-btn-float" type="radio" name="bf[submission][submit_button_type]" value="float" data-adv-group="submit-type" data-for-val="button" />
                                            <label for="bf-submit-btn-float"><?php echo $lang->submission_button_float; ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="bf-local-settings">
                                    <td class="bf-adm-label-td">
                                        <span class="bf-wrapper">
                                            <label for="bf-submit-default"><?php echo $lang->submission_type_default; ?></label>
                                        </span>
                                    </td>
                                    <td class="center">
                                        <input id="bf-submit-default" type="radio" value="default" name="bf[submission][submit_type]" data-disable-adv="submit-type" class="bf-default" />
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <?php echo $lang->submission_hide_panel; ?></span>
                                    </td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-hide-layout" type="hidden" name="bf[submission][hide_panel]" value="0" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                        </div>
                        <!-- Categories -->
                        <?php if ($moduleId !== 'basic') : ?>
                        <div id="bf-categories" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->categories_header; ?></div>
                            <p class="bf-info"><?php echo $lang->categories_info; ?></p>
                            <div class="bf-multi-select-group">
                                <p class="bf-green-info"><?php echo $lang->categories_list_of_enabled; ?></p>
                                <div class="bf-gray-panel">
                                    <?php echo $lang->categories_filter_hint; ?>
                                    <input type="text" class="bf-cat-filter bf-full-width" data-target="#bf-enabled-categories" />
                                </div>
                                <div style="padding-left:15px;">
                                    <a data-select-all="#bf-enabled-categories"><?php echo $lang->categories_select_all; ?></a> /
                                    <a data-unselect-all="#bf-enabled-categories"><?php echo $lang->categories_unselect_all; ?></a>
                                </div>
                                <div id="bf-enabled-categories" class="bf-multi-select">
                                    <?php foreach ($categories as $category) : ?>
                                    <?php if (!isset($settings['categories']) || !isset($settings['categories'][$category['category_id']])) : ?>
                                    <div class="bf-row">
                                        <input type="hidden" name="bf[categories][<?php echo $category['category_id']; ?>]" value="1" />
                                        <?php echo $category['name']; ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="bf-middle-buttons-col">
                                <?php echo $lang->categories_move_selected; ?>
                                <br><button class="bf-move-right"></button><br><button class="bf-move-left"></button><br>
                            </div>
                            <div class="bf-multi-select-group">
                                <p class="bf-red-info"><?php echo $lang->categories_list_of_disabled; ?></p>
                                <div class="bf-gray-panel">
                                    <?php echo $lang->categories_filter_hint; ?>
                                    <input type="text" class="bf-cat-filter bf-full-width" data-target="#bf-disabled-categories" />
                                </div>
                                <div style="padding-left:15px;">
                                    <a data-select-all="#bf-disabled-categories"><?php echo $lang->categories_select_all; ?></a> /
                                    <a data-unselect-all="#bf-disabled-categories"><?php echo $lang->categories_unselect_all; ?></a>
                                </div>
                                <div id="bf-disabled-categories" class="bf-multi-select">
                                    <?php if (isset($settings['categories'])) : ?>
                                    <?php foreach ($settings['categories'] as $catId => $b) : ?>
                                    <div class="bf-row">
                                        <input type="hidden" name="bf[categories][<?php echo $categories[$catId]['category_id']; ?>]" value="1" />
                                        <?php echo $categories[$catId]['name']; ?>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <?php endif; ?>
                        <!-- Attributes Tab -->
                        <div id="bf-attributes" class="tab-content with-border" data-group="settings" data-select-all-group="attributes">
                            <div class="bf-th-header-static"><?php echo $lang->attributes_header; ?></div>
                            <div>
                                <table class="bf-adm-table" data-select-all-group="attributes">
                                    <tr>
                                        <th></th>
                                        <th class="center"><?php echo $lang->enabled; ?></th>
                                        <th class="bf-w165"><?php echo $lang->control; ?></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->attributes_group_setting; ?></span></td>
                                        <td class="center">
                                            <span class="bf-switcher">
                                                <input id="bf-layout-off" type="hidden" name="bf[attributes_default][enable_all]" value="0" data-disable-adv="group-attr-control" />
                                                <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                            </span>
                                        </td>
                                        <td>
                                            <select name="bf[attributes_default][control]" data-adv-group="group-attr-control">
                                                <option value="checkbox"><?php echo $lang->checkbox; ?></option>
                                                <option value="radio"><?php echo $lang->radio; ?></option>
                                                <option value="select"><?php echo $lang->selectbox; ?></option>
                                                <option value="slider"><?php echo $lang->slider; ?></option>
                                                <option value="slider_lbl"><?php echo $lang->slider_labels_only; ?></option>
                                                <option value="slider_lbl_inp"><?php echo $lang->slider_labels_and_inputs; ?></option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->attributes_individual_set; ?></div>
                            <div>
                                <div class="bf-gray-panel">
                                    <div class="bf-half">
                                        <?php echo sprintf($lang->autocomplete_hint, $lang->btn_select_attribute); ?>
                                        <input type="text" id="bf-attr-search" class="bf-autocomplete" data-lookup="attributes" />
                                        <button id="bf-attr-add" class="btn btn-success bf-add-row" data-filter-data="" data-row-tpl="#custom-attr-setting-template" data-target-tbl="#custom-attr-settings">
                                            <i class="fa fa-plus"></i> <?php echo $lang->btn_select_attribute; ?>
                                        </button>
                                    </div>
                                    <div class="bf-half">
                                        <p class="bf-info"><?php echo $lang->attributes_custom_set_descr; ?></p>
                                    </div>
                                </div>
                                <table class="bf-adm-table bf-hide-if-empty" data-select-all-group="attributes">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="center">
                                                <?php echo $lang->enabled; ?>
                                                <div class="bf-group-actions">
                                                    <a data-select-all="attributes" data-select-all-val="2" class="bf-local-settings"><?php echo $lang->set_all_default; ?></a>
                                                    <span class="bf-local-settings">/</span>
                                                    <a data-select-all="attributes" data-select-all-val="0"><?php echo $lang->disable_all; ?></a>
                                                    <span>/</span>
                                                    <a data-select-all="attributes" data-select-all-val="1"><?php echo $lang->enable_all; ?></a>
                                                </div> 
                                            </th>
                                            <th class="bf-w165"><?php echo $lang->control; ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="custom-attr-settings">
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Options Tab -->
                        <div id="bf-options" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->options_header; ?></div>
                            <table class="bf-adm-table" data-select-all-group="attributes">
                                <tr>
                                    <th></th>
                                    <th class="center bf-w165"><?php echo $lang->enabled; ?></th>
                                    <th class="bf-w165"><?php echo $lang->control; ?></th>
                                    <th class="bf-w165"><?php echo $lang->options_view_mode; ?></th>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->options_group_setting; ?></span></td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-layout-off" type="hidden" name="bf[options_default][enable_all]" value="0" data-disable-adv="group-opt-control" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="bf[options_default][control]" data-adv-group="group-opt-control" class="bf-w165">
                                            <option value="checkbox"><?php echo $lang->checkbox; ?></option>
                                            <option value="radio"><?php echo $lang->radio; ?></option>
                                            <option value="select"><?php echo $lang->selectbox; ?></option>
                                            <option value="slider"><?php echo $lang->slider; ?></option>
                                            <option value="slider_lbl"><?php echo $lang->slider_labels_only; ?></option>
                                            <option value="slider_lbl_inp"><?php echo $lang->slider_labels_and_inputs; ?></option>
                                        </select>
                                    </td>
                                    <td class="center">
                                        <select name="bf[options_default][mode]" class="bf-opt-mode bf-w135" data-adv-group="group-opt-control" data-bf-role="mode">
                                            <option value="label"><?php echo $lang->options_view_mode_label; ?></option>
                                            <option value="img_label"><?php echo $lang->options_view_mode_image_and_label; ?></option>
                                            <option value="img"><?php echo $lang->options_view_mode_image; ?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->options_individual_set; ?></div>
                            <div>
                                <div class="bf-gray-panel">
                                    <div class="bf-half">
                                        <?php echo sprintf($lang->autocomplete_hint, $lang->btn_select_option); ?>
                                        <input type="text" id="bf-attr-search" class="bf-w190 bf-autocomplete" data-lookup="options" />
                                        <button id="bf-attr-add" class="btn btn-success bf-add-row" data-filter-data="" data-row-tpl="#custom-opt-setting-template" data-target-tbl="#custom-opt-settings">
                                            <i class="fa fa-plus"></i> <?php echo $lang->btn_select_option; ?>
                                        </button>
                                    </div>
                                    <div class="bf-half">
                                        <p class="bf-info"><?php echo $lang->options_custom_set_descr; ?></p>
                                    </div>
                                </div>
                                <table class="bf-adm-table bf-hide-if-empty" data-select-all-group="options">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="center bf-w165">
                                                <?php echo $lang->enabled; ?>
                                                <div class="bf-group-actions">
                                                    <a data-select-all="options" data-select-all-val="2" class="bf-local-settings"><?php echo $lang->set_all_default; ?></a>
                                                    <span class="bf-local-settings">/</span>
                                                    <a data-select-all="options" data-select-all-val="0"><?php echo $lang->disable_all; ?></a>
                                                    <span>/</span>
                                                    <a data-select-all="options" data-select-all-val="1"><?php echo $lang->enable_all; ?></a>
                                                </div> 
                                            </th>
                                            <th class="bf-w165"><?php echo $lang->control; ?></th>
                                            <th class="bf-w165"><?php echo $lang->options_view_mode; ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="custom-opt-settings">
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Filters Tab -->
                        <div id="bf-filters" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->filters_header; ?></div>
                            <table class="bf-adm-table" data-select-all-group="filters">
                                <tr>
                                    <th></th>
                                    <th class="center"><?php echo $lang->enabled; ?></th>
                                    <th class="bf-w165"><?php echo $lang->control; ?></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->filters_group_setting; ?></span></td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-layout-off" type="hidden" name="bf[filters_default][enable_all]" value="0" data-disable-adv="group-filter-control" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="bf[filters_default][control]" data-adv-group="group-filter-control">
                                            <option value="checkbox"><?php echo $lang->checkbox; ?></option>
                                            <option value="radio"><?php echo $lang->radio; ?></option>
                                            <option value="select"><?php echo $lang->selectbox; ?></option>
                                            <option value="slider"><?php echo $lang->slider; ?></option>
                                            <option value="slider_lbl"><?php echo $lang->slider_labels_only; ?></option>
                                            <option value="slider_lbl_inp"><?php echo $lang->slider_labels_and_inputs; ?></option>
                                        </select>
                                    </td>
                                    <td></td>
                                </tr>
                            </table>
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->filters_individual_set; ?></div>
                            <div>
                                <div class="bf-gray-panel">
                                    <div class="bf-half">
                                        <?php echo sprintf($lang->autocomplete_hint, $lang->btn_select_filter); ?>
                                        <input type="text" id="bf-attr-search" class="bf-w190 bf-autocomplete" data-lookup="filters" />
                                        <button id="bf-attr-add" class="btn btn-success bf-add-row" data-filter-data="" data-row-tpl="#custom-filter-setting-template" data-target-tbl="#custom-filter-settings">
                                            <i class="fa fa-plus"></i> <?php echo $lang->btn_select_filter; ?>
                                        </button>
                                    </div>
                                    <div class="bf-half">
                                        <p class="bf-info"><?php echo $lang->filters_custom_set_descr; ?></p>
                                    </div>
                                </div>
                                <table class="bf-adm-table bf-hide-if-empty" data-select-all-group="filters">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="center">
                                                <?php echo $lang->enabled; ?>
                                                <div class="bf-group-actions">
                                                    <a data-select-all="filters" data-select-all-val="2" class="bf-local-settings"><?php echo $lang->set_all_default; ?></a>
                                                    <span class="bf-local-settings">/</span>
                                                    <a data-select-all="filters" data-select-all-val="0"><?php echo $lang->disable_all; ?></a>
                                                    <span>/</span>
                                                    <a data-select-all="filters" data-select-all-val="1"><?php echo $lang->enable_all; ?></a>
                                                </div> 
                                            </th>
                                            <th class="bf-w165"><?php echo $lang->control; ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="custom-filter-settings">
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Responsive View -->
                        <div id="bf-responsive" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->responsive_header; ?></div>
                            <table class="bf-adm-table bf-adv-group-cont">
                                <tr>
                                    <th></th>
                                    <th class="bf-w170"></th>
                                    <th></th>
                                    <th><span class="bf-local-settings"><?php echo $lang->default; ?></span></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->responsive_mode_enable; ?></span></td> 
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-responsive" type="hidden" name="bf[style][responsive][enabled]" value="0" data-disable-adv="responsive" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->responsive_collapse_sections; ?></span></td> 
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-responsive-collapse" type="hidden" name="bf[style][responsive][collapsed]" value="0" data-adv-group="responsive" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->responsive_max_width; ?></span></td> 
                                    <td class="center">
                                        <input type="text" name="bf[style][responsive][max_width]" value="" data-adv-group="responsive" class="bf-w65" />
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->responsive_max_screen_width; ?></span></td> 
                                    <td class="center">
                                        <input type="text" name="bf[style][responsive][max_screen_width]" value="" data-adv-group="responsive" class="bf-w65" />
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->responsive_position; ?></span></td> 
                                    <td class="center">
                                        <input id="bf-responsive-position-left" type="radio" name="bf[style][responsive][position]" value="left" data-adv-group="responsive" />
                                        <label for="bf-responsive-position-left"><?php echo $lang->left; ?></label>
                                        <input id="bf-responsive-position-right" type="radio" name="bf[style][responsive][position]" value="right" data-adv-group="responsive" />
                                        <label for="bf-responsive-position-right"><?php echo $lang->right; ?></label>
                                    </td>
                                    <td>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->responsive_offset; ?></span></td> 
                                    <td class="center">
                                        <input id="bf-responsive-offset" type="text" name="bf[style][responsive][offset]" value="" data-adv-group="responsive" class="bf-w65" />
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                        <!-- Theme Tab -->
                         <div id="bf-style" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->theme_block_header; ?></div>
                            <div>
                                <table class="bf-adm-table">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th><span class="bf-local-settings"><?php echo $lang->default; ?></span></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_background; ?></span></td>
                                        <td class="center bf-w170">
                                            <input id="bf-style-block-header-background" class="bf-w165 entry color-pick" type="text" name="bf[style][block_header_background][val]" />
                                        </td>
                                        <td class="bf-w65 center"><input type="checkbox" name="bf[style][block_header_background][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("block_header_background", this)}'/></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_text; ?></span></td>
                                        <td class="center bf-w170">
                                            <input id="bf-style-block-header-text" class="bf-w165 entry color-pick" type="text" name="bf[style][block_header_text][val]" /> 
                                        </td>
                                        <td class="bf-w65 center"><input type="checkbox" name="bf[style][block_header_text][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("block_header_text", this)}'/></td>
                                        <td></td>
                                    </tr>
                                    <?php foreach ($languages as $language) : ?>
                                    <tr>
                                        <td class="bf-adm-label-td">
                                            <span class="bf-wrapper">
                                                <?php echo $lang->theme_title; ?> (
                                                <img src="<?php echo $language['image_path']; ?>" /> 
                                                <?php echo $language['name']; ?>)
                                            </span>
                                        </td>
                                        <td colspan="2">
                                            <input type="text" name="bf[behaviour][filter_name][<?php echo $language['language_id']; ?>]" value="" class="bf-w195" />
                                        </td>
                                        <td></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->theme_product_quantity; ?></div>
                            <div>
                            <table class="bf-adm-table">
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th><span class="bf-local-settings"><?php echo $lang->default; ?></span></th>
                                    <th></th>
                                </tr>
                                 <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_background; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-product-quantity-background" class="bf-w165 entry color-pick" type="text" name="bf[style][product_quantity_background][val]"/> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][product_quantity_background][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("product_quantity_background", this)}'/></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_text; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-product-quantity-text" class="bf-w165 entry color-pick" type="text" name="bf[style][product_quantity_text][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][product_quantity_text][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("product_quantity_text", this)}' /></td>
                                    <td></td>
                                </tr>
                                </table>
                                </div>
                                <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->theme_price_slider; ?></div>
                            <div>
                            <table class="bf-adm-table">
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th><span class="bf-local-settings"><?php echo $lang->default; ?></span></th>
                                    <th></th>
                                </tr>
                                 <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_background; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-price-slider-background" class="bf-w165 entry color-pick" type="text" name="bf[style][price_slider_background][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][price_slider_background][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("price_slider_background", this)}' /></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_active_area_background; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-price-slider-area-background" class="bf-w165 entry color-pick" type="text" name="bf[style][price_slider_area_background][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][price_slider_area_background][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("price_slider_area_background", this)}' /></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_border; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-price-slider-border" class="bf-w165 entry color-pick" type="text" name="bf[style][price_slider_border][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][price_slider_border][default]" value="1" class="bf-chkbox-def bf-local-settings"  onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("price_slider_border", this)}'/></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_slider_handle_background; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-price-slider-handle-background" class="bf-w165 entry color-pick" type="text" name="bf[style][price_slider_handle_background][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][price_slider_handle_background][default]" value="1" class="bf-chkbox-def bf-local-settings"  onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("price_slider_handle_background", this)}'/></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_slider_handle_border; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-price-slider-handle-border" class="bf-w165 entry color-pick" type="text" name="bf[style][price_slider_handle_border][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][price_slider_handle_border][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("price_slider_handle_border", this)}'/></td>
                                    <td></td>
                                </tr>

                            </table>
                            </div>
                             <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->theme_group_block_header; ?></div>
                            <div>
                            <table class="bf-adm-table">
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th><span class="bf-local-settings"><?php echo $lang->default; ?></span></th>
                                    <th></th>
                                </tr>
                                 <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_background; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-group-block-header-background" class="bf-w165 entry color-pick" type="text" name="bf[style][group_block_header_background][val]" /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][group_block_header_background][default]" value="1" class="bf-chkbox-def bf-local-settings"  onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("group_block_header_background", this)}'/></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_text; ?></span></td>
                                    <td class="center bf-w170">
                                        <input id="bf-style-group-block-header-text" class="bf-w165 entry color-pick" type="text" name="bf[style][group_block_header_text][val]"  /> 
                                    </td>
                                    <td class="bf-w65 center"><input type="checkbox" name="bf[style][group_block_header_text][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange = 'if (jQuery(this).is(":checked")) {BF.changeDefault("group_block_header_text", this)}' /></td>
                                    <td></td>
                                </tr>
                                </table>
                            </div>
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->theme_responsive_popup_view; ?></div>
                            <div>
                                <table class="bf-adm-table">
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_show_btn_color; ?></span></td>
                                        <td class="center bf-w170">
                                            <input class="bf-w165 entry color-pick" type="text" name="bf[style][resp_show_btn_color][val]" />
                                        </td>
                                        <td class="bf-w65 center"><input type="checkbox" name="bf[style][resp_show_btn_color][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange="if (jQuery(this).is(':checked')) {BF.changeDefault('resp_show_btn_color', this);}" /></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->theme_reset_btn_color; ?></span></td>
                                        <td class="center bf-w170">
                                            <input class="bf-w165 entry color-pick" type="text" name="bf[style][resp_reset_btn_color][val]" />
                                        </td>
                                        <td class="bf-w65 center"><input type="checkbox" name="bf[style][resp_reset_btn_color][default]" value="1" class="bf-chkbox-def bf-local-settings" onchange="if (jQuery(this).is(':checked')) {BF.changeDefault('resp_reset_btn_color', this);}" /></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <!-- Attribute Values Ordering -->
                        <div id="bf-attr-values" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->ordering_header; ?></div>
                            <div class="bf-alert" style="display: block;"><?php echo $lang->ordering_note; ?></div>
                            <p class="bf-info"><?php echo $lang->ordering_descr; ?></p>
                            <div class="bf-multi-select-group">
                                <div class="bf-gray-panel">
                                    <?php echo $lang->ordering_filter_hint; ?>
                                    <input type="text" class="bf-attr-filter bf-full-width" data-target="#bf-attr-list" />
                                </div>
                                <div id="bf-attr-list" class="bf-multi-select">
                                    <?php foreach ($attributes as $attrId => $attribute) : ?>
                                    <div class="bf-row" data-attr-id="<?php echo $attrId; ?>">
                                        <b><?php echo $attribute['group']; ?></b> / <?php echo $attribute['name']; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="bf-middle-buttons-col">
                            </div>
                            <div class="bf-multi-select-group">
                                <div class="bf-gray-panel">
                                    <div class="buttons">
                                        <div><?php echo $lang->ordering_language; ?>:</div>
                                        <select id="bf-attr-val-language" class="bf-w165">
                                            <?php foreach ($languages as $i => $language) : ?>
                                            <option value="<?php echo $language['language_id']; ?>"><?php echo $language['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="bf-pull-right">
                                            <button class="bf-auto-sort" data-type="number">0..9</button>
                                            <button class="bf-auto-sort" data-type="string">A..Z</button>
                                            <a class="bf-button bf-save-btn" title="<?php echo $lang->button_save_n_close; ?>"><span class="icon"></span></a>
                                        </div>
                                    </div>
                                </div>
                                <div id="bf-attr-val-list" class="bf-multi-select">
                                    
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <!-- Global Settings -->
                        <div id="bf-global-settings" class="tab-content with-border bf-global-settings" data-group="settings">
                            <div class="bf-th-header-static"><?php echo $lang->global_header; ?></div>
                            <p class="bf-info"><?php echo $lang->global_settings_descr; ?></p>
                            <table class="bf-adm-table">
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->global_hide_empty_stock; ?></span></td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-hide-out-of-stock" type="hidden" name="bf[global][hide_out_of_stock]" value="0" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="bf-global-settings">
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <?php echo $lang->global_postponed_count; ?></span>
                                    </td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-postponed-count" type="hidden" name="bf[global][postponed_count]" value="0" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper"><?php echo $lang->global_enable_multiple_attributes; ?></span></td>
                                    <td class="center">
                                        <span class="bf-switcher">
                                            <input id="bf-multiple-attributes" type="hidden" name="bf[global][multiple_attributes]" value="0" data-disable-adv="attr-separator" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td>
                                        <label for="bf-attr-separator"><?php echo $lang->global_separator; ?></label>
                                        <input id="bf-attr-separator" type="text" name="bf[global][attribute_separator]" value="" size="4" data-adv-group="attr-separator" />
                                        <?php echo $lang->global_multiple_attr_separator; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <?php echo $lang->global_in_stock_status_id; ?></span>
                                    </td>
                                    <td class="" colspan="2">
                                        <select name="bf[global][instock_status_id]" class="bf-w165">
                                            <?php foreach ($stockStatuses as $status) : ?>
                                                    <option value="<?php echo $status['stock_status_id']; ?>"><?php echo $status['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <?php echo $lang->global_subcats_fix; ?></span>
                                    </td>
                                    <td class=" center">
                                        <span class="bf-switcher">
                                            <input id="bf-subcategories" type="hidden" name="bf[global][subcategories_fix]" value="0" />
                                            <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                                        </span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bf-adm-label-td"><span class="bf-wrapper">
                                        <?php echo $lang->entry_cron_secret_key; ?>
                                            <span class="bf-link-highlight" style="padding-top:5px;"><?php echo $catalogUrl; ?>index.php?route=module/brainyfilter/cron&key=<b>cron secret key</b></span>
                                        </span>
                                    </td>
                                    <td>
                                        <input id="bf-cron-key" class="bf-w165" type="text" name="bf[global][cron_secret_key]" value="" />
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                    </td>
                                </tr>
                            </table>                    
                        </div>
                        <!-- FAQ container -->
                        <div id="bf-help" class="tab-content with-border" data-group="settings">
                            <div class="bf-th-header" id="bf-faq-n-troubleshooting"><span class="icon bf-arrow"></span><?php echo $lang->help_faq_n_trouleshooting; ?></div>
                            <div style="display:none;">
                                
                            </div>
                            <div class="bf-th-header expanded"><span class="icon bf-arrow"></span><?php echo $lang->help_about; ?></div>
                            <div id="bf-about">
                                <div class="bf-about-text">
                                    <?php echo $lang->help_about_content; ?>
                                    <hr />
                                    <p><?php echo $lang->bf_signature; ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="bf-signature"><?php echo $lang->bf_signature; ?></div>
    <!--                -->
</div>

<table style="display:none;">
    <tbody id="custom-attr-setting-template">
        <tr>
            <td class="bf-adm-label-td"><span class="bf-wrapper bf-attr-name" data-bf-role="name"></span></td>
            <td class="center">
                <span class="bf-switcher">
                    <input class="bf-attr-enable" type="hidden" name="bf[attributes][{i}][enabled]" value="0" data-disable-adv="attr-control-{i}" data-bf-role="enabled" />
                    <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                </span>
            </td>
            <td><select name="bf[attributes][{i}][control]" data-adv-group="attr-control-{i}" data-bf-role="control">
                    <option value="checkbox"><?php echo $lang->checkbox; ?></option>
                    <option value="radio"><?php echo $lang->radio; ?></option>
                    <option value="select"><?php echo $lang->selectbox; ?></option>
                    <option value="slider"><?php echo $lang->slider; ?></option>
                    <option value="slider_lbl"><?php echo $lang->slider_labels_only; ?></option>
                    <option value="slider_lbl_inp"><?php echo $lang->slider_labels_and_inputs; ?></option>
                </select></td>
            <td><a class="bf-remove-row"><i class="fa fa-times"></i></a></td>
        </tr>
    </tbody>
</table>
<table style="display:none;">
    <tbody id="custom-opt-setting-template">
        <tr>
            <td class="bf-adm-label-td"><span class="bf-wrapper bf-attr-name" data-bf-role="name"></span></td>
            <td class="center">
                <span class="bf-switcher">
                    <input class="bf-attr-enable" type="hidden" name="bf[options][{i}][enabled]" value="0" data-disable-adv="opt-control-{i}" data-bf-role="enabled" />
                    <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                </span>
            </td>
            <td class="center">
                <select name="bf[options][{i}][control]" data-adv-group="opt-control-{i}" data-bf-role="control" class="bf-w135">
                    <option value="checkbox"><?php echo $lang->checkbox; ?></option>
                    <option value="radio"><?php echo $lang->radio; ?></option>
                    <option value="select"><?php echo $lang->selectbox; ?></option>
                    <option value="slider"><?php echo $lang->slider; ?></option>
                    <option value="slider_lbl"><?php echo $lang->slider_labels_only; ?></option>
                    <option value="slider_lbl_inp"><?php echo $lang->slider_labels_and_inputs; ?></option>
                    <option value="grid"><?php echo $lang->grid_of_images; ?></option>
                </select>
            </td>
            <td class="center">
                <select name="bf[options][{i}][mode]" class="bf-opt-mode bf-w135" data-adv-group="opt-control-{i}" data-bf-role="mode">
                    <option value="label"><?php echo $lang->options_view_mode_label; ?></option>
                    <option value="img_label"><?php echo $lang->options_view_mode_image_and_label; ?></option>
                    <option value="img"><?php echo $lang->options_view_mode_image; ?></option>
                </select>
            </td>
            <td><a class="bf-remove-row"><i class="fa fa-times"></i></a></td>
        </tr>
    </tbody>
</table>
<table style="display:none;">
    <tbody id="custom-filter-setting-template">
        <tr>
            <td class="bf-adm-label-td"><span class="bf-wrapper bf-attr-name" data-bf-role="name"></span></td>
            <td class="center">
                <span class="bf-switcher">
                    <input class="bf-attr-enable" type="hidden" name="bf[filters][{i}][enabled]" value="0" data-disable-adv="filter-control-{i}" data-bf-role="enabled" />
                    <span class="bf-def"></span><span class="bf-no"></span><span class="bf-yes"></span>
                </span>
            </td>
            <td><select name="bf[filters][{i}][control]" data-adv-group="filter-control-{i}" data-bf-role="control">
                    <option value="checkbox"><?php echo $lang->checkbox; ?></option>
                    <option value="radio"><?php echo $lang->radio; ?></option>
                    <option value="select"><?php echo $lang->selectbox; ?></option>
                    <option value="slider"><?php echo $lang->slider; ?></option>
                    <option value="slider_lbl"><?php echo $lang->slider_labels_only; ?></option>
                    <option value="slider_lbl_inp"><?php echo $lang->slider_labels_and_inputs; ?></option>
                </select></td>
            <td><a class="bf-remove-row"><i class="fa fa-times"></i></a></td>
        </tr>
    </tbody>
</table>

<!-- Category list template -->

<div id="bf-category-list-tpl" class="bf-category-list" style="display: none;">
    <div class="bf-label">
        <?php echo $lang->categories; ?>
        (<a onclick="jQuery(this).closest('.bf-category-list').find('input').removeAttr('checked')"><?php echo $lang->disable_all; ?></a>
        <span>/</span>
        <a onclick="jQuery(this).closest('.bf-category-list').find('input').attr('checked', 'checked')"><?php echo $lang->enable_all; ?></a>)
    </div>
    <div class="bf-cat-list-cont">
        <ul data-select-all-group="categories">
            <?php foreach ($categories as $cat) : ?>
            <li>
                <label>
                    <input type="checkbox" name="bf[categories][<?php echo $cat['category_id']; ?>]" value="1" />
                    <?php echo $cat['name']; ?>
                </label>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- End of Category list template -->
<script>
BF.lang = {
        'default' : '<?php echo $lang->default; ?>',
        'error_layout_not_set' : '<?php echo $lang->message_error_layout_not_set; ?>',
        'error_cant_remove_default' : '<?php echo $lang->instance_remove_default_layout; ?>',
        'default_layout' : '<?php echo $lang->instance_default_layout; ?>',
        'confirm_remove_layout' : '<?php echo $lang->instance_remove_confirmation; ?>',
        'confirm_unsaved_changes' : '<?php echo $lang->message_unsaved_changes; ?>',
        'updating' : '<?php echo $lang->updating; ?>',
        'empty_table' : '<?php echo $lang->message_empty_table; ?>',
        'content_top' : '<?php echo $lang->instance_content_top; ?>',
        'column_left' : '<?php echo $lang->instance_column_left; ?>',
        'column_right' : '<?php echo $lang->instance_column_right; ?>',
        'content_bottom' : '<?php echo $lang->instance_content_bottom; ?>'
    };
BF.moduleId = '<?php echo $isNewInstance ? 'new' : $moduleId; ?>';
BF.settings = <?php echo json_encode($settings); ?>;
BF.attributes = <?php echo json_encode($attributes); ?>;
BF.options = <?php echo json_encode($options); ?>;
BF.filters = <?php echo json_encode($filters); ?>;
BF.refreshActionUrl = '<?php echo str_replace('&amp;', '&', $refreshAction); ?>';
BF.modRefreshActionUrl = '<?php echo str_replace('&amp;', '&', $modRefreshAction); ?>';
BF.attrValActionUrl = '<?php echo str_replace('&amp;', '&', $attributeValuesAction); ?>';
BF.isFirstLaunch = <?php echo $isFirstLaunch; ?>;
BF.categoryLayouts = <?php echo json_encode($category_layouts); ?>;
jQuery(document).ready(BF.init());
</script>

<style>
    .bf-def:before {
        content: '<?php echo $lang->default; ?>';
    }
    .bf-no:before {
        content: '<?php echo $lang->no; ?>';
    }
    .bf-yes:before {
        content: '<?php echo $lang->yes; ?>';
    }
    .bf-disable-enable .bf-no:before {
        content: '<?php echo $lang->disable_all; ?>';
    }
    .bf-disable-enable .bf-yes:before {
        content: '<?php echo $lang->enable_all; ?>';
    }
</style>
<?php echo $footer;
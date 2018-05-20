<?php

$_['heading_title']                         = 'Brainy Filter'; 
$_['bf_signature']	                        = "Brainy Filter Ultimate 5.1.2 OC2.3"; 
$_['button_save_n_close']                   = 'Save & Close'; 

// Top Menu
$_['top_menu_basic_settings']               = 'Basic settings<span class="help">Settings used by all instances</span>';
$_['top_menu_module_instances']             = 'Module Instances<span class="help">Layouts & settings ovemides</span>';
$_['top_menu_add_new_instance']             = 'Add new instance'; 

// Tabs
$_['tab_embedding']                         = 'Embedding';
$_['tab_attributes_display']                = 'Filter Blocks Display';
$_['tab_filter_layout_view']                = 'Filter Layout View';
$_['tab_data_submission']                   = 'Data Submission';
$_['tab_attributes']                        = 'Attributes';
$_['tab_options']                           = 'Options';
$_['tab_filters']                           = 'Filters';
$_['tab_categories']                        = 'Categories';
$_['tab_responsive_view']                   = 'Responsive View';
$_['tab_style']                             = 'Theme';
$_['tab_attr_values']                       = 'Values Ordering';
$_['tab_global_settings']                   = 'Global Settings'; 
$_['tab_help']                              = 'Help'; 

// Containers for Filter Embedding
$_['embedding_header']                      = 'Containers for Filter Embedding';
$_['embedding_warning']                     = 'These settings are crucial for the filter to operate.';
$_['embedding_container_selector']          = '<b>Product List Container</b><span class="help">CSS selector of the DOM element which contains the product list <b>only</b></span>';
$_['embedding_paginator_selector']          = '<b>Pagination Panel</b><span class="help">CSS selector of the pagination panel</span>';
$_['embedding_description']                 = 'These settings are used by the filter to embed a resulting list of products into the products page. If these settings are incorrect the filter will not be able to show the filtered list. The filter can automatically define the settings for many popular teams but sometimes you may need to do that manually by describing CSS selectors for the product list container and the pagination panel container.'; 
$_['entry_cron_secret_key']                 = '<b>CRON secret key</b><span class="help">The key is used in order to protect CRON access point from unauthorized requests. Note that the access point isn\'t accessible if the field is empty.<br> CRON URL will be:</span>';

// Filter Blocks Display
$_['filter_blocks_header']                  = 'Filter Blocks Display';
$_['filter_blocks_descr']                   = 'Choose preferable blocks which you want to see on the filter layout. <br/><b>Sort order</b> of the blocks is changed by dragging the rows';
$_['filter_search']                         = 'Keywords filter';
$_['filter_price']                          = 'Price filter';
$_['filter_category']                       = 'Category filter';
$_['filter_manufacturer']                   = 'Manufacturer filter'; 
$_['filter_stock_status']                   = 'Stock status filter';
$_['filter_attribute']                      = 'Attribute filter';
$_['filter_filter']                         = 'OpenCart filters';
$_['filter_option']                         = 'Option filter';
$_['filter_rating']                         = 'Rating filter'; 

// Filter Layout View
$_['layout_header']                         = 'Filter Layout View';
$_['layout_show_attr_groups']               = 'Show attribute groups';
$_['layout_product_count']                  = 'Enable product counts';
$_['layout_hide_empty_attr']                = 'Hide empty attribute values<span class="help">Note: the feature is only available if the Product counts setting is enabled</span>';
$_['layout_sliding']                        = 'Limit number of attributes shown by default';
$_['layout_sliding_num_to_show']            = 'Number of attributes to show';
$_['layout_sliding_min']                    = 'Minimum number of attributes to hide';
$_['layout_height_limit']                   = 'Limit blocks height';
$_['layout_max_height_limit']               = 'Maximum height'; 

// Data Submission
$_['submission_header']                     = 'Data Submission';
$_['submission_descr']                      = 'Parameters defining what action is required to apply the filter settings to the current list of products.';
$_['submission_type_default']               = '<b>Use default</b><span class="help">Setting from Basic Settings will be used</span>';
$_['submission_type_button']                = '<b>The apply button is pressed</b>';
$_['submission_type_auto']                  = '<b>Any filter setting change</b> <span class="help">Apply the filter once a user changes any of the filter settings.</span>';
$_['submission_delay']                      = '<b>Several filter settings change</b> <span class="help">Apply the filter once a user stops changing settings. Stopping means no actions within a certain predefined time period.</span>';
$_['submission_time_in_sec']                = 'Time in milliseconds'; 
$_['submission_hide_panel']                 = '<b>Fade out the filter panel during requests</b>';
$_['submission_button_fixed']               = 'Place the button at the bottom';
$_['submission_button_float']               = 'Use floating button'; 

// Attributes
$_['attributes_header']                     = 'Attributes';
$_['attributes_group_setting']              = '<b>Default Settings for All Attributes</b><span class="help">These settings are applied to all product attributes that you have. Use the form below to edit settings for each individual attribute</span>';
$_['attributes_custom_set_descr']           = 'This section allows overriding default attribute settings (see above) for selected attributes. Please use the form to the left to choose the attributes you would like to change and then change their settings in the table below';
$_['attributes_individual_set']             = 'Individual Attributes Settings';
$_['btn_select_attribute']                  = 'Select Attribute';
$_['autocomplete_hint']                     = 'Start typing the name of the attribute you would like to change, click its name and then press the “%s” button'; 

// Options
$_['options_header']                        = 'Options';
$_['options_group_setting']                 = '<b>Default Settings for All Options</b><span class="help">These settings are applied to all product options that you have. Use the form below to edit settings for each individual option</span>';
$_['options_custom_set_descr']              = 'This section allows overriding default option settings (see above) for selected options. Please use the form to the left to choose the options you would like to change and then change their settings in the table below';
$_['options_individual_set']                = 'Individual Options Settings';
$_['options_view_mode']                     = 'View mode';
$_['options_view_mode_label']               = 'Show labels only';
$_['options_view_mode_image']               = 'Show images only';
$_['options_view_mode_image_and_label']     = 'Show labels and images';
$_['btn_select_option']                     = 'Select Option'; 

// Filters
$_['filters_header']                        = 'Filters';
$_['filters_group_setting']                 = '<b>Default Settings for All Filters</b><span class="help">These settings are applied to all product filters that you have. Use the form below to edit settings for each individual attribute</span>';
$_['filters_custom_set_descr']              = 'This section allows overriding default filter settings (see above) for selected filters. Please use the form to the left to choose the filters you would like to change and then change their settings in the table below';
$_['filters_individual_set']                = 'Individual Filters Settings';
$_['btn_select_filter']                     = 'Select Filter'; 

// Categories 
$_['categories_header']                     = 'Choose the categories where you want to have the filter enabled';
$_['categories_info']                       = 'Move the categories between the Enabled and Disabled lists. The current instance settings will be applied only to the Enabled categories. The current filter instance will not be shown for the Disabled categories.';
$_['categories_move_selected']              = 'Move<br>Selected';
$_['categories_filter_descr']               = 'Start typing in order to filter category list below';
$_['categories_list_of_enabled']            = 'Categories to <i>enable</i> the current filter instance';
$_['categories_list_of_disabled']           = 'Categories to <i>disable</i> the current filter instance';
$_['categories_select_all']                 = 'Select All';
$_['categories_unselect_all']               = 'Unselect All';
$_['categories_filter_hint']                = 'Type to filter Categories by a keyword';

// Responsive Behaviour
$_['responsive_header']                     = 'Responsive View';
$_['responsive_mode_enable']                = 'Show the filter in a popup in responsive mode';
$_['responsive_max_width']                  = 'Max Filter Popup Width<span class="help">Set this to zero to expand the filter popup to full screen width</span>';
$_['responsive_max_screen_width']           = 'Screen width at which the filter is moved into a popup<span class="help">This should be the width at which your responsive theme switches to its mobile version</span>';
$_['responsive_position']                   = 'Position';
$_['responsive_offset']                     = 'Top offset';
$_['responsive_collapse_sections']          = 'Collapse attribute sections'; 

// Theme
$_['theme_block_header']                    = 'Block Header';
$_['theme_text']                            = 'Text';
$_['theme_title']                           = 'Title';
$_['theme_border']                          = 'Border';
$_['theme_background']                      = 'Background';
$_['theme_price_slider']                    = 'Price Slider';
$_['theme_show_btn_color']                  = 'Colour of the Show Filter button';
$_['theme_reset_btn_color']                 = 'Colour of the Reset button';
$_['theme_product_quantity']                = 'Product Quantity';
$_['theme_group_block_header']              = 'Group Block Header';
$_['theme_slider_handle_border']            = 'Slider Handle Border';
$_['theme_responsive_popup_view']           = 'Responsive Popup View';
$_['theme_active_area_background']          = 'Active area background';
$_['theme_slider_handle_background']        = 'Slider Handle Background'; 

// Values Ordering
$_['ordering_header']                       = 'Attribute Values Ordering';
$_['ordering_filter_hint']                  = 'Type to filter Attributes by a keyword';
$_['ordering_descr']                        = 'Select an attribute, the values of which you want to put in order, with help of the input field below. Change sort order in the values list appeared by dragging the rows. Once all the values have been sorted out, click the tick button in order to save the changes.';
$_['ordering_note']                         = 'Note: This page provides ability to order <b>Attribute values</b> only. Ordering of Option and Filter values can be performed with help of standard Opencart tools';
$_['ordering_language']                     = 'Language';

// Global Settings
$_['global_header']                         = 'Global Settings';
$_['global_settings_descr']                 = 'These settings are applied to all the module instances and can\'t be overriden';
$_['global_hide_empty_stock']               = '<b>Hide Out Of Stock Products</b><span class="help">Makes the filter take into account stock status per each product option</span>';
$_['global_enable_multiple_attributes']     = '<b>Enable multiple attribute values</b><span class="help">Enabling of this option makes the extension treat attribute values not as singular values, but as a set of values separated with specified separator</span>';
$_['global_multiple_attr_separator']        = '(no spaces please)';
$_['global_separator']                      = 'Separator';
$_['global_in_stock_status_id']             = '<b>In Stock Status</b><span class="help">Set the default in stock status selected in product edit.</span>';
$_['global_subcats_fix']                    = '<b>Show products from subcategories in their parent categories</b><span class="help">This option enables showing products from child categories in the corresponding parent categories and invokes Brainy Filter for them. By default OpenCart requires adding each product into both a child and its parent category by hand.</span>'; 
$_['global_postponed_count']                = 'Postponed counting of totals<span class="help">Performs counting of totals in separate request to the server. This may increase page loading speed.</span>';

// Help
$_['help_about']                            = 'About';
$_['help_faq_n_trouleshooting']             = 'FAQ & Troubleshooting';
$_['help_about_content']                    = '<p>Brainy Filter Ultimate 5.1.2 OC2.3 is a module for OpenCart for filtering products. It\'s the most thought out and nice looking extention of the kind.</p>' .'<h2>Free Support</h2>' .'<p>Our team does its best to maintain the module bug free and easy to work with. If you find a bug in the module code we will fix it free of charge. We also can install the module and make a few tweaks to your theme to make it work. Please note that this can be done only once. If you chose another theme or reinstall the system we will not be able to provide you with free support for the second time. We also do not adjust the module template to suit any custom themes better and do not add any custom features within the bounds of the free support service.</p>' .'<p>(!) If you face a problem before contacting our support team please make sure to check the FAQ &amp; Troubleshoting section. Please note that section is loaded from the Internet and is updated periodically.</p>' .'<p>The support email is <a href="mailto:support@giantleaplab.com">support@giantleaplab.com</a>. (!) When contacting the support please provide your OpenCart order id and the date of purchase.</p>' .'<h2>Paid Support</h2>' .'<p>Our development team has enormous experience in web development and would be happy to help with any custom features you might need. We also develop non-OpenCart projects including quite large and complicated. You can always discuss all of the details and get a quote by contacting our sales department <a href="mailto:sales@giantleaplab.com">sales@giantleaplab.com</a>.</p>';



// Instance
$_['instance_content_top']                  = 'Content Top';
$_['instance_content_bottom']               = 'Content Bottom';
$_['instance_column_left']                  = 'Column Left';
$_['instance_column_right']                 = 'Column Right';
$_['instance_remove']                       = 'Remove';
$_['instance_layout']                       = 'Layout';
$_['instance_position']                     = 'Position';
$_['instance_basic_settings_info']          = 'Settings used by default for all layouts. Some of these settings can be overridden in the Filter Instances section.';
$_['instance_remove_default_layout']        = 'The default layout is used to display search results for requests sent from other layouts that don\\\'t include products. It cannot be removed or disabled.';
$_['instance_new_layout_notice']            = 'Please choose in what layout and in what position this filter instance will be shown on your site!';
$_['instance_default_layout']               = 'Default built-in layout';
$_['instance_remove_confirmation']          = 'Are you sure you want to remove this instance?';

// Attribute values sort order popup
$_['edit_values_sort_order']                = 'Values sort order';


// Messages
$_['message_success']                       = 'Success: You have modified Brainy Filter module!';
$_['message_new_instance']                  = 'In order to enable the module on the front-end please create at least one instance for any of the layouts in the Module Instances tab';
$_['message_empty_table']                   = 'The table is empty'; 
$_['message_error_attr_separator_empty']    = 'Error: Separator value for multiple attribute values is blank';
$_['message_error_permission']              = 'Error: You are not allowed to change settings of Brainy Filter!'; 
$_['message_error_submit_delay']            = 'Error: The value entered into the delay time field is invalid!';
$_['message_error_layout_not_set']          = 'Please choose a layout on which you want to see Brainy Filter, or remove the instance';
$_['message_unsaved_changes']               = 'Some settings have been changed. Continue without saving?';


// Common
$_['left']                                  = 'Left';
$_['right']                                 = 'Right'; 
$_['radio']                                 = 'Radio button';
$_['select']                                = '- Select -';
$_['module']                                = 'Modules';
$_['slider']                                = 'Slider with inputs';
$_['control']                               = 'Control';
$_['enabled']                               = 'Enabled';
$_['disabled']                              = 'Disabled';
$_['default']                               = 'Default';
$_['collapse']                              = 'Collapse';
$_['checkbox']                              = 'Checkbox';
$_['selectbox']                             = 'Selectbox';
$_['sort_order']                            = 'Sort order';
$_['enable_all']                            = 'Enable All';
$_['disable_all']                           = 'Disable All';
$_['grid_of_images']                        = 'Grid of images';
$_['set_all_default']                       = 'Default';
$_['slider_labels_only']                    = 'Slider with labels';
$_['slider_labels_and_inputs']              = 'Slider with inputs and labels';


$_['update_cache'] = 'Update Cache';
//$_['override_default'] = 'Override default';
$_['updating'] = 'Updating...';
$_['attribute_value'] = 'Attribute value';
$_['categories'] = 'Categories';
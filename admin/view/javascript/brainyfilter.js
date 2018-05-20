/**
 * Brainy Filter Ultimate 5.1.2 OC2.3, November 4, 2016 / brainyfilter.com 
 * Copyright 2015-2016 Giant Leap Lab / www.giantleaplab.com 
 * License: Commercial. Reselling of this software or its derivatives is not allowed. You may use this software for one website ONLY including all its subdomains if the top level domain belongs to you and all subdomains are parts of the same OpenCart store. 
 * Support: http://support.giantleaplab.com
 */
(function($){
    var BF = {
        instanceId : null,
        isFirstLaunch : true,
        settings : {},
        attributes : {},
        options : {},
        filters : {},
        lang : {},
        $elem : $(),
        settingsHash : '',
        
        init : function() {
            this.$elem = $('#bf-form');
            this.fillForm(this.$elem, this.settings);
            if (this.moduleId === 'basic') {
                $('.bf-local-settings').remove();
            }
            this.initTabs();
            this.initCustomFilterSettings();
            this.initSwitchers();
            this.initColorPicker();
            this.initSectionSorting();
            this.initAdvacedSettings();
            this.initIntersection();
            this.initSliders();
            this.initAutocomplete();
            this.initSelectAll();
            this.initCategoryTab();
            this.initOrderingTab();
            
            this.$elem.submit(this.submitForm.bind(this));
            this.hideEmptyTables();
            this.$elem.find('#bf-faq-n-troubleshooting').one('click', this.insertFaqFrame);
            
            // check unsaved settings
            var $curTabInp = $('[name="bf[current_adm_tab]"]').prop('disabled', true);
            this.settingsHash = this.$elem.serialize().hashCode();
            $curTabInp.prop('disabled', false);
            $('a[href]').on('click', function(e){
                $curTabInp.prop('disabled', true);
                var hash = this.$elem.serialize().hashCode();
                $curTabInp.prop('disabled', false);
                if (this.settingsHash !== hash && !confirm(this.lang.confirm_unsaved_changes)) {
                    e.preventDefault();
                    return false;
                }
            }.bind(this));
        },
        
        initTabs : function() {
            this.$elem.find('.tab').on('click', function(){
                var $tab = $(this),
                    tabName = $tab.data('tabName'),
                    $target = $($tab.data('target')),
                    group  = $target.data('group'),
                    container = $('#bf-adm-main-container');
                    
                $tab.parent().find('.tab').removeClass('selected');
                $tab.addClass('selected');
                container.height(container.height());
                
                $('.tab-content[data-group=' + group + ']').hide();
                $target.css({display : 'block', opacity : 0})
                    .animate({opacity : 1}, 200, function(){
                        container.css('height', 'auto');
                    });
                $('[name="bf[current_adm_tab]"]').val(tabName);
            });
            this.$elem.find('.tabs').each(function(){
                var $tabs = $(this).find('.tab').not('.hidden');
                if (!$tabs.filter('.selected').size()) $tabs.first().addClass('selected');
            });
            this.$elem.find('.tab.selected').each(function() {
                $($(this).data('target')).show();
            });
        },
        
        initSwitchers : function() {
            if (this.moduleId === 'basic') {
                $('.bf-def').remove();
            } else if (this.moduleId === 'new') {
                $('.bf-switcher input[type=hidden]').val('2');
                $('.bf-layout-enable .bf-switcher input[type=hidden]').val('0');
            }
            $('.bf-layout-enable .bf-def').remove();
            this.$elem.on('click', '.bf-switcher', function(e){
                var $choice = $(e.target),
                    $switch = $(this),
                    $input  = $switch.find('input').first(),
                    val = $choice.is('.bf-no') ? 0 : ($choice.is('.bf-yes') ? 1 : 2);
                if (!$input.is('[disabled]')) {
                    $input.val(val).change();
                    $switch.find('span').removeClass('bf-active');
                    $choice.addClass('bf-active');
                }
            });
            $('.bf-switcher').each(function(){
                var $switch = $(this),
                    val = ~~$switch.find('input').first().val(),
                    active = val === 0 ? '.bf-no' : (val === 1) ? '.bf-yes' : '.bf-def';
                    $switch.find(active).addClass('bf-active');
            });
        },
        
        initColorPicker : function() {
            $("input.color-pick").ColorPickerSliders({
                size: 'sm',
                placement: 'top',
                swatches: false,
                sliders: false,
                hsvpanel: true
            });
        },
        
        initSectionSorting : function() {
            $('#bf-filter-sections tbody').sortable({
                animation: 100,
                scroll: false,
                ghostClass: 'bf-sort-ghost'
            });
        },
        
        initAdvacedSettings : function() {
            $('[data-adv-group]').each(function(){
                var group = $(this).data('adv-group');
                $(this).prop('disabled', $('[data-disable-adv="'+group+'"]').val() !== '1');
            });
            this.$elem.on('change', '[data-disable-adv]', function(){
                var group = $(this).data('disable-adv'), val = $(this).val();
                $('[data-adv-group="'+group+'"]').each(function(){
                    $(this).prop('disabled', !(val === '1' || $(this).data('for-val') === val));
                });
            });
        },
        
        initIntersection : function() {
            this.$elem.on('change', '.bf-intersect input', function(){
                var p = $(this).closest('.bf-intersect');
                var val = $(this).val();
                $(this).closest('.bf-intersect-cont').find('.bf-intersect').each(function(){
                    if ($(this)[0] !== p[0]) {
                        if (val === '1') {
                            $(this).parent().find('.bf-no').click();
                        }
                    }
                });
            });
        },
        
        initSliders : function() {
            this.$elem.find('.bf-th-header').each(function(){
                if(!$(this).hasClass('expanded')){
                    $(this).next().hide();
                }
            });
            this.$elem.find('.bf-th-header').click(function(){
                var tbl = $(this).next();
                if ($(this).hasClass('expanded')) {
                    $(this).removeClass('expanded');
                    tbl.stop().slideUp(200);
                } else {
                    $(this).addClass('expanded');
                    tbl.stop().slideDown(200);
                }

            });
        },
        
        initAutocomplete : function() {
            this.$elem.find('.bf-autocomplete').each(function(i, v){
                var lookup = [],
                    lookupData = this[$(v).data('lookup')];
                for (var i in lookupData) {
                    var name = lookupData[i].group 
                             ? lookupData[i].group + ' / ' + lookupData[i].name 
                             : lookupData[i].name;
                    lookup.push({value: name, data : i});
                }
                $(v).autocomplete({
                    lookup: lookup, 
                    lookupLimit: 10,
                    onSelect: function (suggestion) {
                        var data = $.extend({}, this[$(v).data('lookup')][suggestion.data]);
                            data.id = suggestion.data;
                        $(v).parent().find('.bf-add-row').data('filterData', data);
                    }.bind(this)
                });
                
            }.bind(this));
            this.$elem.find('.bf-attr-autocomplete').each(function(i, v){
                var lookup = [];
                for (var i in this.attributes) {
                    var name = this.attributes[i].group + ' / ' + this.attributes[i].name;
                    lookup.push({value: name, data : i});
                }
                $(v).autocomplete({
                    lookup: lookup, 
                    lookupLimit: 10,
                    onSelect: function (suggestion) {
                        console.log(suggestion.data);
                    }.bind(this)
                });
                
            }.bind(this));
        },
        
        initCustomFilterSettings : function() {
            $('.bf-add-row').click(function(e){
                e.preventDefault();
                var $this = $(this),
                    $tbl = $($this.data('targetTbl')),
                    tpl  = $($this.data('rowTpl')).html(),
                    data = $this.data('filterData');
                if (data.group) {
                    data.name = '<b>' + data.group + '</b> / ' + data.name;
                }
                if (!data) return;
                BF.addRow($tbl, tpl, data.id, data);
                $this.data('filterData', '');
                $this.parent().find('input[type=text]').val('');
            });
            
            $(document).on('click', '.bf-remove-row', this.removeRow);
            
            for (var i in this.settings.attributes) {
                var data = this.settings.attributes[i];
                data.name = '<b>' + this.attributes[i].group + '</b> / ' + this.attributes[i].name;
                this.addRow($('#custom-attr-settings'), $('#custom-attr-setting-template').html(), i, data);
            }
            
            for (var i in this.settings.options) {
                var data = this.settings.options[i];
                    data.name = this.options[i].name;
                this.addRow($('#custom-opt-settings'), $('#custom-opt-setting-template').html(), i, data);
            }
            
            for (var i in this.settings.filters) {
                var data = this.settings.filters[i];
                    data.name = this.filters[i].name;
                this.addRow($('#custom-filter-settings'), $('#custom-filter-setting-template').html(), i, data);
            }
        },
        
        initSelectAll : function() {
            this.$elem.find('[data-select-all]').click(function(){
                var group = $(this).attr('data-select-all'),
                    val   = ~~$(this).attr('data-select-all-val'),
                    cls   = val === 1 ? '.bf-yes' : (val === 2 ? '.bf-def' : '.bf-no');
                $(this).closest('[data-select-all-group="' + group + '"]').find(cls).click();
                return false;
            });
        },
        
        initCategoryTab : function() {
            // init selecting
            var $listEnabled = $('#bf-enabled-categories'),
                $listDisabled = $('#bf-disabled-categories');
        
            $listEnabled.on('click', '.bf-row', function(e){
                $(e.target).toggleClass('bf-selected');
            });
            $listDisabled.on('click', '.bf-row', function(e){
                $(e.target).toggleClass('bf-selected');
            });
            
            $('[data-select-all]').on('click', function(){
                $($(this).data('select-all')).find('.bf-row').not('hidden').addClass('bf-selected');
            });
            $('[data-unselect-all]').on('click', function(){
                $($(this).data('unselect-all')).find('.bf-row').removeClass('bf-selected');
            });
            
            // init filtering
            this.$elem.find('.bf-cat-filter').on('keyup', function(){
                var $target = $($(this).data('target')),
                    value = $(this).val();
                if (value !== '') {
                    $target.find('.bf-row').each(function(){
                        var $row = $(this), 
                            str = $row.text(),
                            hide = str.toLowerCase().indexOf(value.toLowerCase()) === -1;
                        $row.toggleClass('hidden', hide);
                        if (hide) {
                            $row.removeClass('bf-selected');
                        }
                    });
                } else {
                    $target.find('.bf-row').removeClass('hidden');
                }
            });
            
            // init movement
            this.$elem.find('.bf-move-left').on('click', function(){
                $listEnabled.append($listDisabled.find('.bf-row.bf-selected').removeClass('bf-selected'));
                return false;
            });
            this.$elem.find('.bf-move-right').on('click', function(){
                $listDisabled.append($listEnabled.find('.bf-row.bf-selected').removeClass('bf-selected'));
                return false;
            });
            
            // init tab appearance
            $('#bf-layout-id').change(function(){
                $('[data-target="#bf-categories"]').toggleClass('hidden',  BF.categoryLayouts.indexOf($(this).val()) === -1);
            });
        },
        
        initOrderingTab : function() {
            // init selecting
            $('#bf-attr-list').on('click', '.bf-row', function(e){
                $('#bf-attr-list .bf-row.bf-selected').removeClass('bf-selected');
                var $attr = $(this),
                    attrId = $attr.data('attrId');
                $attr.toggleClass('bf-selected');
                BF.loadAttrValues(attrId);
            });
                        
            // init filtering
            this.$elem.find('.bf-attr-filter').on('keyup', function(){
                var $target = $($(this).data('target')),
                    value = $(this).val();
                if (value !== '') {
                    $target.find('.bf-row').each(function(){
                        var $row = $(this), 
                            str = $row.text(),
                            hide = str.toLowerCase().indexOf(value.toLowerCase()) === -1;
                        $row.toggleClass('hidden', hide);
                        if (hide) {
                            $row.removeClass('bf-selected');
                        }
                    });
                } else {
                    $target.find('.bf-row').removeClass('hidden');
                }
            });
            
            $('#bf-attr-val-language').on('change', this.changeAttrValuesLanguage);
            $('#bf-attr-values .bf-save-btn').on('click', this.saveAttributeValuesOrder);
            $('#bf-attr-values .bf-auto-sort').on('click', function(){
                var $btn = $(this), type = $btn.data('type'), 
                    direction, lbl;
                if ($btn.hasClass('bf-desc')) {
                    direction = 'desc';
                    $btn.text(type === 'number' ? '0..9' : 'A..Z');
                } else {
                    direction = 'asc';
                    $btn.text(type === 'number' ? '9..0' : 'Z..A');
                }
                $btn.toggleClass('bf-desc');
                $btn.addClass('bf-active');
                BF.sortAttributeValues(type, direction);
                return false;
            });
        },
        
        loadAttrValues : function(attrId) {
            var $list = $('#bf-attr-val-list'),
                curLang = $('#bf-attr-val-language').val();
            $list.children().remove();
            $.ajax({
                url : BF.attrValActionUrl,
                data : {attr_id : attrId},
                dataType : 'json',
                success : function(json) {
                    if (!json || json.error) {
                        return false;
                    }
                    for (var lang in json) {
                        var $group = $('<div class="bf-row-group lang-'+lang+'"></div>');
                        for (var i in json[lang]) {
                            var $row = $('<div class="bf-row"></div>');
                            $row.data('valueId', json[lang][i].attribute_value_id).text(json[lang][i].value);
                            $group.append($row);
                        }
                        $list.append($group);
                        $group.sortable({ animation: 100, ghostClass: 'bf-sort-ghost' });
                        $group.toggleClass('hidden', lang !== curLang);
                        $group.data('lang', lang);
                    }
                }
            });
        },
        
        changeAttrValuesLanguage : function() {
            var curLang = $('#bf-attr-val-language').val(),
                $list = $('#bf-attr-val-list');
        
            $list.find('.bf-row-group').addClass('hidden');
            $list.find('.lang-' + curLang).removeClass('hidden');
        },
        
        saveAttributeValuesOrder : function() {
            var data = { sort_order: {} };
            $('#bf-attr-val-list .bf-row-group').each(function(){
                var $group = $(this);
                $group.find('.bf-row').each(function(i){
                    data.sort_order[$(this).data('valueId')] = i;
                });
            });
            $('#bf-attr-val-list').css('opacity', '0.3');
            $.ajax({
                type : 'post',
                url : BF.attrValActionUrl,
                data : data,
                success : function() {
                    $('#bf-attr-val-list').css('opacity', '1');
                }
            });
        },
        
        sortAttributeValues : function(type, direction) {
            var $table = $('#bf-attr-val-list .bf-row-group:visible'),
                values = [],
                sortAsc = function(a, b){
                    return a.val < b.val ? -1 : 1;
                },
                sortDesc = function(a, b){
                    return a.val > b.val ? -1 : 1;
                };
            
            $table.find('.bf-row').each(function(){
                var $row = $(this), 
                    val = (type === 'number') 
                        ? parseFloat($row.text().replace(/(^[\d\.]+)(.*)/, '$1'))
                        : $row.text() + ' ';
                    if (type === 'number' && isNaN(val)) val = 1e10;
                values.push({val: val, $: $row});
            });
            
            values.sort(direction === 'desc' ? sortDesc : sortAsc);
            
            $.each(values, function(i, v){
                $table.append(v.$);
            });
        },
        
        hideEmptyTables : function($tbl) {
            $tbl = $tbl || this.$elem.find('.bf-hide-if-empty');
            $tbl.each(function(){
                var isEmpty = !$(this).find('tbody').children().size();
                $(this).toggleClass('hidden', isEmpty);
                if (isEmpty) {
                    $(this).after('<div class="alert alert-info">' + BF.lang.empty_table + '</div>');
                } else {
                    $(this).next('.alert').remove();
                }
            });
        },
        
        fillForm : function(form, settingsObj) {
            var settings = this._convertToFormNames(settingsObj);
            for (var name in settings) {
                var field = form.find('[name="bf'+name+'"]');
                if (field.size()) {
                    var type = field.eq(0).attr('type') ? field.eq(0).attr('type').toLowerCase() : '';
                    var tag  = field.eq(0).prop('tagName').toLowerCase();
                    if (type === 'text' || type === 'hidden' || tag === 'select') {
                        field.val(settings[name]);
                    } else if (type === 'radio' || type === 'checkbox') {
                        field.filter('[value="'+settings[name]+'"]').attr('checked', 'checked');
                    }
                } else {
    //                console.log(name + ' - not found');
                }
            }
        },
        
        _convertToFormNames : function(obj) {
            var out = {};
            if (typeof obj === 'object' || typeof obj === 'array') {
                for (var k in obj) {
                    var arr = this._convertToFormNames(obj[k]);
                    for (var k2 in arr) {
                        out['['+k+']'+k2] = arr[k2];
                    }
                }
            } else {
                out[''] = obj;
            }
            return out;
        },
        
        _convertFormToObj : function() {
            var obj = {};
            var func = function(obj, arr, val) {
                var f = arr.shift();
                if (typeof obj[f] === 'undefined') {
                    obj[f] = {};
                }
                if (arr.length) {
                    return func(obj[f], arr, val);
                } else {
                    obj[f] = val;
                }
            };
            // replace each checkbox with hidden field in order to don't lose unticked items
            this.$elem.find('input[type=checkbox]').each(function(){
                var hidden = $('<input type="hidden" />');
                hidden.attr('name', $(this).attr('name'));
                hidden.val($(this).is(':checked') ? '1' : '0');
                $(this).replaceWith(hidden);
            });
            $(this.$elem.serializeArray()).each(function(i, v){
                var arr = this.name.replace(/(^[^\[]+\[)|(\]$)/g, '').split('][');
                func(obj, arr, this.value);
            });
            return obj;
        },
        
        addRow : function($tbl, tplStr, id, vals) {
            vals = vals || {};
            var $tpl = $(tplStr.split('{i}').join(id));
            if (vals.name) {
                $tpl.find('[data-bf-role=name]').html(vals.name);
            }
            if (vals.enabled) {
                $tpl.find('[data-bf-role=enabled]').val(vals.enabled);
            }
            if (vals.control) {
                $tpl.find('[data-bf-role=control]').val(vals.control);
            }
            $tbl.prepend($tpl);
            this.hideEmptyTables($tbl.closest('table'));
        },
        
        removeRow : function() {
            var $tbl = $(this).closest('table');
            $(this).closest('tr').remove();
            BF.hideEmptyTables($tbl);
        },
        
        insertFaqFrame : function() {
            var $frame = '<iframe src="http://docs.brainyfilter.com/faq-and-troubleshooting.html"></iframe>';
            $('#bf-faq-n-troubleshooting').next().html($frame);
        },
        
        validateForm : function() {
            $('.warning, .success').remove();
            var success = true,
                layoutSelected = ~~this.$elem.find('.bf-layout-select').val(),
                isInstance = this.$elem.find('.bf-layout-select').size();
                
            if (!layoutSelected && isInstance) {
                success = false;
                $('.breadcrumb').after('<div class="alert alert-warning bf-validation-msg">'+BF.lang.error_layout_not_set+'</div>');
            }
            
            return success;
        },
        
        submitForm : function(e) {
            if (e) {
                e.preventDefault();
            }
            if (!this.validateForm()) {
                return;
            }
            
            $('#bf-enabled-categories input[type=hidden]').remove();
            
            this.$elem.find('input[disabled], select[disabled]').removeAttr('disabled');
            $('[name=bf_layout]').attr('disabled','disabled');
            var data = this._convertFormToObj();
            if (this.moduleId !== 'basic') {
                delete data.global;
            }

            data['behaviour']['sort_order'] = {};
            this.$elem.find('.bf-sort').each(function(i){
                data['behaviour']['sort_order'][$(this).data('section')] = i;
            });

            $('#form [name=bf]').val(JSON.stringify(data));
            $('#form').submit();
        },
        
        refreshDB : function() {
            var btn = $('#bf-refresh-db'),
                lbl = btn.find('.lbl').text();
            if (!btn.hasClass('wait')) {
                btn.addClass('wait').find('.lbl').text(BF.lang.updating);
                $.ajax({
                    url : BF.refreshActionUrl,
                    success : function() {
                        btn.removeClass('wait');
                        btn.find('.lbl').text(lbl);
                    }
                });
            }
        }
    };
    
    window['BF'] = BF;
})(jQuery);

String.prototype.hashCode = function() {
  var hash = 0, i, chr, len;
  if (this.length === 0) return hash;
  for (i = 0, len = this.length; i < len; i++) {
    chr   = this.charCodeAt(i);
    hash  = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
};
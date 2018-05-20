/**
 * Brainy Filter Ultimate 5.1.2 OC2.3, November 4, 2016 / brainyfilter.com 
 * Copyright 2015-2016 Giant Leap Lab / www.giantleaplab.com 
 * License: Commercial. Reselling of this software or its derivatives is not allowed. You may use this software for one website ONLY including all its subdomains if the top level domain belongs to you and all subdomains are parts of the same OpenCart store. 
 * Support: http://support.giantleaplab.com
 */
(function($){
    
if (typeof BrainyFilter === 'undefined') {
    var BrainyFilter = {

        ajaxHandler: null,

        sliderId: ".bf-slider-range",

        filterFormId: ".bf-form",

        maxFieldId: "[name='bfp_price_max']",

        minFieldId: "[name='bfp_price_min']",

        max: 0,
        min: 0,
        lowerValue : 0,
        higherValue : 0,

        timeout: null,

        urlSeparators: null,

        selectionCache: {},

        ajaxEnabled: true,

        requestCount : false,
        requestPrice : false,
        separateCountRequest : false,

        redirectTo: '',
        baseUrl: '',
        currentRoute: '',
        
        isInitialized: false,

        init: function() {
            this.isInitialized = true;
            this.ajaxEnabled = !isIE() && $(this.selectors.container).size() 
                                       && $(this.selectors.paginator).size();
            this.redirectTo = this.redirectTo.replace('&amp;', '&');

            $(this.sliderId).each(function(){
                var $slider = $(this),
                    sliderType = parseInt($slider.data('slider-type'));
                $slider[0].slide = null;
                $slider.slider({
                    range: true,
                    min: BrainyFilter.min,
                    max: BrainyFilter.max,
                    values: [BrainyFilter.lowerValue, BrainyFilter.higherValue],
                    slide: function( event, ui ) {
                        $(BrainyFilter.minFieldId).val(ui.values[0]);
                        $(BrainyFilter.maxFieldId).val(ui.values[1]);
                    },
                    stop : function(){ BrainyFilter.currentSendMethod($(this)); }
                });
                if (sliderType === 2 || sliderType === 3) {
                    BrainyFilter.priceSliderLabels($slider);
                }
            });
            
            $('.bf-price-container input').keyup(function() {
                var $inp = $(this), 
                    index = ($inp.hasClass("bf-range-max")) ? 1 : 0;
                (index) ? $(".bf-range-max").val($inp.val()) 
                        : $(".bf-range-min").val($inp.val());
                $(BrainyFilter.sliderId).slider("values", index, $inp.val());
            });
            $('.bf-search-container input').keyup(function(e) {
                var code = e.keyCode || e.which;
                if (code === 13) {
                    e.preventDefault();
                    BrainyFilter.sendRequest();
                    $(document).one('bfFloatButton.show', function(){
                        $('.bf-float-submit').hide();
                    });
                } else {
                    //BrainyFilter.currentSendMethod();
                }
            });

            try {
                this.initSliders();
            } catch(e) {
                console.log(e);
            }

            if ((this.requestCount || this.requestPrice) && this.separateCountRequest) {
                this.getTotalByAttr();
            }

            $(this.filterFormId).find('select, input').change(function(){
                var fid = $(this).data('filterid');
                if (fid) {
                    var $input = $('input[data-filterid='+fid+']');
                    if ($(this)[0].checked) {
                        $input.attr('checked', 'checked');
                    } else {
                        $input.removeAttr('checked');
                    }
                }
                BrainyFilter.currentSendMethod($(this));
            });

            $(this.filterFormId).find('input[type="checkbox"], input[type="radio"]').change(function(){
                if (!$(this).closest('.bf-attr-filter').find('.bf-count').size()) {
                    BrainyFilter.addCross($(this));
                }
            });

            $(this.filterFormId).find('input[type="checkbox"], input[type="radio"]').each(function(i,v){
                BrainyFilter.addCross($(v));
            });
            
            $(this.filterFormId).find('option[data-totals]').each(function(){
                var totals = $(this).data('totals');
                $(this).append('<span> (' + totals + ')</span>');
            });

            this.floatSubmit();

            $(this.filterFormId).submit(function(){
//                BrainyFilter.sendRequest();
                return false;
            });

            this.selectionCache = this.serializeMultipleForms();

            this.initSliding();

            this.initGrid();

            this.rebindSearchAction();

            this.collapse();
            
            this.initHorizontalView();

            this.initAbsolutePosition();
            
            this.addBFilterParam();
            
            window.onpopstate = function() {
                window.location.reload();
            };
        },

        initSliders : function() {
            $('.bf-slider-container').each(function(){
                var $slider = $(this),
                    sliderType = parseInt($slider.data('slider-type')),
                    $cont = $slider.closest('.bf-attr-filter'),
                    id = $slider.data('slider-group'),
                    $s = $('[data-slider-group='+id+']').not($slider).closest('.bf-attr-filter'),
                    minInp = $cont.find('[data-min-limit]'),
                    maxInp = $cont.find('[data-max-limit]'),
                    minLbl = $cont.find('.bf-slider-text-inp-min'),
                    maxLbl = $cont.find('.bf-slider-text-inp-max'),
                    labels = BrainyFilter.sliderValues[id].slice(0),
                    values = [NaN, NaN];
                    var v1 = parseInt(minInp.val()),
                        v2 = parseInt(maxInp.val());
                    for (var i=0; i < labels.length; i++) {
                        if (parseInt(labels[i].s) === v1) {
                            values[0] = i + 1;
                        }
                        if (parseInt(labels[i].s) === v2) {
                            values[1] = i + 1;
                        }
                    }
                    //values = [parseInt(minInp.val()) + 1, parseInt(maxInp.val()) + 1];
                    labels.splice(0, 0, {n:''});
                    labels.splice(labels.length, 0, {n:''});
                    values[0] = isNaN(values[0]) ? 0 : values[0];
                    values[1] = isNaN(values[1]) ? labels.length - 1 : values[1];
                    
                $slider.slider({
                    range: true,
                    min: 0,
                    max: labels.length - 1,
                    values: values,
                    slide: function(e, ui) {
                        minInp.val(labels[ui.values[0]].s);
                        maxInp.val(labels[ui.values[1]].s);
                        minLbl.attr('value', labels[ui.values[0]].n).val(labels[ui.values[0]].n);
                        maxLbl.attr('value', labels[ui.values[1]].n).val(labels[ui.values[1]].n);
                        $s.find('[data-min-limit]').val(labels[ui.values[0]].s);
                        $s.find('[data-max-limit]').val(labels[ui.values[1]].s);
                        var sMinLbl = $s.find('.bf-slider-text-inp-min'),
                            sMaxLbl = $s.find('.bf-slider-text-inp-max');
                        sMinLbl.val(labels[ui.values[0]].n);
                        sMaxLbl.val(labels[ui.values[1]].n);
                        $s.find('.bf-slider-container').slider('option', 'values', ui.values);
                    },
                    stop : function(){ BrainyFilter.currentSendMethod($(this)); }
                });
                
                var changeSlider = function(){
                    var $slider = $(this).closest('.bf-attr-filter').find('.bf-slider-container'),
                        id = $slider.data('slider-group'),
                        val = $(this).val(),
                        target = $(this).hasClass('bf-slider-text-inp-min') ? 'min' : 'max',
                        fVal = -1;
                    for (var i = 0; i < labels.length; i++) {
                        if (labels[i].n === val) {
                            fVal = i;
                            break;
                        }
                    }
                    if (fVal !== -1) {
                        if (target === 'min') {
                            minInp.val(labels[fVal].s);
                            $slider.slider("values", 0, fVal);
                        } else {
                            maxInp.val(labels[fVal].s);
                            $slider.slider("values", 1, (fVal) ? fVal : labels.length - 1);
                        }
                    }
                };
                minLbl.keyup(changeSlider);
                maxLbl.keyup(changeSlider);
                
                if (sliderType === 2 || sliderType === 3) {
                    BrainyFilter.addSliderLabels($slider, labels);
                }
            });
        },

        initGrid : function() {
            $('.bf-grid input').each(function(){ 
                var sel = function(el){
                    if (el.is(':checked')) {
                        el.closest('.bf-grid').find('.bf-grid-item').removeClass('selected');
                        el.closest('.bf-grid-item').addClass('selected');
                    }
                };
                $(this).change(function(){ sel( $(this) ); });
                sel( $(this) );
            });
        },

        addCross: function($obj) {
            var fid = $obj.data('filterid');

            $('input[data-filterid='+fid+']').each(function(){
                var checkbox = $(this);
                var parent = checkbox.closest('.bf-attr-filter');
                if($obj[0].checked) { 
                    if (checkbox.is("input[type='radio']")) {
                        checkbox.parents('.bf-attr-block').find('.bf-cross').remove();
                    }
                    var cross = $('<span class="bf-cross" data-filterid="'+fid+'"></span>');
                    cross.click(function(){
                        var fid = $(this).data('filterid');
                        var $cross = $('.bf-cross[data-filterid='+fid+']');
                        $cross.closest('.bf-attr-filter').find('input').removeAttr('checked');
                        BrainyFilter.currentSendMethod($(this));
                        $cross.hide();
                        setTimeout(function(){$cross.remove();}, 500);
                    });
                    parent.find('.bf-c-3').html(cross);
                    checkbox.attr('checked', 'checked');
                    checkbox[0].checked = true;
                } else {
                    parent.find('.bf-cross').remove();
                    if (checkbox[0].checked) {
                        checkbox.removeAttr('checked');
                    }
                }
            });
        },

        reset: function() {
            if ($('.bf-buttonclear').is('[disabled]')) {
                return;
            }
            $('.bf-slider-container').each(function(){
                var $slider = $(this),
                    $cont = $slider.closest('.bf-attr-filter'),
                    minInp = $cont.find('[data-min-limit]'),
                    maxInp = $cont.find('[data-max-limit]'),
                    minLbl = $cont.find('.bf-slider-text-inp-min'),
                    maxLbl = $cont.find('.bf-slider-text-inp-max'),
                    values = [0, $slider.slider('option', 'max')];
                $slider.slider('option', 'values', values);
                minInp.val(values[0]);
                maxInp.val(values[1]);
                minLbl.val('').attr('value', '');
                maxLbl.val('').attr('value', '');
            });
            $(this.filterFormId + ' .bf-search-container input').val('');
            $(this.sliderId).slider("option", "values", [this.min, this.max]);
            $(this.filterFormId + ' ' + this.minFieldId).val(this.min);
            $(this.filterFormId + ' ' + this.maxFieldId).val(this.max);
            $(this.filterFormId + ' input').filter(':checkbox, :radio').removeAttr('checked');
            $(this.filterFormId + ' option').removeAttr('selected');
            $(this.filterFormId + ' option.bf-default').attr('selected', 'true');
            $(this.filterFormId).find('.bf-cross').remove();
            this.sendRequest();
            var disableBtn = function() {
                $('.bf-buttonclear').attr('disabled', 'disabled');
                $(document).ajaxStop(enableBtn);
            };
            var enableBtn  = function() {
                $(document).unbind('ajaxStart', disableBtn);
                $(document).unbind('ajaxStop', enableBtn);
                $('.bf-buttonclear').removeAttr('disabled');
            };
            $(document).ajaxStart(disableBtn);
        },

        currentSendMethod: function($elem){
            var submitType = $elem.closest(BrainyFilter.filterFormId).data('submit-type');
            var submitDelay = $elem.closest(BrainyFilter.filterFormId).data('submit-delay');
            switch(submitType) {
                case 'auto':
                    BrainyFilter.sendRequest();
                    break;
                case 'delay':
                    if (BrainyFilter.timeout) {
                        clearTimeout(BrainyFilter.timeout);
                    }
                    BrainyFilter.timeout = setTimeout(BrainyFilter.sendRequest, submitDelay);
                    break;
                default:
                    break;
            }
        },

        sendRequest: function() {
            if (BrainyFilter.ajaxEnabled) {

                // hide results until response will be recieved
                $('.bf-panel-wrapper').addClass('bf-panel-hidden');
                $('.bf-panel-wrapper').append('<div class="ajax-shadow"></div>');
                
                if(this.ajaxHandler && this.ajaxHandler.readystate !== 4){
                    this.ajaxHandler.abort();
                }

                this.ajaxHandler = $.ajax({
                    url: window.location.origin + BrainyFilter.baseUrl +'index.php' 
                            + BrainyFilter.prepareFilterData(false, 'extension/module/brainyfilter/ajaxfilter') 
                            + '&count=' + (BrainyFilter.requestCount ? '1' : '0')
                            + '&price=' + (BrainyFilter.requestPrice ? '1' : '0')
                            + '&withContent=1'
                            + '&curRoute=' + BrainyFilter.currentRoute,
                    dataType:'json',
                    type: 'get',
                    success: function(res) {
                        if (res) {
                            BrainyFilter.updateProductList(res.products, res.brainyfilter);
                        }
                        $(BrainyFilter.filterFormId).find('input[type="checkbox"], input[type="radio"]').each(function(i,v){
                            BrainyFilter.addCross($(v));
                        });
                        var newUrl   = window.location.origin + BrainyFilter.prepareFilterData(true, false);

                        if ($(BrainyFilter.sliderId).size()) {
                            BrainyFilter.changePriceSlider(res.min, res.max);
                        };
                        
                        BrainyFilter.historyPushState(res, newUrl);

                        BrainyFilter.selectionCache = BrainyFilter.serializeMultipleForms();
                        
                        $(document).trigger('productlistchange');
                    },
                    complete: function() {
                        $('.bf-panel-wrapper .ajax-shadow').remove();
                        $('.bf-panel-wrapper').removeClass('bf-panel-hidden');
                    }
                }); 
            }else{
                var newUrl;
                if (BrainyFilter.redirectTo) {
                    newUrl = BrainyFilter.redirectTo + (BrainyFilter.redirectTo.match(/\?/) ? '&' : '?') + BrainyFilter.serializeFilterForm();
                } else {
                    newUrl = window.location.origin + BrainyFilter.prepareFilterData(true);
                }
                window.location = newUrl;
            }
        }, 
        
        updateProductList: function(html, bfData) {
            var $gridBtn = $('#grid-view').clone(true), 
                $listBtn = $('#list-view').clone(true),
                $html    = $(html),
                $prodCont = $html.find(BrainyFilter.selectors.container),
                $paginCont = $html.find(BrainyFilter.selectors.paginator),
                $emptyMsg = $('<p/>', {'class': 'bf-empty-product-list-msg'}).html(bfLang.empty_list)
                ;

            if ($prodCont.size()) {
                $(BrainyFilter.selectors.container).html($prodCont.html());
                $(BrainyFilter.selectors.paginator).html($paginCont.html());
            } else {
                $(BrainyFilter.selectors.container).html($emptyMsg);
                $(BrainyFilter.selectors.paginator).html('');
            }
            
            BrainyFilter.addBFilterParam();
            
            $('#grid-view').replaceWith($gridBtn);
            $('#list-view').replaceWith($listBtn);
            try {
                if (typeof display === 'function') {
                    if (typeof $.totalStorage !== 'undefined') {
                        display($.totalStorage('display'));
                    } else if (typeof $.cookie !== 'undefined'){
                        display($.cookie('display')); 
                    }
                } else
                if (typeof dataAnimate === 'function') { //support for Boss Themes animation
                    dataAnimate();
                } else {
                    if (typeof localStorage.getItem === 'function' && localStorage.getItem('display') === 'list') {
                        $listBtn.trigger('click');
                    } else {
                        $gridBtn.trigger('click');
                    }
                }
            } catch(e) {
                console.log('Brainy Filter - call of display() failed: ' + e);
            }
            if (BrainyFilter.requestCount) {
                BrainyFilter.changeTotalNumbers(bfData);
            }
            BrainyFilter.initSliding();
            BrainyFilter.hideEmptySections();
            BrainyFilter.initHorizontalScrolls();
        },
        
        historyPushState: function(data, url) {
           window.history.pushState({brainyfilter: true}, document.title, url);
        },
        
        addBFilterParam: function() {
            var self = BrainyFilter, 
                bfilter = BrainyFilter.serializeFilterForm();
            $.fn.bfparam = function(bfparam){
                if (bfparam !== '') {
                    this.each(function(){
                        var $this = $(this), attr = $this.is('[value]') ? 'value' : 'href',
                        url = $(this).attr(attr);
                        url = url.replace(/[\?\&](bfilter|manufacturer_id|search|category_id)\=[^\&]+/g, '');
                        url += /\?/.test(url) ? '&' : '?';
                        url += bfilter;
                        $this.attr(attr, url);
                    });
                }
                return this;
            };
            $('body').find('option[value^="http"]').bfparam(bfilter);
            $(self.selectors.paginator).find('a').bfparam(bfilter);
        },

        prepareFilterData: function(full, route) {
            var path, query;
            var nRoute = '';
            var nPath  = '';
            var str = window.location.search;
            if (str.match(/[\?\&]route\=/)) {
                var nRoute = str.replace(/(.*)([\?\&])(route\=)([^\&]+)(.*)/, '$4');
            }
            if (str.match(/[?\&]path\=/)) {
                var nPath  = str.replace(/(.*)([\?\&])(path\=)([^\&]+)(.*)/, '$4');
            }
            path  = window.location.pathname;
            query = window.location.search.replace(/[\&\?]+((route)|(path)|(page)|(bfilter)|(search)|(category_id)|(sub_category)|(description)|(manufacturer_id))[^&]+/g, '');

            $(this.filterFormId).find('.bf-disabled input').removeAttr('disabled');

            var form  = this.serializeMultipleForms();

            $(this.filterFormId).find('.bf-disabled input').attr('disabled', 'disabled');
            form = '&' + form;

            if (route) {
                form = form.replace(/[\&](route)=[^\&]+/g, '').replace(/[\&]+$/, '');
                form = 'route=' + route + form;
            } else {
                form = form.replace(/[\&](route|path|manufacturer_id)=[^\&]+/g, '').replace(/[\&]+$/, '');
                if (nPath !== '') {
                    form = 'path=' + nPath + form;
                }
                if (nRoute !== '') {
                    form = 'route=' + nRoute + '&' + form;
                }
            }

            var str= window.location.toString();
            str = str.match(/[\?\&]route\=/);
            if (full === true && !str) {
                form = form.replace(/[\&\?]((route)|(path))[^&]+/g, '');
            }

            form = form.replace(/[\&]bfp_[^\=]+\=[^\&]*/g, '');

            var bfilter = this.serializeFilterForm();

            if (form !== '') {
                query = query + (query === '' ? '' : '&') + form;
            }
            if (bfilter !== '') {
                query = query + (query === '' ? '' : '&') + bfilter;
            }
            query = '?' + query.replace(/(\?)|(^[\&])/, '').replace(/[\&]+/, '&');

            return (full) ? path + query : query;
        },

        serializeFilterForm : function(){
            var form  = this.serializeMultipleForms();

            $(this.filterFormId).find('.bf-disabled input').attr('disabled', 'disabled');
            form = '&' + form;

            if (parseInt($(BrainyFilter.minFieldId).val()) === BrainyFilter.min) {
                form = form.replace(/\&bfp_price_min\=[^\&]+/g, '');
            }
            if (parseInt($(BrainyFilter.maxFieldId).val()) === BrainyFilter.max) {
                form = form.replace(/\&bfp_price_max\=[^\&]+/g, '');
            }

            $('.bf-slider-container').each(function(){
                var $slider = $(this), 
                    $cont = $slider.closest('.bf-slider'), 
                    values = $slider.slider('option', 'values');
                if (values[0] === 0) {
                    var name = $cont.find('[data-min-limit]').attr('name');
                    form = form.replace(new RegExp("\&"+name+"\=[^\&]+","g"), '');
                }
                if (values[1] === $slider.slider('option', 'max')) {
                    var name = $cont.find('[data-max-limit]').attr('name');
                    form = form.replace(new RegExp("\&"+name+"\=[^\&]+","g"), '');
                }
            });

            var params = form.replace(/^\&/, '').split('&');
            var bfilter = '';
            var brandFilter = '';
            var price = ['na', 'na'];
            var rating = [];
            var filters = {};
    //        var searchFilters = {};
            if (params.length) {
                for (var i = 0; i < params.length; i ++) {
                    var parts = params[i].split('=');
                    var name = parts[0];
                    var value = parts[1];
                    if (name.match(/^bfp_/)) {
                        if (value !== '') {
                            var p = name.split('_');
                            if (p[1] === 'price') {
                                price[(p[2] === 'min') ? 0 : 1] = value;
                            } else if (p[1] === 'rating') {
                                rating.push(value);
                            } else if (p[1] === 'min' || p[1] === 'max') {
                                if (typeof filters[p[2]] === 'undefined') {
                                    filters[p[2]] = {min: 'na', max: 'na'};
                                }
                                filters[p[2]][p[1]] = value;
                            } else {
                                if (typeof filters[p[1]] === 'undefined') {
                                    filters[p[1]] = [];
                                }
                                filters[p[1]].push(value);
                            }
                        }
                    } else if (name === 'manufacturer_id') {
                        brandFilter = 'manufacturer_id=' + value;
                    }
                }
            }
            if (price[0] !== 'na' || price[1] !== 'na') {
                bfilter += 'price:' + price[0] + '-' + price[1] + ';';
            }
            if (rating.length) {
                bfilter += 'rating:' + rating.join(',') + ';';
            }
            for (var id in filters) {
                if (filters.hasOwnProperty(id)) {
                    if (!$.isArray(filters[id])) {
                        bfilter += id + ':' + filters[id].min + '-' + filters[id].max + ';';
                    } else {
                        bfilter += id + ':' + filters[id].join(',') + ';';
                    }
                }
            }
            var output = '',
                searchFilters = BrainyFilter.serializeSearchForm();
            if (searchFilters !== '') {
                output += (output === '' ? '' : '&') + searchFilters;
            }
            if (brandFilter) {
                output += (output === '' ? '' : '&') + brandFilter;
            }
            if (bfilter !== '') {
                output += (output === '' ? '' : '&') + 'bfilter=' + bfilter;
            }
            return output;
        },

        serializeMultipleForms : function() {
            if ($(this.filterFormId).size() === 1) {
                return $(this.filterFormId).serialize();
            }
            var params = $(this.filterFormId).serializeArray();
            for (var i = 0; i < params.length; i ++) {
                for (var j = i+1; j < params.length; j ++) {
                    if (params[i].name === params[j].name && params[j].value !== '' && params[j].value.toLowerCase() !== 'na') {
                        params[i].value = params[j].value;
                        params.splice(j, 1);
                        break;
                    }
                }
            }
            var str = '';
            for (var i = 0; i < params.length; i ++) {
                str += (str !== '' ? '&' : '') + params[i].name + '=' + params[i].value;
            }
            return str;
        },

        serializeSearchForm : function() {
            var $search = $('#content').find('[name=search]');
            if ($search.size() && $search.val() !== '') {
                var str = 'search=' + encodeURIComponent($search.val());
                var cat = $('#content').find('[name=category_id]').val();
                if (parseInt(cat) > 0) {
                    str += '&category_id=' + cat;
                }
                if ($('#content').find('[name=sub_category]:checked').size()) {
                    str += '&sub_category=true';
                }
                if ($('#content').find('[name=description]:checked').size()) {
                    str += '&description=true';
                }
                return str;
            }
            return '';
        },

        rebindSearchAction : function() {
            $('#button-search').unbind('click')
                .click(function(){
                    BrainyFilter.sendRequest();
                    return false;
                });
        },

        getTotalByAttr: function() {
            $.ajax({
                url: window.location.origin + BrainyFilter.baseUrl +'index.php' 
                            + BrainyFilter.prepareFilterData(false, 'extension/module/brainyfilter/ajaxfilter') 
                            + '&count=' + (BrainyFilter.requestCount ? '1' : '0')
                            + '&price=' + (BrainyFilter.requestPrice ? '1' : '0')
                            + '&curRoute=' + BrainyFilter.currentRoute,
                dataType:'json',
                type: 'get',
                success: function(res) {
                    BrainyFilter.changeTotalNumbers(res.brainyfilter);
                    BrainyFilter.initSliding();
                    BrainyFilter.hideEmptySections();
                    BrainyFilter.initHorizontalScrolls();
                    if (typeof res.min !== 'undefined') {
                        BrainyFilter.changePriceSlider(res.min, res.max);
                    }
                }
            }); 
        },

        changeTotalNumbers: function(data) {
            var $form = $(BrainyFilter.filterFormId).filter('.bf-with-counts');
            $form.find('.bf-count').remove();
            $form.find('option span').remove();
            $form.find('select').removeAttr('disabled');
            var $ctrls = $form.find('.bf-attr-filter')
                .find('input, option')
                .not(':checked')
                .not(':selected')
                .not('[type=text]')
                .not('[type=hidden]')
                .not('.bf-default'),
                $checkedBlks = $form.find('.bf-attr-filter input[type=checkbox]')
                .filter(':checked')
                .parents('.bf-attr-block');
            
            $ctrls.attr('disabled', 'disabled')
                .not('option')
                .parents('.bf-attr-filter')
                .addClass('bf-disabled');

            $ctrls.each(function(){
                var $this = $(this), 
                name = $this.prop('tagName') === 'OPTION' ? $this.parent().attr('name') : $this.attr('name'),
                gid  = name.replace(/(bfp_)([^_]+)(.*)/, '$2'),
                val  = $this.attr('value');
                if (data[gid] && data[gid][val]) {
                    if ($this.prop('tagName') === 'OPTION') {
                        $this.append('<span> (' + data[gid][val] + ')</span>');
                    } else {
                        var $row = $this.closest('.bf-attr-filter').removeClass('bf-disabled');
                        $row.find('.bf-cell').last().append('<span class="bf-count">' + data[gid][val] + '</span>');
                    }
                    $this.removeAttr('disabled');
                }
            });
            
            $checkedBlks.find('.bf-disabled').each(function(){
                var $this = $(this);
                $this.removeClass('bf-disabled').find('.bf-cell').last().append('<span class="bf-count bf-empty">0</span>');
                $this.find('input:disabled').removeAttr('disabled');
            });
            $checkedBlks.find('.bf-count').each(function(i, v){
                var $this = $(v);
                $this.text('+' + $this.text());
            });
            // disable select box if it hasn't any active option
            $form.find('select').each(function(){
                if ($(this).find('option').not('.bf-default,[disabled]').size() === 0) {
                    $(this).attr('disabled', 'true');
                }
            });
        },

        initHorizontalView: function() {
            if (!$('.bf-check-position').hasClass('bf-horizontal')){
                return;
            }
            BrainyFilter.initHorizontalScrolls();

            // slide up/down all the horizontal filter layout
            $('.bf-toggle-filter-arrow').on('click', function() {
                var $cont = $(this).closest('.box').find('.brainyfilter-panel');
                if ($cont.is(':visible')) {
                    $cont.stop().slideUp(600);
                    $(this).addClass('bf-down');
                } else {
                    $cont.stop().slideDown(600);
                    $(this).removeClass('bf-down');
                }
            });
            // add shrink/expand ability for attribute groups
            $('.bf-horizontal').find('.bf-attr-group-header').each(function(){
                var $arrow = $('<a class="bf-group-arrow"></a>');
                $(this).append($arrow);
                var $group = $(this)
                        .nextUntil('.bf-attr-group-header', '.bf-attr-block')
                        .wrapAll('<div class="bf-attr-block-wrapper"></div>')
                        .parent();

                $arrow.on('click', function(){
                    if ($group.is(':visible')) {
                        $arrow.addClass('bf-down');
                        $group.slideUp(600);
                    } else {
                        $arrow.removeClass('bf-down');
                        $group.slideDown(600);
                    }
                });
            });

        },
        
        initHorizontalScrolls : function() {
            var scroll = function(block, direction) {
                var $block = $(block);
                var dw = $block.width();
                var $last = $block.find('.bf-attr-filter').filter(':visible').last();
                var mw = $last.position().left + $last.outerWidth();
                var curOffset = -parseInt($block.find('.bf-attr-block-cont').css('left')) || 0;
                var newOffset = direction > 0 ? curOffset + dw : curOffset - dw;
                    newOffset = newOffset < 0 ? 0 : newOffset;
                $block.find('.bf-attr-filter').filter(':visible').each(function(i){
                    var left = $(this).position().left;
                    var width = $(this).outerWidth();
                    var offset = (newOffset + dw > mw) ? mw - dw : newOffset;
                    var id = (newOffset + dw > mw) ? i + 1 : i;
                    if (offset > left && offset < left + width) {
                        newOffset = $block.find('.bf-attr-filter').filter(':visible').eq(id).position().left;
                        return false;
                    }
                });
                $block.find('.bf-attr-block-cont').stop().animate({left: "-"+ newOffset +"px"}, 1200);
            };
            
            var filterRows = $('.bf-horizontal').find('.bf-attr-block')
                    .not('.bf-keywords-filter')
                    .not('.bf-price-filter')
                    .not('.bf-slider');
            
            filterRows.each(function(){
                var scrollBlockWidth = 0;
                $(this).find('.bf-row').filter(':visible').each(function(){
                    scrollBlockWidth += $(this).outerWidth();
                });
                var $win  = $(this).find('.bf-sliding-cont');
                var $cont = $(this).find('.bf-attr-block-cont');
                $cont.width(scrollBlockWidth + 100);
                $win.removeClass('bf-scrollable');
                $win.parent().find('.bf-btn-left').remove();
                $win.parent().find('.bf-btn-right').remove();
                if ($win.width() < scrollBlockWidth) {
                    $(this).addClass('bf-with-scroll');
                    $win.addClass('bf-scrollable');
                    var $btnLeft  = $('<a class="bf-btn-left"></a>');
                    var $btnRight = $('<a class="bf-btn-right"></a>');
                    $win.after($btnLeft, $btnRight);
                    $btnLeft.on('click', function(){
                        scroll($win, -1);
                    });
                    $btnRight.on('click', function(){
                        scroll($win, 1);
                    });
                } else {
                    $cont.css({left : 0});
                }
            });
                /*The vertical alignment of the blocks*/
            filterRows.each(function(i,v){
                var attrHeader = $(this).find('.bf-attr-header').outerHeight();
                var contHeight = $(this).find('.bf-sliding-cont').outerHeight();
                var slidingCont = $(this).find('.bf-sliding-cont');
                if(attrHeader > contHeight){
                    var margin = (attrHeader - contHeight)/2;
                    slidingCont.css('margin-top', margin);
                    if (slidingCont.hasClass('bf-scrollable')) {
                         $(this).find('.bf-btn-left').css('margin-top', margin);
                         $(this).find('.bf-btn-right').css('margin-top', margin);
                    };
                }
            });
             /*END*/
        },
        
        changePriceSlider: function(min, max) {
            $(this.sliderId).each(function(){
                var $slider = $(this),
                    sliderType = parseInt($slider.data('slider-type')),
                    priceCont = $slider.closest('.bf-price-container'),
                    minVal = parseFloat(priceCont.find(BrainyFilter.minFieldId).val()),
                    maxVal = parseFloat(priceCont.find(BrainyFilter.maxFieldId).val()),
                    vals = [minVal, maxVal],
                    curMin = $slider.slider('option', 'min'),
                    curMax = $slider.slider('option', 'max');
                min = parseFloat(min);
                max = parseFloat(max);

                BrainyFilter.max = max;
                BrainyFilter.min = min;
                $slider.slider('option', 'min', min);
                $slider.slider('option', 'max', max);
                if (vals[0] === curMin || vals[0] < min) {
                    vals[0] = min;
                }
                if (vals[1] === curMax || vals[1] > max) {
                    vals[1] = max;
                }
                if (vals[0] > vals[1]) {
                    vals[0] = vals[1];
                }
                $slider.slider('option', 'values', vals);

                if (sliderType === 2 || sliderType === 3) {
                    BrainyFilter.priceSliderLabels($slider);
                }
                
                priceCont.find(BrainyFilter.minFieldId).val(vals[0]);
                priceCont.find(BrainyFilter.maxFieldId).val(vals[1]);
            });
        },

        floatSubmit: function() {
            var cont = $(BrainyFilter.filterFormId).filter('.bf-with-float-btn');
            var winWidth = $('body').width();
            var btn      = cont.find('.bf-buttonsubmit').eq(0);
            var closeBtn = $('<div class="bf-close-btn"></div>');
            var tick     = $('<div class="bf-tick"></div>');
            var panel    = $('<div class="bf-float-submit"></div>').prepend(tick)
                    .append(btn).append(closeBtn);
            $('body').append(panel);
            panel.css('display', 'none');

            var timer = null;
            var hideBtn = function(){
                $('.bf-float-submit').fadeOut(400);
            };
            closeBtn.click(hideBtn);
            var showBtn = function(){
                var form = BrainyFilter.serializeMultipleForms();
                if (BrainyFilter.selectionCache === form || cont.closest('.bf-responsive.bf-active').hasClass('bf-opened')) {
                    hideBtn();
                    return;
                }
                var outBlockOffset = $(this).closest('.brainyfilter-panel').offset();
                var blockOffset = $(this).closest('.bf-attr-filter').offset();
                var blockHeight = $(this).closest('.bf-attr-filter').outerHeight();
                var panelHeight = panel.outerHeight();
                var panelWidth  = panel.outerWidth();
                var blockWidth  = $(this).closest('.brainyfilter-panel').outerWidth();
                var panelNewLeft = (outBlockOffset.left + blockWidth + panelWidth > winWidth) 
                    ? outBlockOffset.left - panelWidth + 4
                    : outBlockOffset.left + blockWidth - 4;
                if (panel.css('display') === 'block') {
                    if (parseFloat(panel.css('left')) === panelNewLeft) {
                        panel.animate({top: blockOffset.top + (blockHeight - panelHeight) / 2}, 300);
                    } else {
                        panel.offset({top: blockOffset.top + (blockHeight - panelHeight) / 2, left: panelNewLeft});
                    }
                } else {
                    panel.css('display', 'block');
                    panel.offset({top: blockOffset.top, left: panelNewLeft});
                    panel.css({top: blockOffset.top + (blockHeight - panelHeight) / 2});
                }
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(hideBtn, 10000);
                $(document).trigger('bfFloatButton.show');
            };
            cont.find('input, select').not('[type="text"]').change(showBtn);
            cont.find('input[type="text"]').keyup(showBtn);
            cont.find('.bf-c-3').on('click', '.bf-cross', showBtn);
            cont.find(BrainyFilter.sliderId).on( "slidestop", showBtn);
            cont.find('.bf-slider-container').on( "slidestop", showBtn);
        },

        loadingAnimation: function() {
            $('.bf-tick').addClass('bf-loading');
            var stopSpin = function(){
                $('.bf-tick').removeClass('bf-loading');
                $(document).unbind('ajaxComplete', stopSpin);
                $('.bf-float-submit').css('display', 'none');
            };
            $(document).ajaxComplete(stopSpin);
        },

        initSliding: function() {
            $(this.filterFormId).each(function(){
                var $form = $(this);
                var enableSliding = $form.hasClass('bf-with-sliding');
                var limitHeight   = $form.hasClass('bf-with-height-limit');
                var limitHeightOpts = parseInt($form.data('height-limit'));
                var visibleItems = parseInt($form.data('visible-items'));
                var hideItems = parseInt($form.data('hide-items'));
                $form.find('.bf-attr-block-cont').each(function(i, v) {
                    var $this = $(this), 
                        firstInit = false, 
                        wrapper = $this.parent();
                    if (!$this.parent().is('.bf-sliding')) {
                        $this.wrap('<div class="bf-sliding"></div>');
                        $this.parent().wrap('<div class="bf-sliding-cont"></div>');
                        wrapper = $this.parent();
                        wrapper.addClass('bf-expanded');
                        firstInit = true;
                    }
                    if (enableSliding) {
                        var count = $this.find('.bf-attr-filter').filter(':visible').size() - visibleItems;
                        if ( count > 0 && count >= hideItems) {
                            if ($this.parent().hasClass('bf-expanded') && !firstInit) {
                                BrainyFilter.expandBlock(v);
                                $this.parent().parent().find('.bf-sliding-show').removeClass('bf-hidden');
                            } else {
                                BrainyFilter.shrinkBlock(v, visibleItems, true);
                            }
                        } else {
                            $this.parent().css('height', 'auto');
                            $this.parent().parent().find('.bf-sliding-show').addClass('bf-hidden');
                        }
                    }else if(limitHeight) {
                        if (wrapper.height()) {
                            wrapper.css('height', 'auto');
                            if (wrapper.height() > limitHeightOpts) {
                                wrapper.css({'height':limitHeightOpts, 'overflow-x': 'hidden', 'overflow-y': 'auto'});
                            } else {
                                wrapper.css('height', 'auto');
                            }
                        }
                    }
                    $this.parents('.bf-attr-block').find('.bf-attr-header').addClass('bf-clickable');

                    $this.parents('.bf-attr-block').find('.bf-attr-header').off('click').on("click", function(){
                        if ( wrapper.hasClass('bf-expanded') ) {
                            BrainyFilter.shrinkBlock(v, 0);
                        } else {
                            BrainyFilter.expandBlock(v);
                        }
                    });
                });
            });
        },
        
        shrinkBlock: function(block, items, disableAnim) {
            // disable for horizontal view
            if ($(block).closest('.bf-check-position').hasClass('bf-horizontal')) {
                return;
            }
            var $form = $(block).closest(BrainyFilter.filterFormId);
            var visibleItems = parseInt($form.data('visible-items'));
            var count   = $(block).find('.bf-attr-filter').filter(':visible').size() - visibleItems;
            var height  = 0;
            var wrapper = $(block).closest('.bf-sliding-cont');
            var showMore = wrapper.find('.bf-sliding-show').addClass('bf-hidden');
            if (items) {
                $(block).find('.bf-attr-filter').each(function(j, vv){
                    if (j < items) {
                        height += $(vv).height();
                    }
                });
                if (!showMore.size()) {
                    wrapper.append('<div class="bf-sliding-show" ></div>'); 
                }
                wrapper.find('.bf-sliding-show')
                    .text(bfLang.show_more + ' (' + count + ')')
                    .unbind('click')
                    .removeClass('bf-hidden')
                    .click(function() {
                        BrainyFilter.expandBlock(block);
                    });
            }

            if (disableAnim) {
                $(block).closest('.bf-sliding').stop().css('height', height);
            } else {
                $(block).closest('.bf-sliding').stop().animate({height: height}, 300);
            }
            if (!items) {
                $(block).closest('.bf-attr-block').find('.bf-arrow').css('background-position', '50% -128px');
            }
            $(block).parent().removeClass('bf-expanded');
        },

        expandBlock: function(block, disableAnim) {
            // disable for horizontal view
            if ($(block).closest('.bf-check-position').hasClass('bf-horizontal')) {
                return;
            }
            var $form = $(block).closest(BrainyFilter.filterFormId);
            var limitHeight = $form.hasClass('bf-with-height-limit');
            var limitHeightOpts = parseInt($form.data('height-limit'));
            var visibleItems = parseInt($form.data('visible-items'));
            var fullHeight = $(block).height();
            var wrapper    = $(block).parent();
            if(limitHeight && limitHeightOpts < fullHeight) {
                fullHeight = limitHeightOpts;
            }
            if (disableAnim) {
                wrapper.stop().css('height', fullHeight);
            } else {
                wrapper.stop().animate({height : fullHeight}, 300);
            }
            wrapper.parent().find('.bf-sliding-show')
                .text(bfLang.show_less)
                .unbind('click')
                .removeClass('bf-hidden')
                .click(function() {
                    BrainyFilter.shrinkBlock(block, visibleItems);
                });
            wrapper.addClass('bf-expanded');
            $(block).parents('.bf-attr-block').find('.bf-arrow').css('background-position', '50% -153px');
        },

        collapse: function() {
            var height  = 0;
            $('.bf-collapse').parents('.bf-attr-block').find('.bf-sliding').removeClass('bf-expanded');
            $('.bf-collapse').parents('.bf-attr-block').find('.bf-sliding-show').addClass('bf-hidden');
            $('.bf-collapse').parents('.bf-attr-block').find('.bf-arrow').css('background-position', '50% -128px');
            $('.bf-collapse').parents('.bf-attr-block').find('.bf-sliding').stop().animate({height: height}, 300);

        },
        
        hideEmptySections : function() {
            $('.bf-attr-block').each(function(){
                var $this = $(this);
                $this.removeClass('bf-hidden');
                if ($this.find('.bf-attr-filter').filter(':visible').size()) {
                    $this.removeClass('bf-hidden');
                } else {
                    $this.addClass('bf-hidden');
                }
            });
            $('.bf-attr-group-header').each(function(){
                var $this = $(this);
                $this.removeClass('bf-hidden');
                if ($this.nextUntil('.bf-attr-group-header', '.bf-attr-block').filter(':visible').size()
                        || $this.next('.bf-attr-block-wrapper').find('.bf-attr-filter').filter(':visible').size()) {
                    $this.removeClass('bf-hidden');
                } else {
                    $this.addClass('bf-hidden');
                }
            });
        },
        
        initAbsolutePosition : function() {
            var $layout = $('.bf-responsive').eq(0),
                $form = $layout.find(BrainyFilter.filterFormId),
                $fixedLayout = $layout.find('.bf-check-position'),
                curSubmitType, isHorizontal;
            
            var checkWidth = function(){
                var p = 15,
                    w = $(window).width(),
                    mw = parseInt($form.data('resp-max-scr-width')),
                    rw = parseInt($form.data('resp-max-width'));
                if (w <= mw) {
                    $layout.addClass('bf-active');
                    if ($layout.parent().prop('tagName') !== 'BODY') {
                        $('body').append($layout);
                    }
                    var mh = $(window).height() - parseInt($fixedLayout.css('top')) - p;
                    var nw = w - p * 2;
                        nw = nw > rw && rw > 0 ? rw : nw;
                    $fixedLayout.css({'width': nw, 'max-height' : mh});
                    // force auto submit
                    $form.data('submit-type', 'auto');
                    // suppress horizontal view
                    if (isHorizontal) {
                        $form.closest('.bf-check-position').removeClass('bf-horizontal');
                    }
                    // collapse sections
                    if ($form.data('resp-collapse')) {
                        $form.find('.bf-attr-block-cont').each(function(){
                            BrainyFilter.shrinkBlock(this, 0);
                        });
                    }
                    if ($layout.hasClass('bf-opened')) {
                        preventBodyScroll();
                    }
                } else {
                    $layout.removeClass('bf-active');
                    $fixedLayout.css({'width': 'inherit', 'max-height' : 'none'});
                    $form.data('submit-type', curSubmitType);
                    if (isHorizontal) {
                        $form.closest('.bf-check-position').addClass('bf-horizontal');
                    }
                    if ($layout.parent().prop('tagName') === 'BODY') {
                        $('#bf-brainyfilter-anchor').after($layout);
                    }
                    letBodyScroll();
                }
            };
            
            var preventBodyScroll = function() {
                var $body = $('body');
                var $doc = $(document);
                if (!$body.hasClass('bf-non-scrollable')) {
                    $body.css({'top' : -$doc.scrollTop(), 'left' : -$doc.scrollLeft()});
                    $body.addClass('bf-non-scrollable');
                }
            };
            var letBodyScroll = function() {
                var $body = $('body.bf-non-scrollable');
                if ($body.size()) {
                    $body.removeClass('bf-non-scrollable');
                    $(document).scrollTop(-parseInt($body.css('top')));
                    $(document).scrollLeft(-parseInt($body.css('left')));
                    $body.css({'top' : 'auto', 'left' : 'auto'});
                }
            };
            
            if ($layout.size()) {
                $layout.before('<div id="bf-brainyfilter-anchor"></div>');
//                $form = $layout.find(BrainyFilter.filterFormId);
                curSubmitType = $form.data('submit-type');
                isHorizontal  = $form.closest('.bf-check-position').hasClass('bf-horizontal');
                $(window).resize(checkWidth);
                $layout.find('.bf-btn-show').on('click', function(){
                    $layout.toggleClass('bf-opened');
                    if ($('body').hasClass('bf-non-scrollable')) {
                        letBodyScroll();
                    } else {
                        preventBodyScroll();
                    }
                });
            }
            checkWidth();
        },
        
        addSliderLabels : function($slider, labels, showExtrems) {
            var $lbl = $('<span />', {'class': 'bf-slider-label'}),
                w = $slider.outerWidth(),
                n = labels.length - 1,
                dx = w / n,
                dxp = 100 / n,
                $labels = [],
                line = 0;
        
            $slider.find('.bf-slider-label').remove();
            
            $.each(labels, function(i, v){
                if ((!i || i === n) && !showExtrems) return;
                var offset = dxp * i,
                    $l = $lbl.clone();
                
                $slider.append($l);
                $l.text(typeof v.n !== 'undefined' ? v.n : v);
                var lw = $l.width(), marg = (!i) ? 0 : ((i === n) ? -lw : -lw / 2);
                
                if (i === 1) {
                    line = lw;
                } else {
                    var lblLeft = w * offset / 100 + marg;
                    if (line > lblLeft) {
                        if (i === labels.length - 2) {
                            $labels[$labels.length-1].text('').css('margin-left', 0);
                        } else {
                            $l.text('');
                            marg = 0;
                        }
                    } else {
                        line = lblLeft + lw;
                    }
                }
                
                $l.css({left: offset + '%', 'margin-left' : marg});
                $labels.push($l);
            });
            
        },
        priceSliderLabels : function($slider){
            var w = $slider.outerWidth(),
                px = BrainyFilter.max - BrainyFilter.min,
                d = px < 2 ? w : ((BrainyFilter.max.toFixed(2) + '').length + 1) * 6 * 1.5,
                n = Math.floor(w / d),
                dx = w / n,
                labels = [BrainyFilter.currencySymb + BrainyFilter.min.toFixed(2)];
                
            for (var i = 1; i < n; i ++) {
                var p = Math.round(BrainyFilter.min + i * dx / w * (BrainyFilter.max - BrainyFilter.min));
                labels.push(BrainyFilter.currencySymb + p.toFixed(2));
            }
            labels.push(BrainyFilter.currencySymb + BrainyFilter.max.toFixed(2));
            BrainyFilter.addSliderLabels($slider, labels, true);
        }
    };
    window['BrainyFilter'] = BrainyFilter;
}
if (typeof isIE === 'undefined') {
    function isIE(){
        if ((document.all && document.querySelector && !document.addEventListener) 
         || (document.all && !document.querySelector) 
         || (document.all && document.querySelector && document.addEventListener && 
        !window.atob)) {
            return true;
        }else{
            return false;
        }
    }
}
if (typeof window.location.origin === 'undefined') {
  window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
}

})(jQuery);

if ( ! window.console ) console = { log: function(){} };

jQuery(document).ready(function(){
    function onProductListChangeHandler () {
        if(localStorage.getItem('display') === 'grid') {
            Journal.gridView();
        } else {
            Journal.listView();
        }
        if (Journal.quickViewStatus) {
            Journal.enableQuickView();
        }
        $('.main-products .product-grid-item .image > a').prepend('<div class="p-over p-grid-over"> </div>');
        $('.main-products .product-list-item .image > a').prepend('<div class="p-over p-list-over"> </div>');

        $("img.lazy").lazy({
            bind: 'event',
            visibleOnly: false,
            effect: "fadeIn",
            effectTime: 250
        });

        if ($('html').hasClass('product-page') || $('html').hasClass('quickview')) {
            Journal.enableCloudZoom('inner');
        }

        Journal.itemsEqualHeight();
    }
    
    if (typeof Journal !== 'undefined' && BrainyFilter && !BrainyFilter.journalCompatibilityEnabled) {

        BrainyFilter.journalCompatibilityEnabled = true;

        $(document).on('productlistchange', onProductListChangeHandler);
    }
    
});
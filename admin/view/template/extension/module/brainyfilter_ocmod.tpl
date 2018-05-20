<?php echo $header; ?>
<?php echo $column_left; ?>

<div id="content">
    <?php if ($success) : ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="box">
        <div class="heading page-header">
            <div class="container-fluid">
                <h1><?php echo $heading_title; ?></h1>
            </div>
        </div>
    </div>
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="form">
        <input type="hidden" name="action" value="save" />
        <div id="bf-adm-main-menu">
        </div>
        <div id="bf-adm-main-container">
            <div id="bf-adm-basic-settings" class="tab-content" data-group="main">
                <div class="bf-panel">
                    <div class="tab-content-inner">
                        <div class="row">
                            <label for="bf-enabled-on"><?php echo $lang->enabled; ?></label>
                            <input type="radio" name="bf[enabled]" value="1" id="bf-enabled-on" <?php if ($enabled): ?>checked="checked"<?php endif; ?> />
                            <label for="bf-enabled-off"><?php echo $lang->disabled; ?></label>
                            <input type="radio" name="bf[enabled]" value="0" id="bf-enabled-off" <?php if (!$enabled): ?>checked="checked"<?php endif; ?> />
                            <div class="pull-right">
                                <input type="submit" class="btn btn-success" value="Submit" id="submit-btn" />
                            </div>
                        </div>
                        <div class="row">
                            <textarea name="bf[xml]" id="text-xml"><?php echo $xml; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
    <div class="bf-signature"><?php echo $lang->bf_signature; ?></div>
    <!--                -->
</div>
<style>
#form input {
	display: inline-block;
}
#text-xml {
    width: 100%;
    box-sizing: border-box;
    min-height: 500px;
}
.bf-panel .row {
    margin: 0 0 10px;
}
#bf-adm-basic-settings {
    display: block;
}
</style>
<script>
(function($){
    $('#form').submit(function(e){
        e.preventDefault();
        var $btn = $('#submit-btn');
        $btn.attr('value', 'Processing...');
        var data = {bf : { 
                enabled : ~~$('[name="bf\[enabled\]"]:checked').val() ? 'true' : 'false',
                xml     : $('#text-xml').val()
            }
        }
        $.post('', data, function(){
            $.post('<?php echo str_replace('&amp;', '&', $modRefreshAction); ?>',{enable : data.bf.enabled}, function(){
                $btn.attr('value', 'Submit');
            });
        });
    });
})(jQuery);
</script>
<?php echo $footer;
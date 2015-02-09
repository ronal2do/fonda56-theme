<div class="wrap">
<div id="smpush-icon-push" class="icon32"><br></div>
<h2><?php echo get_admin_page_title();?></h2>

<div id="available-widgets" class="widgets-holder-wrap ui-droppable" style="clear:both;">
  <div class="sidebar-name">
    <div class="sidebar-name-arrow"><br></div>
    <h3>Send Progress</h3>
  </div>
  <div class="widget-holder">
    <div class="smpush_progress">
        <div id="smpush_progressbar"><div class="smpush_progress_label">Loading...</div></div>
        <div id="smpush_progressinfo"></div>
        <input type="button" id="cancel_push" class="button button-primary" value="Cancel Send Operation" />
    </div>
  </div>
  <br class="clear">
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
  $("#smpush_progressbar").progressbar({"value": 0});
  <?php if($resumsend === false){?>
  SMPUSH_ProccessQueue('<?php echo admin_url();?>', <?php echo $allcount;?>, <?php echo $increration;?>);
  <?php }else{?>
  SMPUSH_RunQueue('<?php echo admin_url();?>', <?php echo $allcount;?>);
  <?php }?>
  $("#cancel_push").click(function(){
    setTimeout(smpush_resum_timer);
    window.location = '<?php echo admin_url();?>admin.php?page=smpush_cancelqueue&noheader=1';
  });
});
</script>
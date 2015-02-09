<div class="wrap">
   <div id="smpush-icon-archive" class="icon32"><br></div>
   <h2><?php echo get_admin_page_title();?><a href="<?php echo $pageurl;?>&empty=1&noheader=1" class="smio-delete add-new-h2">Clear Archive</a><img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" /></h2>
   <div id="col-container">
      <div id="col-left" style="width: 100%">
      <form action="<?php echo $pageurl;?>" method="get">
      <input type="hidden" name="page" value="<?php echo $pagname;?>" />
      <input type="hidden" name="noheader" value="1" id="smpush-noheader-value" />
         <div class="col-wrap">
          <p class="search-box">
              <label class="screen-reader-text">Search Messages:</label>
              <input type="search" name="query" value="<?php echo (!empty($_GET['query']))?$_GET['query']:'';?>">
              <input type="submit" id="search-submit" class="button" value="Search Devices">
           </p>
          <div class="tablenav top">
      		<div class="alignleft actions bulkactions">
                <select name="doaction">
                  <option value="0">Bulk Actions</option>
                  <option value="delete">Delete</option>
                </select>
                <input type="submit" name="apply" class="button action" value="Apply">
        	</div>
            <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo self::$paging['result'];?> items</span></div>
        	<br class="clear">
        	</div>
             <table class="wp-list-table widefat fixed tags" cellspacing="0" <?php if(get_bloginfo('version') < 3.8){?>style="table-layout: auto"<?php }?>>
                <thead>
                   <tr>
                      <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
                      <th scope="col" class="manage-column" style="width:110px"><span>ID</span></th>
                      <th scope="col" class="manage-column"><span>Message</span></th>
                      <th scope="col" class="manage-column column-categories" style="width: 30%;">Report<span></span></th>
                      <th scope="col" class="manage-column smpush-center"><span>Start Time</span></th>
                      <th scope="col" class="manage-column smpush-center"><span>End Time</span></th>
                      <th scope="col" class="manage-column column-categories" style="width:100px">Action<span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
                      <th scope="col" class="manage-column" style="width:110px"><span>ID</span></th>
                      <th scope="col" class="manage-column"><span>Message</span></th>
                      <th scope="col" class="manage-column column-categories">Report<span></span></th>
                      <th scope="col" class="manage-column smpush-center"><span>Start Time</span></th>
                      <th scope="col" class="manage-column smpush-center"><span>End Time</span></th>
                      <th scope="col" class="manage-column column-categories" style="width:100px">Action<span></span></th>
                   </tr>
                </tfoot>
                <tbody id="push-token-list">
                <?php if($archives){$counter = 0;foreach($archives AS $archive){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $archive->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <th scope="row" class="check-column">
                        <label class="screen-reader-text"></label>
                        <input type="checkbox" name="archive[]" value="<?php echo $archive->id;?>">
                        <div class="locked-indicator"></div>
                      </th>
                      <td class="name column-name"><strong><?php echo $archive->id;?></strong></td>
                      <td class="name column-name"><span><?php echo $archive->message;?></span></td>
                      <td class="name column-name"><span><?php echo (empty($archive->report))?'':smpush_sendpush::printReport(unserialize($archive->report));?></span></td>
                      <td class="name column-name smpush-center"><?php echo date(self::$wpdateformat, strtotime($archive->starttime));?></td>
                      <td class="name column-name smpush-center"><?php echo (empty($archive->endtime))?'Not finished yet':date(self::$wpdateformat, strtotime($archive->endtime));?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="Delete" onclick="smpush_delete_service(<?php echo $archive->id;?>)" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $archive->id;?>_loading" style="display:none" />
                      </td>
                   </tr>
                <?php }}else{?>
                <tr class="no-items"><td class="colspanchange" colspan="7">No items found.</td></tr>
                <?php }?>
                </tbody>
             </table>
             <div class="tablenav bottom">
        		<div class="alignleft actions bulkactions">
                <select name="doaction2">
                  <option value="0">Bulk Actions</option>
                  <option value="delete">Delete</option>
                </select>
                <input type="submit" name="apply" class="button action" value="Apply">
            	</div>
                <div class="tablenav-pages"><span class="displaying-num"><?php echo self::$paging['result'];?> items</span>
                  <span class="pagination-links">
                  <?php echo paginate_links($paging_args);?>
                  </span>
                </div>
            	<br class="clear">
             </div>
         </div>
      </form>
      </div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
</script>
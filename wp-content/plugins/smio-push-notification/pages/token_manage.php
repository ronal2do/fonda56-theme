<div class="wrap">
   <div id="smpush-icon-tokens" class="icon32"><br></div>
   <h2>Manage Device Token
   <a href="<?php echo $pageurl;?>&remove_duplicates=1&noheader=1" class="add-new-h2 smio-delete" data-confirm="It is highly recommended to take a backup from the table before start, Continue?">Remove Duplicates</a>
   <a href="javascript:" onclick="smpush_open_service(-1,2)" class="add-new-h2">Add New Device</a><img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" />
   </h2>
   <div id="col-container">
      <div id="col-left" style="width: 100%">
      <form action="<?php echo $pageurl;?>" method="get">
      <input type="hidden" name="page" value="<?php echo $pagname;?>" />
      <input type="hidden" name="noheader" value="1" id="smpush-noheader-value" />
         <div class="col-wrap">
          <p class="search-box smpush-canhide">
              <label class="screen-reader-text">Search Posts:</label>
              <input type="search" name="query" value="<?php echo (!empty($_GET['query']))?$_GET['query']:'';?>">
              <input type="submit" id="search-submit" class="button" value="Search Devices">
           </p>
          <div class="tablenav top">
      		<div class="alignleft actions bulkactions smpush-canhide">
                <select name="doaction">
                  <option value="0">Bulk Actions</option>
                  <option value="activate">Activate</option>
                  <option value="deactivate">Deactivate</option>
                  <option value="delete">Delete</option>
                </select>
                <input type="submit" name="apply" class="button action" value="Apply">
                <input type="submit" name="applytoall" class="smpush-applytoall button action" value="Apply to all">
        	</div>
            <div class="alignleft actions smpush-canhide">
              <select name="device_type">
              <option value="0">Show all types</option>
              <option value="<?php echo $types_name->ios_name;?>" <?php if($_GET['device_type'] == $types_name->ios_name) echo 'selected="selected"';?>>iOS</option>
              <option value="<?php echo $types_name->android_name;?>" <?php if($_GET['device_type'] == $types_name->android_name) echo 'selected="selected"';?>>Android</option>
              </select>
              <select name="status">
              <option value="0">Show all status</option>
              <option value="1" <?php if($_GET['status'] == 1) echo 'selected="selected"';?>>Active</option>
              <option value="2" <?php if($_GET['status'] == 2) echo 'selected="selected"';?>>Inactive</option>
              </select>
              <?php if($types_name->dbtype == 'localhost'){?>
              <select name="channel_id" class="postform">
              <option value="0">View all channels</option>
              <?php foreach($channels as $channel){?>
              <option value="<?php echo $channel->id;?>" <?php if($_GET['channel_id'] == $channel->id) echo 'selected="selected"';?>><?php echo $channel->title;?></option>
              <?php }?>
              </select>
              <?php }?>
              <input type="submit" id="post-query-submit" class="button" value="Filter">
            </div>
            <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo self::$paging['result'];?> items</span></div>
        	<br class="clear">
        	</div>
             <table class="wp-list-table widefat fixed tags" cellspacing="0" <?php if(get_bloginfo('version') < 3.8){?>style="table-layout: auto"<?php }?>>
                <thead>
                   <tr>
                      <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
                      <th scope="col" class="manage-column" style="width:110px"><span>ID</span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span>Device Token</span></th>
                      <th scope="col" class="manage-column smpush-center"><span>Device Type</span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span>Information</span></th>
                      <th scope="col" class="manage-column column-categories smpush-center" style="width:75px">Active<span></span></th>
                      <th scope="col" class="manage-column column-categories" style="width:155px">Action<span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
                      <th scope="col" class="manage-column" style="width:110px"><span>ID</span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span>Device Token</span></th>
                      <th scope="col" class="manage-column smpush-center"><span>Device Type</span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span>Information</span></th>
                      <th scope="col" class="manage-column column-categories smpush-center">Active<span></span></th>
                      <th scope="col" class="manage-column column-categories">Action<span></span></th>
                   </tr>
                </tfoot>
                <tbody id="push-token-list">
                <?php if($tokens){$counter = 0;foreach($tokens AS $token){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $token->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <th scope="row" class="check-column">
                        <label class="screen-reader-text"></label>
                        <input type="checkbox" name="device[]" value="<?php echo $token->id;?>">
                        <div class="locked-indicator"></div>
                      </th>
                      <td class="name column-name"><strong><?php echo $token->id;?></strong></td>
                      <td class="name column-name smpush-canhide"><span><?php echo $token->device_token;?></span></td>
                      <td class="name column-name smpush-center"><?php echo $token->device_type;?></td>
                      <td class="name column-name smpush-canhide"><span><?php echo $token->information;?></span></td>
                      <td class="description column-comments smpush-center"><?php echo ($token->active == 1)?'Yes':'No';?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="Edit" onclick="smpush_open_service(<?php echo $token->id;?>,2)" />
                      <input type="button" class="button action smpush-open-btn" value="Delete" onclick="smpush_delete_service(<?php echo $token->id;?>)" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $token->id;?>_loading" style="display:none" />
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
                  <option value="activate">Activate</option>
                  <option value="deactivate">Deactivate</option>
                  <option value="delete">Delete</option>
                </select>
                <input type="submit" name="apply" class="button action" value="Apply">
                <input type="submit" name="applytoall" class="smpush-applytoall button action" value="Apply to all">
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
      <div id="col-right" class="smpush_form_ajax" style="width: 45%;display:none;"></div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
</script>
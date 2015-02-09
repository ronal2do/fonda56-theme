<div class="wrap">
   <div id="smpush-icon-chanmanage" class="icon32"><br></div>
   <h2>Manage Channels<a href="javascript:" onclick="smpush_open_service(-1)" class="add-new-h2">New Channel</a><img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" /></h2>
   <div id="col-container">
      <div id="col-left" style="width: 55%;margin-top: 10px;">
         <div class="col-wrap">
             <table class="wp-list-table widefat fixed tags" cellspacing="0">
                <thead>
                   <tr>
                      <th scope="col" class="manage-column column-posts" style="width:25px"><span>ID</span></th>
                      <th scope="col" class="manage-column"><span>Name</span></th>
                      <th scope="col" class="manage-column"><span>Description</span></th>
                      <th scope="col" class="manage-column"><span>Privacy</span></th>
                      <th scope="col" class="manage-column" style="width:80px"><span>Subscribers</span></th>
                      <th scope="col" class="manage-column column-categories" style="width:75px"><span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" class="manage-column column-posts"><span>ID</span></th>
                      <th scope="col" class="manage-column"><span>Title</span></th>
                      <th scope="col" class="manage-column"><span>Description</span></th>
                      <th scope="col" class="manage-column"><span>Privacy</span></th>
                      <th scope="col" class="manage-column"><span>Subscribers</span></th>
                      <th scope="col" class="manage-column column-categories"><span></span></th>
                   </tr>
                </tfoot>
                <tbody id="the-list" data-wp-lists="list:tag">
                <?php if($channels){$counter = 0;foreach($channels AS $channel){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $channel->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <td class="name column-name"><?php echo $channel->id;?></td>
                      <td class="name column-name"><strong><?php if($channel->default == 1)echo '*';?><?php echo $channel->title;?></strong><br />
                      <div class="row-actions">
                      <?php if($channel->default == 0){?>
                      <span class="edit"><a href="<?php echo $pageurl;?>&default=1&noheader=1&id=<?php echo $channel->id;?>">Default</a></span>
                      <span class="delete"> | <a class="smio-delete" href="<?php echo $pageurl;?>&delete=1&noheader=1&id=<?php echo $channel->id;?>">Delete</a></span>
                      <?php }?>
                      </div>
                      </td>
                      <td class="description column-description"><?php echo $channel->description;?></td>
                      <td class="description column-description"><?php echo ($channel->private == 1)?'Private':'Public';?></td>
                      <td class="description column-description"><?php echo $channel->count;?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="Edit" onclick="smpush_open_service(<?php echo $channel->id;?>)" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $channel->id;?>_loading" style="display:none" />
                      </td>
                   </tr>
                <?php }}else{?>
                <tr class="no-items"><td class="colspanchange" colspan="5">No items found.</td></tr>
                <?php }?>
                </tbody>
             </table>
             <br class="clear">
            <div class="form-wrap">
            <p><strong>Note:</strong><br>For how to subscribe,view or display push channels back to documentation page.</p>
            </div>
         </div>
      </div>
      <div id="col-right" class="smpush_form_ajax" style="width: 45%"></div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
jQuery(document).ready(function() {
    smpush_open_service(-1);
});
</script>
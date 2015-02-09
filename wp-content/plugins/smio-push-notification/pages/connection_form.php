<form action="<?php echo $pageurl;?>&noheader=1" method="post" id="smpush_jform" class="validate">
<input type="hidden" name="id" value="<?php echo $connection['id'];?>">
<input type="hidden" name="type" value="<?php echo $connection['dbtype'];?>">
   <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content" class="edit-form-section">
         <div id="namediv" class="stuffbox">
            <h3><label><?php echo (empty($connection['title']))?'Add New Connection':$connection['title'];?></label></h3>
            <div class="inside">
               <table class="form-table">
                <tbody>
                  <tr valign="top" class="form-required">
                     <td class="first">Title</td>
                     <td><input name="title" type="text" size="40" value="<?php echo $connection['title'];?>" aria-required="true"></td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Description</td>
                     <td><textarea name="description" rows="5" cols="40"><?php echo $connection['description'];?></textarea></td>
                  </tr>
                  <?php if($connection['dbtype'] == ''){?>
                  <tr valign="top" class="form-required">
                     <td class="first">Connection Type</td>
                     <td>
                     <select name="type" onchange="if(this.value=='remote'){$('.smpush_select_conn').show()}else{$('.smpush_select_conn').hide()}">
                        <option value="localhost">Wordpress Database</option>
                        <option value="remote">Remote Connection</option>
                     </select>
                     </td>
                  </tr>
                  <?php }$style = ($connection['dbtype'] == 'remote')?'':'style="display:none;"';?>
                  <tr valign="top" class="form-required smpush_select_conn" <?php echo $style;?>>
                     <td class="first">DB Host</td>
                     <td><input name="dbhost" value="<?php echo $connection['dbhost'];?>" type="text" size="40" aria-required="true"></td>
                  </tr>
                  <tr valign="top" class="form-required smpush_select_conn" <?php echo $style;?>>
                     <td class="first">DB Name</td>
                     <td><input name="dbname" value="<?php echo $connection['dbname'];?>" type="text" size="40" aria-required="true"></td>
                  </tr>
                  <tr valign="top" class="form-required smpush_select_conn" <?php echo $style;?>>
                     <td class="first">DB Username</td>
                     <td><input name="dbuser" value="<?php echo $connection['dbuser'];?>" type="text" size="40" aria-required="true"></td>
                  </tr>
                  <tr valign="top" class="form-required smpush_select_conn" <?php echo $style;?>>
                     <td class="first">DB Password</td>
                     <td><input name="dbpass" value="<?php echo $connection['dbpass'];?>" type="text" size="40" aria-required="true"></td>
                  </tr>
                  <tr valign="top" class="form-required">
                     <td class="first">Table Name</td>
                     <td>
                     <input name="tbname" value="<?php echo $connection['tbname'];?>" type="text" size="40" aria-required="true">
                     <p class="description">Use {wp_prefix} for the Wordpress prefix table value</p>
                	 </td>
                  </tr>
                  <tr valign="top"><th colspan="2">Column Names</th></tr>
                  <tr valign="top" class="form-required">
                     <td class="first">ID</td>
                     <td>
                     <input name="id_name" value="<?php echo $connection['id_name'];?>" type="text" size="40" aria-required="true">
                     <p class="description">The table primary key name</p>
                	 </td>
                  </tr>
                  <tr valign="top" class="form-required">
                     <td class="first">Device Token</td>
                     <td>
                     <input name="token_name" value="<?php echo $connection['token_name'];?>" type="text" size="40" aria-required="true">
                     <p class="description">Table column that stores the devices token value</p>
                	 </td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Active<br />(optional)</td>
                     <td><input name="active_name" value="<?php echo $connection['active_name'];?>" type="text" size="40">
                     <p class="description">Name of the table column that stores the devices status 1 or 0</p></td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Information<br />(optional)</td>
                     <td><input name="info_name" value="<?php echo $connection['info_name'];?>" type="text" size="40">
                     <p class="description">The table column that stores the devices information like device name, version and model</p></td>
                  </tr>
                  <tr valign="top" class="form-required">
                     <td class="first">Device Type</td>
                     <td>
                     <input name="type_name" value="<?php echo $connection['type_name'];?>" type="text" size="40" aria-required="true">
                     <p class="description">Name of the table column that stores the devices type</p>
                	 </td>
                  </tr>
                  <tr valign="top"><th colspan="2">Device Type Values</th></tr>
                  <tr valign="top" class="form-required">
                     <td class="first">IOS</td>
                     <td><input name="ios_name" value="<?php echo $connection['ios_name'];?>" type="text" size="40" aria-required="true"></td>
                  </tr>
                  <tr valign="top" class="form-required">
                     <td class="first">Android</td>
                     <td><input name="android_name" value="<?php echo $connection['android_name'];?>" type="text" size="40" aria-required="true"></td>
                  </tr>
                  <tr valign="top">
                      <td colspan="2"><input type="submit" name="submit" id="smio-submit" class="button button-primary" style="width: 120px;" value="Save Changes">
                      <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_process" alt="" /></td>
                   </tr>
                </tbody>
              </table>
            </div>
         </div>
      </div>
   </div>
</form>
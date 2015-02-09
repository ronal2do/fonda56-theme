<div class="wrap">
<div id="smpush-icon-doc" class="icon32"><br></div>
<h2>Developer Documentation</h2>

<div id="available-widgets" class="widgets-holder-wrap ui-droppable">
  <div class="sidebar-name" style="clear:both;">
    <div class="sidebar-name-arrow"><br></div>
    <h3>Complete list of services</h3>
  </div>
  <div class="widget-holder">
    <select id="smpush_model_select" style="margin-top:8px;margin-left: 10px;">
        <option value="about">Basics</option>
    <?php foreach($document['group'] AS $group=>$title){?>
        <?php foreach($document['links'][$group] AS $method=>$servtitle){?>
        <option value="<?php echo $method;?>"><?php echo $servtitle;?></option>
        <?php }?>
    <?php }?>
    </select>
    <table class="smpush_document smpush_apidesc smpush_method_about" style="margin-top:15px;margin-left: 10px;width:78%;">
        <tr>
            <th>Base URL</th>
            <?php $smpushdocurl = (!empty($smpushexurl['auth_key']))?$smpushexurl['push_basename'].'/?auth_key='.$smpushexurl['auth_key']:$smpushexurl['push_basename'].'/';?>
            <td class="smpush_tdhold"><a href="<?php echo $smpushdocurl;?>" target="_blank"><?php echo $smpushdocurl;?></a></td>
        </tr>
        <tr>
            <th>Direct Base URL</th>
            <?php $directsmapiurl = (!empty($smpushexurl['auth_key']))?site_url().'/index.php?smapicontrol=debug&auth_key='.$smpushexurl['auth_key']:site_url().'/index.php?smapicontrol=debug';?>
            <td class="smpush_tdhold"><a href="<?php echo $directsmapiurl;?>" target="_blank"><?php echo $directsmapiurl;?></a></td>
        </tr>
        <tr>
            <th>Send Type</th>
            <td class="smpush_tdhold">Data can be sent in two methods POST and GET</td>
        </tr>
        <tr>
            <th>Authentication</th>
            <td class="smpush_tdhold">Authentication Key parameter should be sent if enabled in plugin setting</td>
        </tr>
        <tr>
          <th>Response Schema</th>
          <td class="smpush_tdcode smpushtbwbord">
          <table class="smpush_document">
              <tr><td><span>respond</span>Success return 1, and 0 if fails</td></tr>
              <tr><td><span>message</span>Return string message when happens error or success insert, and return empty if there's result</td></tr>
              <tr><td><span>result</span>Return array(s) of data, and empty if there's no result or happened error</td></tr>
          </table>
          </td>
        </tr>
        <tr>
            <th>Output Type</th>
            <td class="smpush_tdhold">JSON</td>
        </tr>
        <tr>
            <th>PHP Version</th>
            <td class="smpush_tdhold">Plugin requires PHP version 5.2.4 or later</td>
        </tr>
        <tr>
            <th>Full Documentation</th>
            <td class="smpush_tdhold">You will find a full documentation for this product <a href="http://smartiolabs.com/product/push-notification-system/documentation/" target="_blank">here</a></td>
        </tr>
        <tr>
            <th>Support</th>
            <td class="smpush_tdhold">We will be happy if you ask us for any help <a href="http://smartiolabs.com/support" target="_blank">Smart IO Labs</a></td>
        </tr>
    </table>

    <?php foreach($document['api'] AS $model=>$api){?>
    <table class="smpush_document smpush_apidesc smpush_method_<?php echo $model;?>" style="margin-top:15px;margin-left: 10px;width:78%;display:none;">
        <tr>
            <th>Request Example</th>
            <?php 
            $api['example'] = (empty($smpushexurl['auth_key']))?str_replace('{api_key}', '', $api['example']):str_replace('{api_key}', 'auth_key='.$smpushexurl['auth_key'].'&', $api['example']);
            $smpushdocurl = $smpushexurl['push_basename'].'/'.$api['example'];
            $smpushdocurl = rtrim($smpushdocurl, '&');
            $smpushdocurl = rtrim($smpushdocurl, '?');
            ?>
            <td class="smpush_tdhold"><a href="<?php echo $smpushdocurl;?>" target="_blank"><?php echo $smpushdocurl;?></a></td>
        </tr>
        <tr>
            <th>Send Type</th>
            <td class="smpush_tdhold">Send parameters in POST or GET is available</td>
        </tr>
        <?php if(!empty($api['note'])){?>
        <tr>
            <th>Note</th>
            <td class="smpush_tdhold"><?php echo $api['note'];?></td>
        </tr>
        <?php }?>
        <?php if(count($api['params']) > 0){?>
        <tr>
            <th>Parameters</th>
            <td class="smpush_td">
            <table class="smpush_document">
            <?php foreach($api['params'] AS $title=>$desc){?>
            <tr>
              <td class="smpush_tdparam"><?php echo $title;?></td>
              <td class="smpush_tdcode">
              <table class="smpush_document">
                  <tr><td><span>Description</span><?php echo $desc['description'];?></td></tr>
                  <tr><td><span>Type</span><?php echo $desc['type'];?></td></tr>
                  <tr><td><span>Required</span><?php if(!empty($desc['requiredtxt'])){echo $desc['requiredtxt'];}elseif($desc['required']){echo 'Yes';}else{echo 'No';}?></td></tr>
              </table>
              </td>
            </tr>
            <?php }?>
            </table>
            </td>
        </tr>
        <?php }?>
        <?php if(count($api['order']) > 0){?>
        <tr>
            <th>Order</th>
            <td class="smpush_td" style="border-top: #e0e0e0 1px solid;">
            <table class="smpush_document">
            <?php foreach($api['order'] AS $title=>$desc){?>
            <tr>
              <td class="smpush_tdparam"><?php echo $title;?></td>
              <td class="smpush_tdcode">
              <table class="smpush_document">
                  <tr><td><span>Description</span><?php echo $desc['description'];?></td></tr>
                  <tr><td><span>Type</span>ASC or DESC</td></tr>
                  <?php if($desc['default']){?>
                  <tr><td><span>Default</span>Default order, orders in <?php echo $desc['type'];?> mode</td></tr>
                  <?php }?>
              </table>
              </td>
            </tr>
            <?php }?>
            </table>
            </td>
        </tr>
        <?php }?>
        <tr>
            <th>Errors</th>
            <td class="smpush_tdhold smpush-errors">
            <?php if(count($api['errors']) > 0){?>
            <?php foreach($api['errors'] AS $error){?>
            <p><?php echo $error;?></p>
            <?php }}?>
            </td>
        </tr>
    </table>
    <?php }?>
  </div>
  <br class="clear">
</div>
</div>
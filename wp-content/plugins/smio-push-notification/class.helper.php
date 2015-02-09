<?php

class smpush_helper {
  public $ParseOutput;
  public static $returnValue;
  public static $staticResult;
  public static $paging;

  public function __construct(){}

  public static function touch_time( $edit = false, $tab_index = 0, $multi = 0 ) {
    global $wp_locale;
    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
    $tab_index_attribute = " tabindex=\"$tab_index\"";

    $time_adj = current_time('timestamp');
    $jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
    $mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
    $aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
    $hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
    $mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
    $ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

    $cur_jj = gmdate( 'd', $time_adj );
    $cur_mm = gmdate( 'm', $time_adj );
    $cur_aa = gmdate( 'Y', $time_adj );
    $cur_hh = gmdate( 'H', $time_adj );
    $cur_mn = gmdate( 'i', $time_adj );

    $month = "<select " . ( $multi ? '' : 'id="mm" ' ) . "name=\"mm\"$tab_index_attribute>\n";
    for ( $i = 1; $i < 13; $i = $i +1 ) {
    $monthnum = zeroise($i, 2);
    $month .= "\t\t\t" . '<option value="' . $monthnum . '"';
    if ( $i == $mm )
    $month .= ' selected="selected"';
    /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
    $month .= '>' . sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
    }
    $month .= '</select>';

    $day = '<input type="text" ' . ( $multi ? '' : 'id="jj" ' ) . 'name="jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
    $year = '<input type="text" ' . ( $multi ? '' : 'id="aa" ' ) . 'name="aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';
    $hour = '<input type="text" ' . ( $multi ? '' : 'id="hh" ' ) . 'name="hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
    $minute = '<input type="text" ' . ( $multi ? '' : 'id="mn" ' ) . 'name="mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';

    echo '<div class="timestamp-wrap">';
    /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
    printf( __( '%1$s %2$s, %3$s @ %4$s : %5$s' ), $month, $day, $year, $hour, $minute );

    echo '</div><input type="hidden" id="ss" name="ss" value="' . $ss . '" />';

    if ( $multi ) return;

    echo "\n\n";
    foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) {
      echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $$timeunit . '" />' . "\n";
      $cur_timeunit = 'cur_' . $timeunit;
      echo '<input type="hidden" id="'. $cur_timeunit . '" name="'. $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
    }
  }

  public static function Paging($sql, $db){
  	if(isset($_REQUEST['perpage'])) $limit = $_REQUEST['perpage'];
  	else $limit = 20;
  	if(isset($_REQUEST['callpage'])) $currentpage = $_REQUEST['callpage'];
  	else $currentpage = 1;

    if(preg_match('/group by ([a-zA-Z0-9`*(),._\n\r]+)\s?/i', $sql, $match)){
      $cselect = 'DISTINCT('.$match[1].')';
      $countsql = preg_replace('/group by ([a-zA-Z0-9`*(),._\n\r\s]+)\s?/i', '', $sql);
    }
    else{
      $cselect = '*';
      $countsql = $sql;
    }
    $countsql = preg_replace('/select ([a-zA-Z0-9`*(),._\n\r\s]+) from/i', 'SELECT COUNT('.$cselect.') FROM', $countsql);
    $count = $db->get_var($countsql);
    if($db->num_rows > 1)
        $count = $db->num_rows;
    if($count == 0)
        return;
  	$pages = $count/$limit;
  	$pages = ceil($pages);

  	if($currentpage < $pages)
  		self::$paging['stillmore'] = 1;
  	else{
  		$currentpage = $pages;
  		self::$paging['stillmore'] = 0;
  	}
  	if($currentpage == 1){
  		self::$paging['previous'] = 0;
  		self::$paging['next'] = $currentpage+1;
  	}
  	elseif($currentpage == $pages){
  		self::$paging['previous'] = $currentpage-1;
  		self::$paging['next'] = 0;
  	}
  	else{
  		self::$paging['previous'] = $currentpage-1;
  		self::$paging['next'] = $currentpage+1;
  	}

    self::$paging['result'] = $count;
    self::$paging['pages'] = $pages;
    self::$paging['perpage'] = $limit;
    self::$paging['page'] = $currentpage;

  	if($currentpage > 0) $currentpage--;
  	$from = $currentpage*$limit;
  	return $sql." LIMIT $from,$limit";
  }

  public function output($respond, $result){
    if(!$this->ParseOutput){
      $this->ParseOutput = true;
      if(is_array($result))
        return $result;
      else
        return array();
    }
    self::jsonPrint($respond, $result);
  }

  public static function jsonPrint($respond, $result){
    $json = array();
  	if(is_array($result)){
  		$json['respond'] = $respond;
        $json['message'] = '';
        $json['result'] = $result;
  	}
  	else{
  		$json['respond'] = $respond;
  		$json['message'] = $result;
        $json['result'] = array();
  	}
    if(self::$returnValue == 'cronjob'){
      if($respond == 0){
        smpush_cronsend::writeLog($json['message']);
        die();
      }
      else{
        return;
      }
    }
    elseif(self::$returnValue){
      self::$staticResult = array('respond'=>$respond, 'result'=>$result);
      return true;
    }
    header('Content-Type: application/json');
  	echo json_encode($json);
  	die();
  }

  public function fetchPrintResult(){
    return self::$staticResult;
  }

  public function queryBuild($sql, $arg){
    if(isset($arg['like'])){
      foreach($arg['like'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index LIKE '$value'";
    }
    if(isset($arg['in'])){
      foreach($arg['in'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index IN ($value)";
    }
    if(isset($arg['notin'])){
      foreach($arg['notin'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index NOT IN ($value)";
    }
    if(isset($arg['between'])){
      foreach($arg['between'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index='$value' BETWEEN $value[0] AND $value[1]";
    }
    if(isset($arg['date'])){
      foreach($arg['date'] AS $tb=>$value){
        foreach($value AS $index=>$key)
            $where[] = "$key[index](".SMPUSHTBPRE."$tb)='$key[value]'";
      }
    }
    if(isset($arg['where'])){
      foreach($arg['where'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index='$value'";
    }
    if(isset($where))
        $where = 'WHERE '.implode(' AND ', $where);
    else
        $where = '';
    if(isset($arg['orderby']))
        $order = 'ORDER BY '.SMPUSHTBPRE.$arg['orderby'].' '.$arg['order'];
    else
        $order = '';
    return str_replace(array('{where}','{order}'), array($where, $order), $sql);
  }

  public function CheckParams($params, $or=false){
    if(! is_array($params)){
        $this->output(0, 'Parameters `'.$params.'` is required');
    }
    $indexes = '';
    foreach($params AS $param){
        if(!isset($_REQUEST[$param]) OR empty($_REQUEST[$param])){
            if($or) $indexes[] = $param;
            else $this->output(0, 'Parameter `'.$param.'` is required, All required parameters are `'.implode($params, '`,`').'`');
        }
        elseif($or) return;
    }
    if($or){
        $this->output(0, 'Parameters `'.implode($params, '`,`').'` at least one of them is required');
    }
  }

  public static function ShortString($string, $charcount){
    $lenght = strlen($string);
    if($lenght > $charcount){
      $string = substr($string, 0, $charcount).'...';
      return $string;
    }
    else{
      return $string;
    }
  }

}

?>
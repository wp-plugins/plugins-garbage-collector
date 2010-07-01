<?php
/* 
 * Plugins Garbage Collector main form
 * 
 */

if (!defined('PGC_PLUGIN_URL')) {
  die;  // Silence is golden, direct call is prohibited
}

require_once('pgc-lib.php');

$shinephpFavIcon = PGC_PLUGIN_URL.'/images/vladimir.png';
$mess = '';

$showScanResults = false;
if (isset($_POST['action']) && $_POST['action']=='scan') {
  $time0 = time();
  $showScanResults = true;
  $tables = pgc_getNotWordPressTables();
  scanPluginsForDbTablesUse(&$tables);
  $time1 = time();
} else if (isset($_POST['deleteTableAction'])) {
  $mess = pgc_deleteUnusedTablesFromDB();
} else if (isset($_POST['deleteExtraColumnsAction'])) {
  $mess = pgc_deleteExtraColumnsFromWPTables();
}

// options page display part

pgc_showMessage($mess);

?>
  <form method="post" action="tools.php?page=plugins-garbage-collector.php" onsubmit="return pgc_onSubmit();">
<?php
    settings_fields('pgc-options');
?>
				<div id="poststuff" class="metabox-holder">					
					<div class="has-sidebar" >
						<div id="post-body-content" class="has-sidebar-content">
<script language="javascript" type="text/javascript">
  function pgc_Actions(action) {
    if (action=='scan') {
      if (document.getElementById('search_nonewp_tables').checked) {
        searchNoneWpTables = 1;
      } else {
        searchNoneWpTables = 0;
      }
      if (document.getElementById('search_wptables_structure_changes').checked) {
        searchWpTablesStructureChanges = 1;
      } else {
        searchWpTablesStructureChanges = 0;
      }
      if (searchNoneWpTables + searchWpTablesStructureChanges == 0) {
        alert('<?php _e('Turn on at least one Search checkbox before start Scan process!','pgc'); ?>');
        return false;
      }
      
      actionTxt = '<?php _e('Scanning', 'pgc'); ?>';
    } else {
      actionTxt = action;
    }
    if (!confirm(actionTxt +' '+'<?php _e('will take some time. Please confirm to continue', 'pgc'); ?>')) {
      return false;
    }
    if (action=='scan') {      
      pgcScanButtonClick();
    } else {
      url = '<?php echo PGC_WP_ADMIN_URL; ?>/tools.php?page=plugins-garbage-collector.php&action='+ action;
      document.location = url;
    }

  }

  function pgc_onSubmit() {
    var checkBoxes = new Array();
    checkBoxes = document.getElementsByTagName('input');
    var checkedFound = false;
    for (var i = 0; i < checkBoxes.length; i++) {
      if (checkBoxes[i].type=='checkbox' && checkBoxes[i].checked) {
        checkedFound = true;
        break;
      }
    }
    if (!checkedFound) {
      alert('<?php _e('Select at least one table before click on Delete button', 'pgc'); ?>');
      return false;
    }
    if (!confirm('<?php _e('Delete database tables last confirmation: Click "Cancel" if you have any doubt.', 'pgc'); ?>')) {
      return false;
    }
  }

</script>
<div class="postbox" style="float: left; width: 100%;">
		<div class="inside">
      <div class="submit" style="padding-top: 10px; text-align:center;">
        <div class="pgc_lm30">
          <input type="radio" name="search_criteria[]" id="search_nonewp_tables" checked="checked" value="1" title="<?php _e('Search DB for tables created by plugins','pgc'); ?>"/>
          <label for="search_nonewp_tables"><?php _e('Search none-WP tables', 'pgc'); ?></label>
        </div>
        <div class="pgc_lm30">
          <input type="radio" name="search_criteria[]" id="search_wptables_structure_changes" value="2" title="<?php _e('Search DB for changes which plugins made to the original WP tables structure','pgc'); ?>"/>
          <label for="search_wptables_structure_changes"><?php _e('Search WP tables structure changes (beta - experimental)', 'pgc'); ?></label>
        </div>
        <div class="pgc_lm30">
          <input type="checkbox" name="show_hidden_tables" id="show_hidden_tables" title="<?php _e('Include tables which are hidden by your request to the search results','pgc'); ?>"/>
          <label for="show_hidden_tables"><?php _e('Show hidden tables', 'pgc'); ?></label>
        </div>

        <div style="float: left; display: inline; margin-bottom:10px;">
          <input type="button" name="scan_db" value="<?php _e('Scan', 'pgc'); ?>" title="<?php _e('Click this button to gather information how plugins use your WordPress database', 'pgc'); ?>" onclick="pgc_Actions('scan');"/>
        </div>
      </div>
      <?php
  echo pgc_displayBoxEnd();
?>
        <img id="ajax_progressbar" class="ajax_processing" src="<?php echo PGC_WP_ADMIN_URL; ?>/images/loading.gif" alt="ajax request processing..." title="AJAX request processing..."/>
        <div id="progressborder" >
          <img src="<?php echo PGC_PLUGIN_URL.'/images/blank.png'; ?>" width="300" height="1"/>          
        <div id="progressbar"></div>
      </div>
      <div id="statusbar"></div>
      <div id="scanresults"></div>
		</div>
		<div id="pgc-about" style="clear: both;">
			<?php echo pgc_displayBoxStart(__('About this Plugin:', 'pgc'), 'float: left; display: block; width: 200px;'); ?>
			<a class="pgc_rsb_link" style="background-image:url(<?php echo $shinephpFavIcon; ?>);" target="_blank" href="http://www.shinephp.com/"><?php _e("Author's website", 'pgc'); ?></a>
			<a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/plugins-garbage-collector-icon.png'; ?>);" target="_blank" href="http://www.shinephp.com/plugins-garbage-collector-wordpress-plugin/"><?php _e('Plugin webpage', 'pgc'); ?></a>
			<a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/changelog-icon.png'; ?>);" target="_blank" href="http://www.shinephp.com/plugins-garbage-collector-wordpress-plugin/#changelog"><?php _e('Changelog', 'pgc'); ?></a>
			<a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/faq-icon.png'; ?>)" target="_blank" href="http://www.shinephp.com/plugins-garbage-collector-wordpress-plugin/#faq"><?php _e('FAQ', 'pgc'); ?></a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/donate-icon.png'; ?>)" target="_blank" href="http://www.shinephp.com/donate"><?php _e('Donate', 'pgc'); ?></a>
<?php 
      echo pgc_displayBoxEnd();
			echo pgc_displayBoxStart(__('Greetings:','pgc'), 'float: left; display: inline; margin-left: 10px; width: 300px;');
?>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo $shinephpFavIcon; ?>);" target="_blank" title="<?php _e("It's me, the author", 'pgc'); ?>" href="http://www.shinephp.com">Vladimir</a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/rubes.png'; ?>);" target="_blank" title="<?php _e("For the help with Chech translation",'pgc');?>" href="http://rubes.eu">Jindřich \"Masterbill\" Rubeš</a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/simon.png'; ?>);" target="_blank" title="<?php _e("For the help with French translation",'pgc');?>" href="http://saymonz.net">Simon</a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/christian.png'; ?>);" target="_blank" title="<?php _e("For the help with German translation",'pgc');?>" href="http://www.irc-junkie.org">Christian</a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/masino.png'; ?>);" target="_blank" title="<?php _e("For the help with Indonesian translation",'pgc');?>" href="http://www.openscriptsolution.com">Masino Sinaga</a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/alessandro.png'; ?>);" target="_blank" title="<?php _e("For the help with Italian translation",'pgc');?>" href="http://technodin.org">Alessandro Mariani</a>
      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/yoichi.png'; ?>);" target="_blank" title="<?php _e("For the help with Japanese translation",'pgc');?>" href="http://www.ad-minister.net">Yoichi</a>
      <a class="pgc_rsb_link" target="_blank" title="<?php _e("For the help with Spanish translation",'pgc');?>" >Melvis E. Leon Lopez</a>
<?php
      _e('Do you wish to see your name with link to your site here? You are welcome! Your help with translation and new ideas are very appreciated.','pgc');
			echo pgc_displayBoxEnd();
      echo pgc_displayBoxStart(__('More plugins from','pgc').' <a href="http://www.shinephp.com" title="ShinePHP.com">ShinePHP.com</a>', 'float: left; display: inline; margin-left: 10px; width: 350px;');
      if (file_exists(ABSPATH.WPINC.'/rss.php')) {
        include_once(ABSPATH.WPINC.'/rss.php');
        $rss = fetch_rss('http://www.shinephp.com/category/shinephp-wordpress-plugins/feed/');
        if ($rss && $rss->items && sizeof($rss->items) > 0) {
          echo '<ul>';
          foreach ((array) $rss->items as $item) {
            $title = htmlentities($item['title'], ENT_QUOTES, "UTF-8");
            $link = $item['link'];
            echo '<li><a href="'.$link.'">'.$title.'</a></li>';
          }
          echo '</ul>';
        } else {
          echo '<ul><li>'.__('No items found.', 'pgc') . '</li></ul>';
        }
        echo '<hr/>';
        echo '<span style="font-size: 12px; font-weight: bold;">Recent Posts:</span><br/>';
        $rss = fetch_rss('http://feeds.feedburner.com/shinephp');
        //$rss = fetch_rss('http://www.shinephp.com/category/shinephp-plugins-news/feed/');
        if ($rss && $rss->items && sizeof($rss->items) > 0) {
          echo '<ul>';
          $rss->items = array_slice($rss->items, 0, 5);
          foreach ((array) $rss->items as $item) {
            $title = htmlentities($item['title'], ENT_QUOTES, "UTF-8");
            $link = $item['link'];
            $date = date('F j, Y', strtotime($item['pubdate']));
            echo '<li><a href="'.$link.'">'.$title.'</a>&ndash; <span class="rss-date">'.$date.'</span></li>';
          }
          echo '</ul>';
        } else {
          echo '<ul><li>'.__('No items found.', 'pgc') . '</li></ul>';
        }
      }
      echo pgc_displayBoxEnd();
?>
						</div>
					</div>
				</div>
    </form>


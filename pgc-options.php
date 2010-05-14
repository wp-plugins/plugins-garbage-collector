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
if (isset($_REQUEST['action']) && $_REQUEST['action']=='scan') {
  $time0 = time();
  $showScanResults = true;
  $tables = pgc_getNotWordPressTables();
  scanPluginsForDbTablesUse(&$tables);
  $time1 = time();
} else if (isset($_POST['deleteTableAction'])) {
  $mess = deleteUnusedTablesFromDB();
}

// options page display part
function pgc_displayBoxStart($title) {
?>
			<div class="postbox" style="float: left;">
				<h3 style="cursor:default;"><span><?php echo $title ?></span></h3>
				<div class="inside">
<?php
}
// 	end of thanks_displayBoxStart()

function pgc_displayBoxEnd() {
?>
				</div>
			</div>
<?php
}
// end of thanks_displayBoxEnd()


pgc_showMessage($mess);

?>
  <form method="post" action="tools.php?page=plugins-garbage-collector.php" onsubmit="return pgc_onSubmit();">
<?php
    settings_fields('pgc-options');
?>
				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div class="inner-sidebar" >
						<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
									<?php pgc_displayBoxStart(__('About this Plugin:', 'pgc')); ?>
											<a class="pgc_rsb_link" style="background-image:url(<?php echo $shinephpFavIcon; ?>);" target="_blank" href="http://www.shinephp.com/"><?php _e("Author's website", 'pgc'); ?></a>
											<a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/plugins-garbage-collector-icon.png'; ?>" target="_blank" href="http://www.shinephp.com/plugins-garbage-collector-wordpress-plugin/"><?php _e('Plugin webpage', 'pgc'); ?></a>
											<a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/changelog-icon.png'; ?>);" target="_blank" href="http://www.shinephp.com/plugins-garbage-collector-wordpress-plugin/#changelog"><?php _e('Changelog', 'pgc'); ?></a>
											<a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/faq-icon.png'; ?>)" target="_blank" href="http://www.shinephp.com/plugins-garbage-collector-wordpress-plugin/#faq"><?php _e('FAQ', 'pgc'); ?></a>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/donate-icon.png'; ?>)" target="_blank" href="http://www.shinephp.com/donate"><?php _e('Donate', 'pgc'); ?></a>
									<?php pgc_displayBoxEnd(); ?>
									<?php pgc_displayBoxStart(__('Greetings:','pgc')); ?>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo $shinephpFavIcon; ?>);" target="_blank" title="<?php _e("It's me, the author", 'pgc'); ?>" href="http://www.shinephp.com">Vladimir</a>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/simon.png'; ?>);" target="_blank" title="<?php _e("For the help with French translation",'pgc');?>" href="http://saymonz.net">Simon</a>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/christian.png'; ?>);" target="_blank" title="<?php _e("For the help with German translation",'pgc');?>" href="http://www.irc-junkie.org">Christian</a>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/masino.png'; ?>);" target="_blank" title="<?php _e("For the help with Indonesian translation",'pgc');?>" href="http://www.openscriptsolution.com">Masino Sinaga</a>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/alessandro.png'; ?>);" target="_blank" title="<?php _e("For the help with Italian translation",'pgc');?>" href="http://technodin.org">Alessandro Mariani</a>
                      <a class="pgc_rsb_link" style="background-image:url(<?php echo PGC_PLUGIN_URL.'/images/yoichi.png'; ?>);" target="_blank" title="<?php _e("For the help with Japanese translation",'pgc');?>" href="http://www.ad-minister.net">Yoichi</a>
                      <a class="pgc_rsb_link" target="_blank" title="<?php _e("For the help with Spanish translation",'pgc');?>" >Melvis E. Leon Lopez</a>
											<?php _e('Do you wish to see your name with link to your site here? You are welcome! Your help with translation and new ideas are very appreciated.','pgc'); ?>
									<?php pgc_displayBoxEnd(); ?>
						</div>
					</div>
					<div class="has-sidebar" >
						<div id="post-body-content" class="has-sidebar-content">
<script language="javascript" type="text/javascript">
  function pgc_Actions(action) {
    if (action=='scan') {
      actionTxt = '<?php _e('Scanning', 'pgc'); ?>';
    } else {
      actionTxt = action;
    }
    if (!confirm(actionTxt +' '+'<?php _e('will take some time. Please confirm to continue', 'pgc'); ?>')) {
      return false;
    }
    url = '<?php echo PGC_WP_ADMIN_URL; ?>/tools.php?page=plugins-garbage-collector.php&action='+ action;
    document.location = url;

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
<?php
  	pgc_displayBoxStart(__('Click &lt;Scan&gt; to gather information how plugins use your WordPress database', 'pgc'));
?>

      <div class="submit" style="padding-top: 10px; text-align:center;">
          <input type="button" name="scan" value="<?php _e('Scan', 'pgc'); ?>" title="<?php _e('Scan', 'pgc'); ?>" onclick="pgc_Actions('scan');"/>
      </div>
<?php
  pgc_displayBoxEnd();
?>

<?php

function displayColumnHeaders() {
?>
            <tr>
              <th><?php _e('Table Name','pgc'); ?></th>
              <th><?php _e('Records #','pgc'); ?></th>
              <th><?php _e('KBytes #','pgc'); ?></th>
              <th><?php _e('Plugin Name','pgc'); ?></th>
              <th><?php _e('Plugin State','pgc'); ?></th>
            </tr>
<?php
}
// end of displayColumnHeaders()

  if ($showScanResults) {
  	_e('Let\'s see what tables in your database do not belong to the core WordPress installation:', 'pgc');
    if (count($tables)>0) {
      //echo ' '.number_format($time1-$time0, 2).' sec.';
?>
        <table class="widefat" style="clear:none;" cellpadding="0" cellspacing="0">
          <thead>
<?php
  displayColumnHeaders();
?>
          </thead>
          <tbody>
<?php
    $showDeleteTablesButton = false;
    $i = 0;
    foreach ($tables as $table) {
      if ($i & 1) {
        $rowClass = 'class="alternate"';
      } else {
        $rowClass = '';
      }
      $i++;
?>
          <tr <?php echo $rowClass; ?> >
            <td style="vertical-align:top;width:100px;" >
<?php
  $deleteCheckBox = '';
  if (!$table->plugin_name) {
    $color = 'red';
    $deleteCheckBox = '<input type="checkbox" name="delete_'.$table->name.'" />';
    $showDeleteTablesButton = true;
  } else if ($table->plugin_state=='active') {
    $color = 'green';
  } else {
    $color = 'blue';
  }
echo $deleteCheckBox.' <span style="color:'.$color.';">'.$table->name.'</span>';

?>
            </td>
            <td>
              <?php echo '<span style="color:'.$color.';">'.$table->records.'</span>'; ?>
            </td>
            <td>
              <?php echo '<span style="color:'.$color.';">'.$table->kbytes.'</span>'; ?>
            </td>
            <td>
<?php 
  if ($table->plugin_name) {
    echo '<span style="color:'.$color.';">'.$table->plugin_name.'</span>';
  } else {
?>
            <span style="color:red;">unknown</span>
<?php
  }
?>
            </td>
            <td>
              <?php echo '<span style="color:'.$color.';">'.$table->plugin_state.'</span>'; ?>
            </td>
          </tr>
<?php
    }
?>
          </tbody>
          <tfoot>
<?php
  displayColumnHeaders();
?>
          </tfoot>
      </table>                                             
<?php
      if ($showDeleteTablesButton) {
?>
      <table>
        <tr>
          <td>
            <div class="submit">
              <input type="submit" name="deleteTableAction" value="<?php _e('Delete Tables', 'pgc'); ?>"/>
            </div>
          </td>
          <td>
            <div style="padding-left: 10px;"><span style="color: red; font-weight: bold;"><?php _e('Attention!','pgc'); ?></span> <?php _e('Operation rollback is not possible. Consider to make database backup first. Please double think before click <code>Delete Tables</code> button.','pgc'); ?></div>
          </td>
        </tr>
      </table>
<?php
      }
    } else {
      pgc_displayBoxStart();
?>
<span style="color: green; text-align: center; font-size: 1.2em;">
    <?php _e('Congratulations! It seems that your WordPress database is clean.','pgc'); ?>
</span>
<?php
      pgc_displayBoxEnd();
    }
  }
?>

						</div>
					</div>
				</div>
    </form>


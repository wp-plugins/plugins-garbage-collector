<?php
/* 
 * * User Role Editor plugin Lirary general staff
 * Author: Vladimir Garagulya vladimir@shinephp.com
 * 
 */


if (!defined("WPLANG")) {
  die;  // Silence is golden, direct call is prohibited
}

$pgc_siteURL = get_option( 'siteurl' );

// Pre-2.6 compatibility
if ( !defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', $thanks_siteURL . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

$pgcPluginDirName = substr(dirname(__FILE__), strlen(WP_PLUGIN_DIR) + 1, strlen(__FILE__) - strlen(WP_PLUGIN_DIR)-1);

define('PGC_PLUGIN_URL', WP_PLUGIN_URL.'/'.$pgcPluginDirName);
define('PGC_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.$pgcPluginDirName);
define('PGC_WP_ADMIN_URL', $pgc_siteURL.'/wp-admin');
define('PGC_ERROR', 'Error is encountered');


global $wpdb;


function pgc_logEvent($message, $showMessage = false) {
  include(ABSPATH .'wp-includes/version.php');

  $fileName = PGC_PLUGIN_DIR.'/plugins-garbage-collector.log';
  $fh = fopen($fileName,'a');
  $cr = "\n";
  $s = $cr.date("d-m-Y H:i:s").$cr.
      'WordPress version: '.$wp_version.', PHP version: '.phpversion().', MySQL version: '.mysql_get_server_info().$cr;
  fwrite($fh, $s);
  fwrite($fh, $message.$cr);
  fclose($fh);

  if ($showMessage) {
    pgc_showMessage('Error is occur. Please check the log file.');
  }
}
// end of pgc_logEvent()


function pgc_showMessage($message) {

  if ($message) {
    echo '<div class="updated" style="margin:0;">'.$message.'</div><br style="clear: both;"/>';
  }

}
// end of pgc_showMessage()


function pgc_getNotWordPressTables() {
  global $wpdb;

  $wpTables = array('users', 'usermeta', 'posts', 'categories', 'post2cat', 'comments', 'links', 'link2cat', 'options',
                        'postmeta', 'terms', 'term_taxonomy', 'term_relationships', 'commentmeta');

  $non_wp_tables = array();
  $query = "show tables";
  $existingTables = $wpdb->get_col($query);
  foreach ($existingTables as $existingTable) {
    $existingTable = strtolower($existingTable);
    $legalWPTable = false;
    foreach ($wpTables as $wpTable) {
      if ($wpdb->prefix.$wpTable==$existingTable) {
        $legalWPTable = true;
        break;
      }
    }
    if (!$legalWPTable && strpos($existingTable, $wpdb->prefix, 0)!==false) {
      $query = "SELECT table_rows, ROUND((data_length + index_length)/1024,2) as kbytes
                  FROM information_schema.TABLES
                  WHERE information_schema.TABLES.table_schema='".DB_NAME."' and
                        information_schema.TABLES.table_name='$existingTable'
                  limit 0, 1";
      $result = $wpdb->get_results($query);
      $non_wp_table = new stdClass;
      $non_wp_table->name = $existingTable;
      $non_wp_table->name_without_prefix = str_replace($wpdb->prefix, '', $existingTable);
      $non_wp_table->records = $result[0]->table_rows;
      $non_wp_table->kbytes = $result[0]->kbytes;
      $non_wp_table->plugin_name = '';
      $non_wp_table->plugin_file = '';
      $non_wp_table->plugin_state = '';
      $non_wp_tables[] = $non_wp_table;
    }
  }

  return $non_wp_tables;
}
// end of pgc_getUnusedTables()


function scanPluginsForDbTablesUse(&$tables) {
  global $wpdb;


  $plugins_list = get_plugins();
  foreach ($plugins_list as $key=>$plugin) {
    $plugin_files = get_plugin_files($key);
    foreach ($plugin_files as $plugin_file) {
      $ext = pathinfo($plugin_file, PATHINFO_EXTENSION);
      if ($ext=='php' || $ext=='PHP') {
        $fh = fopen(WP_PLUGIN_DIR.'/'.$plugin_file, 'r');
        if (!fh) {
          continue;
        }
        while (!feof($fh)) {
          $s = fgets($fh);
          $s = strtolower($s);
          $pluginFound = false;
          foreach ($tables as $table) {
            if (!$table->plugin_name && (strpos($s, $table->name_without_prefix)!==false)) {
              $table->plugin_name = $plugin['Title'].' '.$plugin['Version'];
              $table->plugin_file = $key;
            }
          }
        }
        fclose($fh);
      }
    }
  }

  $active_plugins = get_option('active_plugins');
  foreach ($tables as $table) {
    if ($table->plugin_file) {
      $pluginActive = false;
      foreach ($active_plugins as $active_plugin) {
        if ($table->plugin_file==$active_plugin) {
          $pluginActive = true;
          break;
        }
      }
      if ($pluginActive) {
        $table->plugin_state = 'active';
      } else {
        $table->plugin_state = 'deactivated';
      }
    } else {
      $table->plugin_state = 'deleted?';
    }
    $table->plugin_state = $table->plugin_state;
  }
}
// end of scanPluginsForDbTables()


function deleteUnusedTablesFromDB() {
  global $wpdb;

  $tablesToDelete = array();
  foreach ($_POST as $key=>$value) {
    if (strpos($key, 'delete_')!==false) {
      $tablesToDelete[] = substr($key, 7);
    }
  }
  if (count($tablesToDelete)==0) {
    return;
  }

  $dbName = DB_NAME;
  $actionResult = '';
  foreach($tablesToDelete as $tableToDelete) {
    if ($actionResult) {
      $actionResult .= ', ';
    }
    $query = "drop table `$dbName`.`$tableToDelete`";
    $wpdb->query($query);
    if ($wpdb->last_error) {
      if ($actionResult) {
        $actionResult = 'Tables are deleted: '.$actionResult;
      }
      return $actionResult.'<br/>'.$wpdb->last_error;
    }
    $actionResult .= ' '.$tableToDelete;
  }

  return 'Tables are deleted successfully: '.$actionResult;
}
// end of deleteUnusedTablesFromDB()

?>

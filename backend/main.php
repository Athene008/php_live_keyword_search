<?php
ini_set('include_path', '../');
require('./core.php');

$best_win_apps = new BestWinApps;

if ($_GET['mode'] == 'load') {
    $result = $best_win_apps->more_load($_GET['keyword'], $_GET['count']);
} else {
    $result = $best_win_apps->save_stats_clicks($_GET['app_id'], $_GET['clicked'], $_GET['keyword']);
}

echo json_encode($result);
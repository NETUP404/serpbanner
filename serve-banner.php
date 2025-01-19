<?php
if (!isset($_GET['user_id'])) {
    exit('No user ID provided.');
}

$user_id = intval($_GET['user_id']);

require_once('../../../wp-load.php'); // Cargar WordPress

global $wpdb;
$table_name = $wpdb->prefix . 'bes_banners';

$banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d AND approved = 1", $user_id));

if (!$banner) {
    exit('Banner not found or not approved.');
}

$ip = $_SERVER['REMOTE_ADDR'];
$impression_cookie = "bes_impression_$user_id";
$click_cookie = "bes_click_$user_id";

if (!isset($_COOKIE[$impression_cookie])) {
    setcookie($impression_cookie, $ip, time() + 3600 * 24, "/");
    $wpdb->query("UPDATE $table_name SET impressions = impressions + 1 WHERE user_id = $user_id");
}

if (isset($_GET['click'])) {
    if (!isset($_COOKIE[$click_cookie])) {
        setcookie($click_cookie, $ip, time() + 3600 * 24, "/");
        $wpdb->query("UPDATE $table_name SET clicks = clicks + 1, credits = credits + 20 WHERE user_id = $user_id");
    }
    header("Location: " . esc_url($banner->target_url));
    exit;
}

header("Content-Type: application/javascript");
?>
document.write('<a href="<?php echo esc_url(add_query_arg(array('user_id' => $user_id, 'click' => 1), site_url('/wp-content/plugins/banner-exchange/serve-banner.php'))); ?>" target="_blank"><img src="<?php echo esc_url($banner->banner_url); ?>" alt="Banner"></a>');

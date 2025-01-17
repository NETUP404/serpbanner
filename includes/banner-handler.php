<?php

// Mostrar el formulario de envío de banner
function bes_display_submit_banner_form() {
    if ( isset( $_POST['bes_submit'] ) && check_admin_referer('bes_submit_banner_nonce', 'bes_nonce') ) {
        bes_handle_banner_submission();
    }
    
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('bes_submit_banner_nonce', 'bes_nonce'); ?>
        <label for="banner_url">URL del Banner:</label>
        <input type="text" name="banner_url" id="banner_url" required>
        
        <label for="target_url">URL de Destino:</label>
        <input type="url" name="target_url" id="target_url" required>
        
        <input type="submit" name="bes_submit" value="Enviar Banner">
    </form>
    <?php
    return ob_get_clean();
}

// Manejar la presentación del formulario de banner
function bes_handle_banner_submission() {
    if ( ! is_user_logged_in() ) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';
    
    $user_id = get_current_user_id();
    $banner_url = sanitize_text_field( $_POST['banner_url'] );
    $target_url = esc_url( $_POST['target_url'] );

    $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'banner_url' => $banner_url,
            'target_url' => $target_url,
            'impressions' => 0,
            'clicks' => 0,
            'credits' => 0,
            'approved' => 0
        ]
    );

    echo '<p>Banner enviado correctamente. Espera la aprobación del administrador.</p>';
}

// Rastrear impresiones de banner
function bes_track_impression($banner_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';

    $ip = $_SERVER['REMOTE_ADDR'];
    $cookie_name = "bes_impression_$banner_id";

    if (!isset($_COOKIE[$cookie_name])) {
        setcookie($cookie_name, $ip, time() + 3600 * 24, "/");
        $wpdb->query("UPDATE $table_name SET impressions = impressions + 1 WHERE id = $banner_id");
    }
}

// Rastrear clics de banner
function bes_track_click($banner_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';

    $ip = $_SERVER['REMOTE_ADDR'];
    $cookie_name = "bes_click_$banner_id";

    if (!isset($_COOKIE[$cookie_name])) {
        setcookie($cookie_name, $ip, time() + 3600 * 24, "/");
        $wpdb->query("UPDATE $table_name SET clicks = clicks + 1, credits = credits + 20 WHERE id = $banner_id");
    }
}

// Mostrar el banner
function bes_display_banner($banner_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';

    $banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $banner_id));

    if ($banner && $banner->approved) {
        bes_track_impression($banner_id);

        echo '<a href="' . esc_url(add_query_arg(['id' => $banner_id, 'click' => 1], admin_url('admin-ajax.php'))) . '" target="_blank" onclick="bes_track_click(' . $banner_id . ')">';
        echo '<img src="' . esc_url($banner->banner_url) . '" alt="Banner">';
        echo '</a>';
    }
}

// Incluir el script de clic de banner
function bes_track_click_script() {
    ?>
    <script type="text/javascript">
        function bes_track_click(banner_id) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo admin_url('admin-ajax.php'); ?>", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send("action=bes_track_click&banner_id=" + banner_id);
        }
    </script>
    <?php
}
add_action('wp_footer', 'bes_track_click_script');

// Manejar la solicitud AJAX de clic de banner
function bes_track_click_ajax() {
    if (isset($_POST['banner_id'])) {
        bes_track_click(intval($_POST['banner_id']));
    }
    wp_die();
}
add_action('wp_ajax_bes_track_click', 'bes_track_click_ajax');
add_action('wp_ajax_nopriv_bes_track_click', 'bes_track_click_ajax');

// Registrar el shortcode para mostrar el banner
function bes_register_banner_display_shortcode() {
    add_shortcode('bes_display_banner', 'bes_display_banner_shortcode');
}

// Función del shortcode para mostrar el banner
function bes_display_banner_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'bes_display_banner');

    ob_start();
    bes_display_banner($atts['id']);
    return ob_get_clean();
}

add_action('init', 'bes_register_banner_display_shortcode');
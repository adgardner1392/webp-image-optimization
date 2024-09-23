<?php
/*
Plugin Name: WebP Image Optimization
Plugin URI: https://example.com/plugins/webp-image-optimization/
Description: Automatically converts uploaded images to WebP format and resizes them.
Version: 1.1.1
Author: Adam Gardner
Author URI: https://github.com/adgardner1392
License: GPLv2 or later
Text Domain: webp-image-optimization
Domain Path: /languages
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin textdomain for translations.
 */
function webp_image_optimization_load_textdomain() {
    load_plugin_textdomain( 'webp-image-optimization', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'webp_image_optimization_load_textdomain' );

/**
 * Enqueue admin scripts and styles
 */
function webp_image_optimization_enqueue_admin_assets( $hook ) {
    // Check if we are on the plugin's settings page
    if ( $hook !== 'tools_page_webp-image-optimization' ) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'webp-image-optimization-admin',
        plugin_dir_url( __FILE__ ) . 'css/admin.css',
        array(),
        '1.0'
    );

    // Enqueue JS
    wp_enqueue_script(
        'webp-image-optimization-admin',
        plugin_dir_url( __FILE__ ) . 'js/admin.js',
        array(),
        '1.0',
        true
    );
}
add_action( 'admin_enqueue_scripts', 'webp_image_optimization_enqueue_admin_assets' );

/**
 * Add settings page
 */
function webp_image_optimization_add_settings_page() {
    add_submenu_page(
        'tools.php', // Parent slug
        __( 'WebP Image Optimization Settings', 'webp-image-optimization' ), // Page title
        __( 'WebP Image Optimization', 'webp-image-optimization' ), // Menu title
        'manage_options', // Capability
        'webp-image-optimization', // Menu slug
        'webp_image_optimization_render_settings_page' // Callback function
    );
}
add_action( 'admin_menu', 'webp_image_optimization_add_settings_page' );

/**
 * Register settings
 */
function webp_image_optimization_register_settings() {
    register_setting(
        'webp_image_optimization_settings_group',
        'webp_image_optimization_settings',
        'webp_image_optimization_sanitize_settings' // Sanitize callback
    );

    add_settings_section(
        'webp_image_optimization_settings_section',
        __( 'Resize and Conversion Settings', 'webp-image-optimization' ),
        'webp_image_optimization_settings_section_callback',
        'webp_image_optimization_settings'
    );

    // Maximum Width field
    add_settings_field(
        'max_width',
        __( 'Maximum Width (px)', 'webp-image-optimization' ),
        'webp_image_optimization_max_width_render',
        'webp_image_optimization_settings',
        'webp_image_optimization_settings_section'
    );

    // Maximum Height field
    add_settings_field(
        'max_height',
        __( 'Maximum Height (px)', 'webp-image-optimization' ),
        'webp_image_optimization_max_height_render',
        'webp_image_optimization_settings',
        'webp_image_optimization_settings_section'
    );

    // JPEG Quality field
    add_settings_field(
        'jpeg_quality',
        __( 'JPEG Quality (0-100)', 'webp-image-optimization' ),
        'webp_image_optimization_jpeg_quality_render',
        'webp_image_optimization_settings',
        'webp_image_optimization_settings_section'
    );

    // PNG Compression Level field
    add_settings_field(
        'png_compression',
        __( 'PNG Compression Level (0-9)', 'webp-image-optimization' ),
        'webp_image_optimization_png_compression_render',
        'webp_image_optimization_settings',
        'webp_image_optimization_settings_section'
    );

    // Don't Convert JPEG checkbox
    add_settings_field(
        'dont_convert_jpeg',
        __( 'Don\'t convert JPEG images to WebP format', 'webp-image-optimization' ),
        'webp_image_optimization_dont_convert_jpeg_render',
        'webp_image_optimization_settings',
        'webp_image_optimization_settings_section'
    );

    // Don't Convert PNG checkbox
    add_settings_field(
        'dont_convert_png',
        __( 'Don\'t convert PNG images to WebP format', 'webp-image-optimization' ),
        'webp_image_optimization_dont_convert_png_render',
        'webp_image_optimization_settings',
        'webp_image_optimization_settings_section'
    );

}
add_action( 'admin_init', 'webp_image_optimization_register_settings' );

/**
 * Sanitize settings input
 */
function webp_image_optimization_sanitize_settings( $input ) {
    $output = array();

    // Sanitize Maximum Width
    if ( isset( $input['max_width'] ) ) {
        $output['max_width'] = intval( $input['max_width'] );
        if ( $output['max_width'] <= 0 ) {
            $output['max_width'] = 1500; // Default value
        }
    }

    // Sanitize Maximum Height
    if ( isset( $input['max_height'] ) ) {
        $output['max_height'] = intval( $input['max_height'] );
        if ( $output['max_height'] <= 0 ) {
            $output['max_height'] = 1500; // Default value
        }
    }

    // Sanitize JPEG Quality
    if ( isset( $input['jpeg_quality'] ) ) {
        $jpeg_quality = intval( $input['jpeg_quality'] );
        if ( $jpeg_quality < 0 || $jpeg_quality > 100 ) {
            $jpeg_quality = 90; // Default value
        }
        $output['jpeg_quality'] = $jpeg_quality;
    } else {
        $output['jpeg_quality'] = 90; // Default value
    }

    // Sanitize PNG Compression Level
    if ( isset( $input['png_compression'] ) ) {
        $png_compression = intval( $input['png_compression'] );
        if ( $png_compression < 0 || $png_compression > 9 ) {
            $png_compression = 6; // Default value
        }
        $output['png_compression'] = $png_compression;
    } else {
        $output['png_compression'] = 6; // Default value
    }

    // Sanitize Don't Convert JPEG checkbox
    $output['dont_convert_jpeg'] = isset( $input['dont_convert_jpeg'] ) && $input['dont_convert_jpeg'] == '1' ? true : false;

    // Sanitize Don't Convert PNG checkbox
    $output['dont_convert_png'] = isset( $input['dont_convert_png'] ) && $input['dont_convert_png'] == '1' ? true : false;

    return $output;
}

/**
 * Settings section callback
 */
function webp_image_optimization_settings_section_callback() {
    echo '<p>' . esc_html__( 'Set the maximum dimensions for images, specify image quality/compression, and select which image types you do not want to convert to WebP. Images larger than the specified dimensions will be resized upon upload.', 'webp-image-optimization' ) . '</p>';
}

/**
 * Render Maximum Width field
 */
function webp_image_optimization_max_width_render() {
    $options = get_option( 'webp_image_optimization_settings' );
    $max_width = isset( $options['max_width'] ) ? esc_attr( $options['max_width'] ) : '';
    echo '<input type="number" name="webp_image_optimization_settings[max_width]" value="' . esc_attr( $max_width ) . '" />';
}

/**
 * Render Maximum Height field
 */
function webp_image_optimization_max_height_render() {
    $options = get_option( 'webp_image_optimization_settings' );
    $max_height = isset( $options['max_height'] ) ? esc_attr( $options['max_height'] ) : '';
    echo '<input type="number" name="webp_image_optimization_settings[max_height]" value="' . esc_attr( $max_height ) . '" />';
}

/**
 * Render JPEG Quality field
 */
function webp_image_optimization_jpeg_quality_render() {
    $options = get_option( 'webp_image_optimization_settings' );
    $jpeg_quality = isset( $options['jpeg_quality'] ) ? esc_attr( $options['jpeg_quality'] ) : '90';
    ?>
    <div class="webp-settings__field webp-settings__field--jpeg-quality">
        <input type="range" class="webp-settings__slider" id="jpeg_quality_range" value="<?php echo esc_attr( $jpeg_quality ); ?>" min="0" max="100" />
        <input type="number" class="webp-settings__input" id="jpeg_quality_number" name="webp_image_optimization_settings[jpeg_quality]" value="<?php echo esc_attr( $jpeg_quality ); ?>" min="0" max="100" />
        <span class="webp-settings__value" id="jpeg_quality_value"><?php echo esc_html( $jpeg_quality ); ?></span>
    </div>
    <?php
}


/**
 * Render PNG Compression Level field
 */
function webp_image_optimization_png_compression_render() {
    $options = get_option( 'webp_image_optimization_settings' );
    $png_compression = isset( $options['png_compression'] ) ? esc_attr( $options['png_compression'] ) : '6';
    ?>
    <div class="webp-settings__field webp-settings__field--png-compression">
        <input type="range" class="webp-settings__slider" id="png_compression_range" value="<?php echo esc_attr( $png_compression ); ?>" min="0" max="9" />
        <input type="number" class="webp-settings__input" id="png_compression_number" name="webp_image_optimization_settings[png_compression]" value="<?php echo esc_attr( $png_compression ); ?>" min="0" max="9" />
        <span class="webp-settings__value" id="png_compression_value"><?php echo esc_attr( $png_compression ); ?></span>
    </div>
    <?php
}

/**
 * Render Don't Convert JPEG checkbox
 */
function webp_image_optimization_dont_convert_jpeg_render() {
    $options = get_option( 'webp_image_optimization_settings' );
    $dont_convert_jpeg = isset( $options['dont_convert_jpeg'] ) ? $options['dont_convert_jpeg'] : '';
    echo '<input type="checkbox" name="webp_image_optimization_settings[dont_convert_jpeg]" value="1"' . checked( 1, $dont_convert_jpeg, false ) . ' />';
}

/**
 * Render Don't Convert PNG checkbox
 */
function webp_image_optimization_dont_convert_png_render() {
    $options = get_option( 'webp_image_optimization_settings' );
    $dont_convert_png = isset( $options['dont_convert_png'] ) ? $options['dont_convert_png'] : '';
    echo '<input type="checkbox" name="webp_image_optimization_settings[dont_convert_png]" value="1"' . checked( 1, $dont_convert_png, false ) . ' />';
}

/**
 * Render settings page
 */
function webp_image_optimization_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'WebP Image Optimization Settings', 'webp-image-optimization' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            // These functions already handle escaping internally.
            settings_fields( 'webp_image_optimization_settings_group' );
            do_settings_sections( 'webp_image_optimization_settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Hook into file upload to process images.
 */
add_filter( 'wp_handle_upload', 'webp_image_optimization_handle_upload', 10, 2 );

function webp_image_optimization_handle_upload( $upload, $context ) {
    $file_path = $upload['file'];

    // Get the settings
    $options = get_option( 'webp_image_optimization_settings' );
    $max_width       = isset( $options['max_width'] ) ? intval( $options['max_width'] ) : 1500; // Default to 1500 if not set
    $max_height      = isset( $options['max_height'] ) ? intval( $options['max_height'] ) : 1500; // Default to 1500 if not set

    // Resize the image
    $resized_image = webp_image_optimization_resize_image( $file_path, $max_width, $max_height ); // Use settings values
    if ( $resized_image ) {
        $file_path = $resized_image;
    }

    // Convert to WebP format
    $webp_file = webp_image_optimization_convert_to_webp( $file_path );
    if ( $webp_file ) {
        // Update $upload array to point to the WebP file
        $upload['file'] = $webp_file;

        // Get the URL corresponding to the WebP file
        $upload_dir     = wp_upload_dir();
        $relative_path  = str_replace( $upload_dir['basedir'], '', $webp_file );
        $upload['url']  = $upload_dir['baseurl'] . $relative_path;

        // Update MIME type
        $upload['type'] = 'image/webp';
    }

    return $upload;
}

/**
 * Convert image to WebP format.
 */
function webp_image_optimization_convert_to_webp( $file ) {
    $file_info = pathinfo( $file );
    $extension = strtolower( $file_info['extension'] );

    // Only convert if it's a supported image format
    if ( ! in_array( $extension, array( 'jpeg', 'jpg', 'png' ), true ) ) {
        return false;
    }

    // Get settings
    $options = get_option( 'webp_image_optimization_settings' );

    // Check if conversion for this type is disabled
    switch ( $extension ) {
        case 'jpeg':
        case 'jpg':
            if ( ! empty( $options['dont_convert_jpeg'] ) ) {
                return false; // Skip conversion
            }
            $image = imagecreatefromjpeg( $file );
            break;
        case 'png':
            if ( ! empty( $options['dont_convert_png'] ) ) {
                return false; // Skip conversion
            }
            $image = imagecreatefrompng( $file );
            break;
        default:
            return false;
    }

    if ( ! $image ) {
        return false;
    }

    // Define the path for the WebP file
    $webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';

    // Convert the image to WebP and save
    if ( imagewebp( $image, $webp_file, 80 ) ) { // Quality set to 80
        imagedestroy( $image );
        return $webp_file;
    }

    imagedestroy( $image );
    return false;
}

/**
 * Resize image to specified dimensions.
 */
function webp_image_optimization_resize_image( $file, $max_width, $max_height ) {
    $image_info = getimagesize( $file );
    $width      = $image_info[0];
    $height     = $image_info[1];
    $mime_type  = $image_info['mime'];

    if ( $width <= $max_width && $height <= $max_height ) {
        return $file; // No resizing needed
    }

    // Calculate aspect ratio and new dimensions
    $aspect_ratio = $width / $height;
    if ( $width > $height ) {
        $new_width  = $max_width;
        $new_height = $max_width / $aspect_ratio;
    } else {
        $new_width  = $max_height * $aspect_ratio;
        $new_height = $max_height;
    }

    $new_width  = intval( $new_width );
    $new_height = intval( $new_height );

    $image_resized = imagecreatetruecolor( $new_width, $new_height );

    // Load the original image
    switch ( $mime_type ) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg( $file );
            break;
        case 'image/png':
            $image = imagecreatefrompng( $file );
            // Preserve transparency
            imagealphablending( $image_resized, false );
            imagesavealpha( $image_resized, true );
            break;
        default:
            return false;
    }

    if ( ! $image ) {
        return false;
    }

    // Resample the image
    imagecopyresampled( $image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

    // Get settings
    $options = get_option( 'webp_image_optimization_settings' );
    $jpeg_quality    = isset( $options['jpeg_quality'] ) ? intval( $options['jpeg_quality'] ) : 90; // Default to 90
    $png_compression = isset( $options['png_compression'] ) ? intval( $options['png_compression'] ) : 6; // Default to 6

    // Save resized image
    switch ( $mime_type ) {
        case 'image/jpeg':
            imagejpeg( $image_resized, $file, $jpeg_quality );
            break;
        case 'image/png':
            imagepng( $image_resized, $file, $png_compression );
            break;
    }

    // Free up memory
    imagedestroy( $image );
    imagedestroy( $image_resized );

    return $file;
}

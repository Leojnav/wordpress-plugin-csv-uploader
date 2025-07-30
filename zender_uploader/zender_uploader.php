<?php
/**
 * Plugin Name: TV Zender CSV Uploader
 * Description: Upload TV zender data van CSV files in de WordPress database
 * Version: 1.0
 * Author: Joël van Sandijk
 * Text Domain: zender-uploader
 */
 
 // Exit if accessed directly
 if (!defined('ABSPATH')) {
     exit;
 }
 
class TV_Zenders_Importer {
    // Table naam in database
    private $table_name;
    
    /**
     * Constructor - initialize plugin
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'zenders';
        
        // Initialize the plugin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Handle CSV import within admin_init to prevent header issues
        add_action('admin_init', array($this, 'handle_csv_import'));
        
        // Create table on plugin activation
        register_activation_hook(__FILE__, array($this, 'create_table'));
    }
    
    /**
     * Create custom database table for TV zenders
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            naam varchar(100) NOT NULL,
            zender int(11) NOT NULL,
            logo varchar(255) NOT NULL,
            extras text NOT NULL,
            package text NOT NULL,
            categorie text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add menu item in WordPress admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'Zenders Importer',
            'TV Zenders',
            'manage_options',
            'zender-uploader',
            array($this, 'render_admin_page'),
            'dashicons-video-alt3',
            30
        );
        
        add_submenu_page(
            'zender-uploader',
            'Import CSV',
            'Import CSV',
            'manage_options',
            'zender-uploader',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'zender-uploader',
            'View Zenders',
            'View Zenders',
            'manage_options',
            'tv-zenders-list',
            array($this, 'render_zenders_list')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('tv_zenders_importer_settings', 'tv_zenders_importer_settings');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_zender-uploader' !== $hook && 'tv-zenders_page_tv-zenders-list' !== $hook) {
            return;
        }
        
        wp_enqueue_style('zender-uploader-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), '1.0.0');
        wp_enqueue_script('zender-uploader-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), '1.0.0', true);
        
        // Add localized variables for the JavaScript
        wp_localize_script('zender-uploader-js', 'zenderUploader', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tv_zenders_importer_nonce')
        ));
    }
    
/**
 * Handle CSV import (moved to admin_init to prevent header issues)
 */
public function handle_csv_import() {
    // Check if we're on the right page and if form was submitted
    if (!isset($_GET['page']) || $_GET['page'] !== 'zender-uploader') {
        return;
    }
    
    if (!isset($_POST['import_csv']) || !isset($_FILES['csv_file'])) {
        return;
    }
    
    // Verify that a file was uploaded
    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        // Store error in transient instead of dying
        set_transient('tv_zenders_import_error', 'Error uploading file. Please try again.', 60);
        return;
    }
    
    // Check file extension
    $file_info = pathinfo($_FILES['csv_file']['name']);  // FIXED: Changed 'naam' to 'name'
    if (!isset($file_info['extension']) || strtolower($file_info['extension']) !== 'csv') {
        set_transient('tv_zenders_import_error', 'The uploaded file is not a CSV file. Please upload a valid CSV file.', 60);
        return;
    }
    
    // REPLACE THIS SECTION WITH THE NEW CODE BELOW
    // Get file contents
    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$file) {
        set_transient('tv_zenders_import_error', 'Could not open the uploaded file.', 60);
        return;
    }
    
    // Set correct locale for CSV parsing
    setlocale(LC_ALL, 'en_US.UTF-8');
    
    global $wpdb;
    
    // Clear existing data if requested
    if (isset($_POST['clear_table']) && $_POST['clear_table'] === '1') {
        $wpdb->query("TRUNCATE TABLE $this->table_name");
    }
    
    // Skip header row
    $header = fgetcsv($file, 0, ',', '"', '"'); 
        
        // Process rows
        $imported = 0;
        while (($row = fgetcsv($file, 0, ',')) !== false) {
            // Skip empty rows
            if (empty($row) || count($row) < 1) {
                continue;
            }
            
            // Process row - handle the special format of the CSV from the example
            $zender_data = $this->parse_csv_row($row);
            
            if (empty($zender_data['naam']) || empty($zender_data['zender'])) {
                continue; // Skip rows with missing essential data
            }
            
            // Insert into database
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'naam' => $zender_data['naam'],
                    'zender' => $zender_data['zender'],
                    'logo' => $zender_data['logo'],
                    'extras' => $zender_data['extras'],
                    'package' => $zender_data['package'],
                    'categorie' => $zender_data['categorie'],
                ),
                array('%s', '%d', '%s', '%s', '%s', '%s')
            );
            
            if ($result) {
                $imported++;
            }
        }
        
        fclose($file);
        
        // Store the result in a transient
        set_transient('tv_zenders_imported', $imported, 60);
        
        // Redirect using JavaScript instead of wp_redirect
        add_action('admin_notices', function() {
            echo '<script>window.location.href = "' . admin_url('admin.php?page=zender-uploader&imported=1') . '";</script>';
            exit;
        });
    }
    
    /**
    * Parse a CSV row from the specific format provided in the example
    */
    private function parse_csv_row($row) {
        // If we have a single-element row, it's likely the complex format with nested quotes
        if (count($row) === 1) {
            $line = trim($row[0]);

            // Use str_getcsv to properly parse the CSV line
            $parts = str_getcsv($line, ',', '"', '"');

            // Ensure we have all parts
            return array(
                'naam' => isset($parts[0]) ? $parts[0] : '',
                'zender' => isset($parts[1]) ? intval($parts[1]) : 0,
                'logo' => isset($parts[2]) ? $parts[2] : '',
                'extras' => isset($parts[3]) ? $parts[3] : '',
                'package' => isset($parts[4]) ? $parts[4] : '',
                'categorie' => isset($parts[5]) ? $parts[5] : '',
            );
        }

        // Fallback for normal CSV format
        return array(
            'naam' => isset($row[0]) ? $row[0] : '',
            'zender' => isset($row[1]) ? intval($row[1]) : 0,
            'logo' => isset($row[2]) ? $row[2] : '',
            'extras' => isset($row[3]) ? $row[3] : '',
            'package' => isset($row[4]) ? $row[4] : '',
            'categorie' => isset($row[5]) ? $row[5] : '',
        );
    }
    
    /**
     * Clean up quoted fields by removing surrounding quotes and handling escaped quotes
     */
    private function clean_quoted_field($field) {
        // Trim whitespace
        $field = trim($field);

        // Remove surrounding quotes if they exist on both sides
        if (strlen($field) >= 2 && substr($field, 0, 1) === '"' && substr($field, -1) === '"') {
            $field = substr($field, 1, -1);
        }

        // Replace double quotes (escape sequence) with single quotes
        $field = str_replace('""', '"', $field);

        return $field;
    }
    
    /**
     * Render the admin page for importing CSV
     */
    public function render_admin_page() {
        // Get the import result if any
        $imported = get_transient('tv_zenders_imported');
        if ($imported !== false) {
            delete_transient('tv_zenders_imported');
        }
        
        // Get error message if any
        $error_message = get_transient('tv_zenders_import_error');
        if ($error_message !== false) {
            delete_transient('tv_zenders_import_error');
        }
        
        // Get count of zenders in database
        global $wpdb;
        $zender_count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name");
        
        ?>
        <div class="wrap">
            <h1>TV Zenders CSV uploader</h1>
            
            <?php if (isset($_GET['imported']) && $imported !== false) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo intval($imported); ?> zenders successvol geïmporteerd!</p>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message !== false) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($error_message); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Upload Zenders uit CSV</h2>
                <p>Upload een CSV bestand met TV zender data om in de  database te importeren.</p>
                <p>Huidig zender aantal in de database: <strong><?php echo intval($zender_count); ?></strong></p>
                
                <form method="post" enctype="multipart/form-data">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="csv_file">CSV Bestand</label></th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" required />
                                <p class="description">
                                    Het soort CSV bestand moet het volgende zijn: CSV UTF-8 (door komma's gescheiden) (*.csv)
                                    <br/>
                                    Het CSV bestand moet deze kolommen hebben: naam, zender, logo, extras, package
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Opties</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="clear_table" value="1" />
                                    Verwijder bestaande data voor bij het uploaden
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="import_csv" class="button button-primary" value="Import CSV" />
                    </p>
                </form>
            </div>
            
            <div class="card">
                <h2>CSV Opmaak Regels</h2>
                <p>Het CSV Bestand moet de volgende opmaak hebben:</p>
                <pre>naam,zender,logo,extras,package,categorie
NPO 1,1,npo1.png,"HD,SD","Basispakket,Standaardpakket","DVBC"
NPO 2,2,npo2.png,"HD,SD","Basispakket,Standaardpakket","DVBC"</pre>
                <p>Zorg ervoor dat het CSV bestand komma's gebruikt om waardes uit elkaar te houden en dubbele aanhalingstekens voor teksten met komma's. [CSV UTF-8 (door komma's gescheiden) (*.csv)]</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render the zenders list page
     */
    public function render_zenders_list() {
        global $wpdb;
        
        // Get zenders from database
        $zenders = $wpdb->get_results("SELECT * FROM $this->table_name ORDER BY zender ASC");
        
        ?>
        <div class="wrap">
            <h1>TV Zender Lijst</h1>
            
            <?php if (empty($zenders)) : ?>
                <div class="notice notice-warning">
                    <p>Geen zenders gevonden in the database. <a href="<?php echo admin_url('admin.php?page=zender-uploader'); ?>">Upload zenders</a> om te beginnen.</p>
                </div>
            <?php else : ?>
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <a href="<?php echo admin_url('admin.php?page=zender-uploader'); ?>" class="button">Upload meer Zenders</a>
                    </div>
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo count($zenders); ?> items</span>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped zenders-table">
                    <thead>
                        <tr>
                            <th>Zender #</th>
                            <th>Naam</th>
                            <th>Logo</th>
                            <th>Extras</th>
                            <th>Package</th>
                            <th>Categorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($zenders as $zender) : ?>
                            <tr>
                                <td><?php echo esc_html($zender->zender); ?></td>
                                <td><?php echo esc_html($zender->naam); ?></td>
                                <td><?php echo esc_html($zender->logo); ?></td>
                                <td><?php echo esc_html($zender->extras); ?></td>
                                <td><?php echo esc_html($zender->package); ?></td>
                                <td><?php echo esc_html($zender->categorie); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}
 
// Initialize the plugin
$tv_zenders_importer = new TV_Zenders_Importer();
 
/**
 * Add CSS folder structure on plugin activation
 */
function zender_uploader_create_assets_folders() {
    // Create folders if they don't exist
    $plugin_dir = plugin_dir_path(__FILE__);
    
    if (!file_exists($plugin_dir . 'assets')) {
        mkdir($plugin_dir . 'assets');
    }
    
    if (!file_exists($plugin_dir . 'assets/css')) {
        mkdir($plugin_dir . 'assets/css');
    }
    
    if (!file_exists($plugin_dir . 'assets/js')) {
        mkdir($plugin_dir . 'assets/js');
    }
    
    // Create basic CSS file
    $css_content = <<<CSS
.zenders-table .column-logo img {
    max-width: 50px;
    height: auto;
}

.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
    padding: 1px 12px;
    position: relative;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.card h2 {
    margin-top: 1em;
}

pre {
    background: #f5f5f5;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    overflow: auto;
}
CSS;
    
    file_put_contents($plugin_dir . 'assets/css/admin.css', $css_content);
    
    // Create basic JS file
    $js_content = <<<JS
jQuery(document).ready(function($) {
    // Future JavaScript functionality can be added here
    
    // Confirm before clearing table
    $('form input[name="clear_table"]').on('change', function() {
        if ($(this).is(':checked')) {
            if (!confirm('Waarschuwing: Dit zal alle bestaande zender data verwijderen. Weet je het zeker?')) {
                $(this).prop('checked', false);
            }
        }
    });
});
JS;
     
    file_put_contents($plugin_dir . 'assets/js/admin.js', $js_content);
}

register_activation_hook(__FILE__, 'zender_uploader_create_assets_folders');
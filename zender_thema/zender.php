
<?php
/**
 * Template Name: Zender List
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . "zenders";
$zenders = $wpdb->get_results("SELECT * FROM $table_name ORDER BY zender ASC", ARRAY_A);
$title = "TV Zender Lijst";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html style="margin-top: 0px !important;" xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?> version="XHTML+RDFa 1.0">
<title>Kabeltex | <?php echo $title; ?></title>
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/style.css" rel="stylesheet">
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/style-zenders.css" rel="stylesheet">
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/css/navigation.css" rel="stylesheet">
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/css/kb-table.css" rel="stylesheet">
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/css/kb-mijn.css" rel="stylesheet">
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/../../plugins/kb-crm/assets/afspraakplannen.css?ver=1.0" rel="stylesheet">
<link href="<?php echo esc_url( get_template_directory_uri() ); ?>/../../plugins/kb-crm/assets/huisinstallatie.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/navigation.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/faq.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/kabeltex.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/search.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/hamburger.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/filter.js"></script>
<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/print.js"></script>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<?php
$body_style = '';
if (stripos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
    $body_style = 'style="background-color: magenta !important;"';
}
?>

<body <?php body_class(); ?> <?php if (is_front_page()) { echo 'onload="postcodeCheckPopup();"'; } ?> <?php echo $body_style; ?>>
<div class="row header">
  <div class="col-2 hide-mobile"></div>
  <div class="col-2 hide-tablet">
	<!-- Display logo or site titie/description -->
    <?php if ( get_custom_logo() ) : ?>
	<?php
	//$custom_logo_id = get_theme_mod( 'custom_logo' );
	//$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
	//$logo_url = esc_url( $logo[0] );
    $auto_user = null;
    if ($user->ID == 0) {
        // geen gebruiker ingelogd, check auto login module
        if (method_exists('KBAutoLogin\Controllers\AutoLoginManager', 'getUser')) {
            $auto_user = call_user_func(array('KBAutoLogin\Controllers\AutoLoginManager', 'getUser'));
        }
    }
    $logo_file = ($auto_user != null) || !empty($user->ID) ? 'logo_zonder.png' : 'logo.png';
    $logo_url = get_template_directory_uri() . '/assets/' . $logo_file;
	?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php bloginfo('name'); ?> | <?php bloginfo( 'description' ); ?>"><img class="site-logo" src="<?php echo $logo_url; ?>" alt="<?php bloginfo('name'); ?> | <?php bloginfo( 'description' ); ?>" /></a>
	<?php else : ?>
		<h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
		<h2><?php bloginfo( 'description' ); ?></h2>
	<?php endif; ?>
	<?php //if (!empty($user->ID)) { echo '<p class="user_indicator">Gebruiker: ' . $user->user_login . '</p>'; } ?>
  </div>
  <div class="col-6 nav-custom">
	<div class="hide-desktop">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php bloginfo('name'); ?> | <?php bloginfo( 'description' ); ?>"><img class="site-logo" src="<?php echo $logo_url; ?>" alt="<?php bloginfo('name'); ?> | <?php bloginfo( 'description' ); ?>" /></a>
	<?php //if (!empty($user->ID)) { echo '<p class="user_indicator">Gebruiker: ' . $user->user_login . '</p>'; } ?>
	</div>
	<!-- Start navigation -->
  <p class='menu-custom'>Menu</p>
	<div id='cssmenu'>
        <?php echo create_menu_markup('header-menu'); ?>
	</div>
	<!-- End navigation -->
  </div>
  <div class="col-2 hide-mobile"></div>
</div>
</div>
    <div class="container">
        <!-- Sidebar Filters -->
        <div class="menu-toggle" onclick="toggleSidebar()">☰ Filters</div>
        <aside class="sidebar">    
            <div class="close-btn" onclick="toggleSidebar()">✕</div>      
            <button id="printVisibleChannels">Zenders printen</button>
            <h3>Wat voor zenders:</h3>
            <div class="submenu">
                <input type="radio" id="check-DVB-C" name="zenders" class="filter" value="DVBC" checked>
                <label for="check-DVB-C"> Kabel TV</label>
                <input type="radio" id="check-TV2GO" name="zenders" class="filter" value="TV2GO">
                <label for="check-TV2GO">TV2GO</label>
            </div>

            <h3>TV-Pakketten:</h3>
            <div class="submenu">
                <div id="basispakket-wrapper">
                  <input type="checkbox" id="check-Basis" name="pakketten" class="filter" value="Basispakket">
                  <label for="check-Basis">Basis</label>
                </div>
                <input type="checkbox" id="check-Standaard" name="pakketten" class="filter" value="Standaardpakket">
                <label for="check-Standaard"> Standaard</label>
                <input type="checkbox" id="check-Totaal" name="pakketten" class="filter" value="Totaalpakket">
                <label for="check-Totaal"> Totaal</label>
                <input type="checkbox" id="check-Recreatie" name="pakketten" class="filter" value="Recreatiepakket">
                <label for="check-Recreatie"> Recreatie</label>
                <input type="checkbox" id="check-Ziggo" name="pakketten" class="filter" value="Ziggo">
                <label for="check-Ziggo"> Ziggo</label>
                <input type="checkbox" id="check-Film1" name="pakketten" class="filter" value="Film1">
                <label for="check-Film1"> Film1</label>
                <input type="checkbox" id="check-Duits" name="pakketten" class="filter" value="Duitspakket">
                <label for="check-Duits"> Duits</label>
                <input type="checkbox" id="check-Kinderen" name="pakketten" class="filter" value="Kinderenpakket">
                <label for="check-Kinderen"> Kinderen</label>
                <input type="checkbox" id="check-Algemeen" name="pakketten" class="filter" value="Algemeenpakket">
                <label for="check-Algemeen"> Algemeen</label>
                <input type="checkbox" id="check-Erotiek" name="pakketten" class="filter" value="Erotiekpakket">
                <label for="check-Erotiek"> Erotiek</label>
            </div>

            <!-- Radio-pakketten -->
            <div id="radio-pakketten-wrapper">
              <h3>Radio-Pakketten:</h3>
              <div class="submenu">
                <input type="checkbox" id="check-Radio" name="pakketten" class="filter" value="Radio">
                <label for="check-Radio">Radio</label>
                <input type="checkbox" id="check-Radio-plus-pakket" name="pakketten" class="filter" value="Radio plus pakket">
                <label for="check-Radio-plus-pakket">Radio plus pakket</label>
              </div>
            </div>

            <h3>Zender kwaliteit:</h3>
            <div class="submenu">
                <input type="checkbox" id="check-SD" name="kwaliteit" class="filter" value="SD">
                <label for="check-SD"> SD</label>
                <input type="checkbox" id="check-HD" name="kwaliteit" class="filter" value="HD">
                <label for="check-HD"> HD</label>
                <input type="checkbox" id="check-4K" name="kwaliteit" class="filter" value="4K">
                <label for="check-4K"> 4K</label>
            </div>
        </aside>

        <!-- Zender-lijst -->
        <main class="zender-lijst" id="top">
            <div class="header-search">
                <div class="zender-header">
                    <span>Zendernaam</span>
                    <span>Kanaal</span>
                    <span>Extra info</span>
                    <span>Pakketsoort</span>
                    <span>Categorie</span>
                </div>
           
                <div class="search-container">
                    <h3>Zoek zender:</h3>
                    <input type="text" id="channelSearch" placeholder="Type zendernaam of kanaal nummer..." />
                    <button type="button" id="clearSearch">Wissen</button>
                </div>
            </div>
            <div id="zenders" class="zenders">
                <?php foreach ($zenders as $zd) : 
                    $extras = is_string($zd["extras"]) ? 
                        (json_decode($zd["extras"]) ?: explode(",", $zd["extras"])) : 
                        $zd["extras"];
                    
                    $packages = is_string($zd["package"]) ? 
                        (json_decode($zd["package"]) ?: explode(",", $zd["package"])) : 
                        $zd["package"];
                        
                    $categories = is_string($zd["categorie"]) ? 
                        (json_decode($zd["categorie"]) ?: explode(",", $zd["categorie"])) : 
                        $zd["categorie"];
                ?>
                    <div class="zender-item" 
                         data-package='<?php echo json_encode($packages); ?>' 
                         data-extras='<?php echo json_encode($extras); ?>'
                         data-categorie='<?php echo json_encode($categories); ?>'>
                        <span>
                            <img class="logo" src="<?php echo esc_url(wp_get_upload_dir()['baseurl'] . '/zender-logos/' . $zd["logo"]); ?>" 
                                 alt="<?php echo esc_attr($zd["naam"]); ?>"> 
                            <strong><?php echo esc_html($zd["naam"]); ?></strong>
                        </span>
                        <span><?php echo esc_html($zd["zender"]); ?></span>
                        <span><?php echo is_array($extras) ? esc_html(implode(", ", $extras)) : esc_html($extras); ?></span>
                        <span><?php echo is_array($packages) ? esc_html(implode(", ", $packages)) : esc_html($packages); ?></span>
                        <span><?php echo is_array($categories) ? esc_html(implode(", ", $categories)) : esc_html($categories); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Pagination Controls -->
    <div id="pagination" class="pagination">
        <button id="prevPage"><a href="#top">Vorige</a></button>
        <span id="pageInfo" class="txt">1 van 1</span>
        <button id="nextPage"><a href="#top">Volgende</a></button>
    </div>
</body>
</html>
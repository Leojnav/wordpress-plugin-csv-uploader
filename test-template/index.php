
<?php
/**
 * Template Name: Zender List
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . "zenders";
$channels = $wpdb->get_results("SELECT * FROM $table_name ORDER BY zender ASC", ARRAY_A);


?>

<!DOCTYPE html>
<html lang="nl">
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <title>TV Channel List</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <h3>Wat voor zenders:</h3>
            <label><input type="checkbox" class="filter" value="DVB-C"> DVB-C</label>
            <label><input type="checkbox" class="filter" value="TV2GO"> TV2GO</label>
            <label><input type="checkbox" class="filter" value="Radio"> Radio</label>

            <h3>TV-Pakketten:</h3>
            <label><input type="checkbox" class="filter" value="Basispakket"> Basis</label>
            <label><input type="checkbox" class="filter" value="Standaardpakket"> Standaard</label>
            <label><input type="checkbox" class="filter" value="Totaalpakket"> Totaal</label>
            <label><input type="checkbox" class="filter" value="Ziggo"> Ziggo Sport</label>
            <label><input type="checkbox" class="filter" value="Film1"> Film1</label>
            <label><input type="checkbox" class="filter" value="Duitspakket"> Duits</label>
            <label><input type="checkbox" class="filter" value="Kinderenpakket"> Kinderen</label>
            <label><input type="checkbox" class="filter" value="Algemeenpakket"> Algemeen</label>
            <label><input type="checkbox" class="filter" value="Erotiekpakket"> Erotiek</label>
        </aside>

        <!-- Channel List -->
        <main class="channel-list">
            <div class="channel-header">
                <span>Zendernaam</span>
                <span>Kanaal</span>
                <span>Extra's</span>
                <span>Pakketsoort</span>
            </div>
            <div id="channels">
                <?php foreach ($channels as $ch) : 
                    // Safely parse extras and package, handling potential JSON or comma-separated strings
                    $extras = is_string($ch["extras"]) ? 
                        (json_decode($ch["extras"]) ?: explode(",", $ch["extras"])) : 
                        $ch["extras"];
                    
                    $packages = is_string($ch["package"]) ? 
                        (json_decode($ch["package"]) ?: explode(",", $ch["package"])) : 
                        $ch["package"];
                ?>
                    <div class="channel-item" data-package='<?php echo json_encode($packages); ?>'>
                        <span>
                            <img class="logo" src="<?php echo esc_url(wp_get_upload_dir()['baseurl'] . '/zender-logos/' . $ch["logo"]); ?>" 
                                 alt="<?php echo esc_attr($ch["naam"]); ?>" width="30"> 
                            <?php echo esc_html($ch["naam"]); ?>
                        </span>
                        <span><?php echo esc_html($ch["zender"]); ?></span>
                        <span><?php echo is_array($extras) ? esc_html(implode(", ", $extras)) : esc_html($extras); ?></span>
                        <span><?php echo is_array($packages) ? esc_html(implode(", ", $packages)) : esc_html($packages); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Pagination Controls -->
    <div id="pagination">
        <button id="prevPage">Previous</button>
        <span id="pageInfo">Page 1 of X</span>
        <button id="nextPage">Next</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const filters = document.querySelectorAll(".filter");
            const channelItems = document.querySelectorAll(".channel-item");
            
            filters.forEach(filter => {
                filter.addEventListener("change", function () {
                    let activeFilters = Array.from(filters)
                        .filter(f => f.checked)
                        .map(f => f.value);

                    let visibleChannels = 0;
                    channelItems.forEach(item => {
                        let packageTypes = JSON.parse(item.dataset.package);
                        if (activeFilters.length === 0 || activeFilters.some(f => packageTypes.includes(f))) {
                            item.classList.remove('hidden');
                            visibleChannels++;
                        } else {
                            item.classList.add('hidden');
                        }
                    });

                    // Trigger pagination reset
                    resetPagination(visibleChannels);
                });
            });

            // Pagination functionality
            const channelContainer = document.getElementById("channels");
            const itemsPerPage = 20;
            let currentPage = 1;
            let filteredChannels = [];

            function resetPagination(totalVisible) {
                currentPage = 1;
                updatePagination(totalVisible);
            }

            function updatePagination(totalVisible) {
                const totalPages = Math.max(1, Math.ceil(totalVisible / itemsPerPage));
                document.getElementById("pageInfo").innerText = `Page ${currentPage} of ${totalPages}`;

                const pageStartIndex = (currentPage - 1) * itemsPerPage;
                const pageEndIndex = pageStartIndex + itemsPerPage;

                let visibleCount = 0;
                Array.from(channelContainer.children).forEach((item, index) => {
                    if (!item.classList.contains('hidden')) {
                        if (visibleCount >= pageStartIndex && visibleCount < pageEndIndex) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Update navigation buttons
                document.getElementById("prevPage").disabled = currentPage === 1;
                document.getElementById("nextPage").disabled = currentPage === totalPages;
            }

            // Pagination Button Listeners
            document.getElementById("prevPage").addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination(document.querySelectorAll(".channel-item:not(.hidden)").length);
                }
            });

            document.getElementById("nextPage").addEventListener("click", () => {
                const visibleChannels = document.querySelectorAll(".channel-item:not(.hidden)").length;
                const totalPages = Math.max(1, Math.ceil(visibleChannels / itemsPerPage));
                
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination(visibleChannels);
                }
            });

            // Initial pagination setup
            updatePagination(channelItems.length);
        });
    </script>
</body>
</html>
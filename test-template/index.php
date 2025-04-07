
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


?>

<!DOCTYPE html>
<html lang="nl">
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <title>TV Zender Lijst</title>
</head>
<body>
    <div class="container">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <h3>Wat voor zenders:</h3>
            <div class="submenu">
                <input type="radio" id="check-DVB-C" name="zenders" value="DVB-C" checked>
                <label for="check-DVB-C"> DVB-C</label>
                <input type="radio" id="check-TV2GO" name="zenders" value="TV2GO">
                <label for="check-TV2GO"> TV2GO</label>
                <input type="radio" id="check-Radio" name="zenders" value="Radio">
                <label for="check-Radio"> Radio</label>
            </div>

            <h3>TV-Pakketten:</h3>
            <div class="submenu">
                <input type="checkbox" id="check-Basis" name="pakketten" class="filter" value="Basispakket">
                <label for="check-Basis"> Basis</label>
                <input type="checkbox" id="check-Standaard" ame="pakketten" class="filter" value="Standaardpakket">
                <label for="check-Standaard"> Standaard</label>
                <input type="checkbox" id="check-Totaal" name="pakketten" class="filter" value="Totaalpakket">
                <label for="check-Totaal"> Totaal</label>
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
            <div class="zender-header">
                <span>Zendernaam</span>
                <span>Kanaal</span>
                <span>Kwaliteit</span>
                <span>Pakketsoort</span>
            </div>
            <div id="zenders">
                <?php foreach ($zenders as $zd) : 
                    $extras = is_string($zd["extras"]) ? 
                        (json_decode($zd["extras"]) ?: explode(",", $zd["extras"])) : 
                        $zd["extras"];
                    
                    $packages = is_string($zd["package"]) ? 
                        (json_decode($zd["package"]) ?: explode(",", $zd["package"])) : 
                        $zd["package"];
                ?>
                        <div class="zender-item" data-package='<?php echo json_encode($packages); ?>' data-extras='<?php echo json_encode($extras); ?>'>
                        <span>
                            <img class="logo" src="<?php echo esc_url(wp_get_upload_dir()['baseurl'] . '/zender-logos/' . $zd["logo"]); ?>" 
                                 alt="<?php echo esc_attr($zd["naam"]); ?>"> 
                                 <strong><?php echo esc_html($zd["naam"]); ?>
                        </span></strong>
                        <span><?php echo esc_html($zd["zender"]); ?></span>
                        <span><?php echo is_array($extras) ? esc_html(implode(", ", $extras)) : esc_html($extras); ?></span>
                        <span><?php echo is_array($packages) ? esc_html(implode(", ", $packages)) : esc_html($packages); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Pagination Controls -->
    <div id="pagination" class="pagination">
        <button id="prevPage"><a href="#top">Vorige</a></button>
        <span id="pageInfo" class="txt">1 van X</span>
        <button id="nextPage"><a href="#top">Volgende</a></button>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
    const filters = document.querySelectorAll(".filter");
    const zenderItems = document.querySelectorAll(".zender-item");
    filters.forEach(filter => {
        filter.addEventListener("change", function () {
            let activeFilters = Array.from(filters)
                .filter(f => f.checked)
                .map(f => f.value);
            let visibleZenders = 0;
            zenderItems.forEach(item => {
                let packageTypes = JSON.parse(item.dataset.package);
                let extraTypes = JSON.parse(item.dataset.extras);
                let showItem = true;
                let packageFilters = activeFilters.filter(f => !['SD', 'HD', '4K'].includes(f));
                let qualityFilters = activeFilters.filter(f => ['SD', 'HD', '4K'].includes(f));
                let matchesPackage = packageFilters.length === 0 || packageFilters.some(f => packageTypes.includes(f));
                let matchesQuality = qualityFilters.length === 0 || qualityFilters.some(f => extraTypes.includes(f));
                if (matchesPackage && matchesQuality) {
                    item.classList.remove('hidden');
                    visibleZenders++;
                } else {
                    item.classList.add('hidden');
                }
            });
            resetPagination(visibleZenders);
        });
    });
    // Pagination functionality
    const zenderContainer = document.getElementById("zenders");
    const itemsPerPage = 17;
    let currentPage = 1;
    let filteredZenders = [];
    function resetPagination(totalVisible) {
        currentPage = 1;
        updatePagination(totalVisible);
    }
    function updatePagination(totalVisible) {
        const totalPages = Math.max(1, Math.ceil(totalVisible / itemsPerPage));
        document.getElementById("pageInfo").innerText = `${currentPage} van ${totalPages}`;
        const pageStartIndex = (currentPage - 1) * itemsPerPage;
        const pageEndIndex = pageStartIndex + itemsPerPage;
        let visibleCount = 0;
        Array.from(zenderContainer.children).forEach((item, index) => {
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

        document.getElementById("prevPage").disabled = currentPage === 1;
        document.getElementById("nextPage").disabled = currentPage === totalPages;
    }
    document.getElementById("prevPage").addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            updatePagination(document.querySelectorAll(".zender-item:not(.hidden)").length);
        }
    });
    document.getElementById("nextPage").addEventListener("click", () => {
        const visibleZenders = document.querySelectorAll(".zender-item:not(.hidden)").length;
        const totalPages = Math.max(1, Math.ceil(visibleZenders / itemsPerPage));
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination(visibleZenders);
        }
    });
    updatePagination(zenderItems.length);
    });
    </script>
</body>
</html>
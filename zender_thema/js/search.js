document.addEventListener("DOMContentLoaded", function () {
    const filters = document.querySelectorAll(".filter");
    const zenderItems = document.querySelectorAll(".zender-item");
    const searchInput = document.getElementById("channelSearch");
    const clearButton = document.getElementById("clearSearch");
    
    // Pagination variables
    const itemsPerPage = 26;
    let currentPage = 1;
    
    // Search function
    function searchChannels() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        zenderItems.forEach(item => {
            const channelName = item.querySelector('strong').textContent.toLowerCase();
            const channelNumber = item.children[1].textContent.toLowerCase();
            
            // Remove previous highlights
            item.querySelectorAll('.highlight').forEach(el => {
                el.outerHTML = el.innerHTML;
            });
            
            if (searchTerm === '') {
                // If search is empty, show all items (subject to other filters)
                item.classList.remove('search-hidden');
            } else if (channelName.includes(searchTerm) || channelNumber.includes(searchTerm)) {
                item.classList.remove('search-hidden');
                
                // Highlight matching text
                highlightText(item.querySelector('strong'), searchTerm);
                highlightText(item.children[1], searchTerm);
            } else {
                item.classList.add('search-hidden');
            }
        });
        
        // Reset pagination after search
        const visibleCount = document.querySelectorAll(".zender-item:not(.hidden):not(.search-hidden)").length;
        resetPagination(visibleCount);
    }
    
    // Highlight matching text
    function highlightText(element, searchTerm) {
        const text = element.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        const highlightedText = text.replace(regex, '$1');
        element.innerHTML = highlightedText;
    }
    
    // Clear search
    function clearSearch() {
        searchInput.value = '';
        zenderItems.forEach(item => {
            item.classList.remove('search-hidden');
            // Remove highlights
            item.querySelectorAll('.highlight').forEach(el => {
                el.outerHTML = el.innerHTML;
            });
        });
        
        // Reset pagination
        const visibleCount = document.querySelectorAll(".zender-item:not(.hidden)").length;
        resetPagination(visibleCount);
    }
    
    // Modified applyFilters function to work with search
    function applyFilters() {
        // Get selected zender type (DVBC or TV2GO)
        const zenderTypeFilters = Array.from(document.querySelectorAll('input[name="zenders"]:checked'))
            .map(f => f.value);
        
        // Get selected package filters
        const packageFilters = Array.from(document.querySelectorAll('input[name="pakketten"]:checked'))
            .map(f => f.value);
        
        // Get selected quality filters
        const qualityFilters = Array.from(document.querySelectorAll('input[name="kwaliteit"]:checked'))
            .map(f => f.value);
        
        let visibleZenders = 0;
        
        zenderItems.forEach(item => {
            let categorieTypes = [];
            let packageTypes = [];
            let extraTypes = [];
            
            try {
                categorieTypes = JSON.parse(item.dataset.categorie || '[]');
                packageTypes = JSON.parse(item.dataset.package || '[]');
                extraTypes = JSON.parse(item.dataset.extras || '[]');
            } catch (e) {
                console.error("Error parsing JSON data", e);
            }
            
            // Ensure arrays
            if (!Array.isArray(categorieTypes)) categorieTypes = [];
            if (!Array.isArray(packageTypes)) packageTypes = [];
            if (!Array.isArray(extraTypes)) extraTypes = [];
            
            // First filter by zender type (DVBC/TV2GO)
            let matchesZenderType = zenderTypeFilters.length === 0 || 
                                   (zenderTypeFilters.includes("DVBC") && categorieTypes.includes("DVBC")) ||
                                   (zenderTypeFilters.includes("TV2GO") && categorieTypes.includes("TV2GO"));
            
            // Then filter by package type
            let matchesPackage = packageFilters.length === 0 || packageFilters.some(f => packageTypes.includes(f));
            
            // Then filter by quality
            let matchesQuality = qualityFilters.length === 0 || qualityFilters.some(f => extraTypes.includes(f));
            
            if (matchesZenderType && matchesPackage && matchesQuality) {
                item.classList.remove('hidden');
                // Only count if not hidden by search
                if (!item.classList.contains('search-hidden')) {
                    visibleZenders++;
                }
            } else {
                item.classList.add('hidden');
            }
        });
        
        // Reset pagination after filtering
        resetPagination(visibleZenders);
    }
    
    // Reset pagination to page 1
    function resetPagination(totalVisible) {
        currentPage = 1;
        updatePagination(totalVisible);
    }
    
    // Update pagination display (modified to work with search)
    function updatePagination(totalVisible) {
        const totalPages = Math.max(1, Math.ceil(totalVisible / itemsPerPage));
        document.getElementById("pageInfo").innerText = `${currentPage} van ${totalPages}`;
        
        const pageStartIndex = (currentPage - 1) * itemsPerPage;
        const pageEndIndex = pageStartIndex + itemsPerPage;
        
        let visibleCount = 0;
        
        // Go through all zender items
        Array.from(document.getElementById("zenders").children).forEach((item) => {
            if (!item.classList.contains('hidden') && !item.classList.contains('search-hidden')) {
                // This item is visible after filtering and search
                if (visibleCount >= pageStartIndex && visibleCount < pageEndIndex) {
                    // Show this item (it's in the current page range)
                    item.style.display = 'flex';
                } else {
                    // Hide this item (it's outside current page range)
                    item.style.display = 'none';
                }
                visibleCount++;
            } else {
                // This item is filtered out or hidden by search, always hide it
                item.style.display = 'none';
            }
        });

        // Update button states
        document.getElementById("prevPage").disabled = currentPage === 1;
        document.getElementById("nextPage").disabled = currentPage === totalPages;
    }
    
    // Event listeners
    searchInput.addEventListener("input", searchChannels);
    clearButton.addEventListener("click", clearSearch);
    
    // Add change event listener to all filters
    filters.forEach(filter => {
        filter.addEventListener("change", applyFilters);
    });
    
    // Pagination button events (unchanged)
    document.getElementById("prevPage").addEventListener("click", (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            const visibleCount = document.querySelectorAll(".zender-item:not(.hidden):not(.search-hidden)").length;
            updatePagination(visibleCount);
            document.getElementById("top").scrollIntoView();
        }
    });
    
    document.getElementById("nextPage").addEventListener("click", (e) => {
        e.preventDefault();
        const visibleCount = document.querySelectorAll(".zender-item:not(.hidden):not(.search-hidden)").length;
        const totalPages = Math.max(1, Math.ceil(visibleCount / itemsPerPage));
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination(visibleCount);
            document.getElementById("top").scrollIntoView();
        }
    });
    
    // Apply filters on initial page load
    applyFilters();
});

window.addEventListener("resize", () => {
  applyFilters();
  searchChannels();
});

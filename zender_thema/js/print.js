document.addEventListener("DOMContentLoaded", function () {
    const printButton = document.getElementById("printVisibleChannels");
    
    if (printButton) {
        printButton.addEventListener("click", function() {
            printFilteredZenders();
        });
    }
    
    function printFilteredZenders() {
        // Get all visible zender items (not hidden by filters or search)
        const visibleZenders = document.querySelectorAll(".zender-item:not(.hidden):not(.search-hidden)");
        
        if (visibleZenders.length === 0) {
            alert("Geen zenders gevonden om te printen. Pas uw filters aan.");
            return;
        }
        
        // Get current filter selections for the print header
        const selectedFilters = getSelectedFilters();
        
        // Create print content
        let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Kabeltex TV Zenders</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                .print-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 20px;
                }
                .print-header h1 {
                    color: #cc0000;
                    margin-bottom: 10px;
                }
                .filter-info {
                    background-color: #f5f5f5;
                    padding: 15px;
                    margin-bottom: 20px;
                    border-left: 4px solid #cc0000;
                }
                .filter-info h3 {
                    margin-top: 0;
                    color: #cc0000;
                }
                .zender-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .zender-table th {
                    background-color: #cc0000;
                    color: white;
                    padding: 12px 8px;
                    text-align: left;
                    border: 1px solid #ddd;
                    font-weight: bold;
                }
                .zender-table td {
                    padding: 10px 8px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                }
                .zender-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .zender-table tr:hover {
                    background-color: #f5f5f5;
                }
                .zender-name {
                    font-weight: bold;
                    color: #cc0000;
                }
                .channel-number {
                    font-weight: bold;
                    text-align: center;
                }
                .print-footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 20px;
                }
                @media print {
                    body { margin: 0; }
                    .print-header { page-break-after: avoid; }
                    .zender-table { page-break-inside: avoid; }
                    .zender-table tr { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>Kabeltex TV Zenders</h1>
                <p>Gefilterde zenderlijst - ${new Date().toLocaleDateString('nl-NL')}</p>
            </div>`;
        
        // Add filter information if any filters are selected
        if (selectedFilters.length > 0) {
            printContent += `
            <div class="filter-info">
                <h3>Toegepaste filters:</h3>
                <p>${selectedFilters.join(' • ')}</p>
            </div>`;
        }
        
        // Add search information if search is active
        const searchTerm = document.getElementById("channelSearch").value.trim();
        if (searchTerm) {
            printContent += `
            <div class="filter-info">
                <h3>Zoekterm:</h3>
                <p>"${searchTerm}"</p>
            </div>`;
        }
        
        // Create table
        printContent += `
            <table class="zender-table">
                <thead>
                    <tr>
                        <th>Zendernaam</th>
                        <th>Kanaal</th>
                        <th>Extra info</th>
                        <th>Pakketsoort</th>
                        <th>Categorie</th>
                    </tr>
                </thead>
                <tbody>`;
        
        // Add visible zenders to table
        visibleZenders.forEach(function(zender) {
            const zenderName = zender.children[0].querySelector('strong').textContent;
            const channelNumber = zender.children[1].textContent;
            const extraInfo = zender.children[2].textContent;
            const packageInfo = zender.children[3].textContent;
            const categoryInfo = zender.children[4].textContent;
            
            printContent += `
                <tr>
                    <td class="zender-name">${zenderName}</td>
                    <td class="channel-number">${channelNumber}</td>
                    <td>${extraInfo}</td>
                    <td>${packageInfo}</td>
                    <td>${categoryInfo}</td>
                </tr>`;
        });
        
        printContent += `
                </tbody>
            </table>
            <div class="print-footer">
                <p>Totaal aantal zenders: ${visibleZenders.length}</p>
                <p>Gegenereerd op ${new Date().toLocaleString('nl-NL')} | Kabeltex</p>
            </div>
        </body>
        </html>`;
        
        // Open new window and print
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load, then print
        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            // Optional: close the window after printing (user can cancel this)
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        };
    }
    
    function getSelectedFilters() {
        const filters = [];
        
        // Get zender type (DVBC/TV2GO)
        const zenderType = document.querySelector('input[name="zenders"]:checked');
        if (zenderType) {
            filters.push(zenderType.nextElementSibling.textContent.trim());
        }
        
        // Get selected packages
        const selectedPackages = document.querySelectorAll('input[name="pakketten"]:checked');
        if (selectedPackages.length > 0) {
            const packageNames = Array.from(selectedPackages).map(input => 
                input.nextElementSibling.textContent.trim()
            );
            filters.push('Pakketten: ' + packageNames.join(', '));
        }
        
        // Get selected quality filters
        const selectedQualities = document.querySelectorAll('input[name="kwaliteit"]:checked');
        if (selectedQualities.length > 0) {
            const qualityNames = Array.from(selectedQualities).map(input => 
                input.nextElementSibling.textContent.trim()
            );
            filters.push('Kwaliteit: ' + qualityNames.join(', '));
        }
        
        return filters;
    }
});
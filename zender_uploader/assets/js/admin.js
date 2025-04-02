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
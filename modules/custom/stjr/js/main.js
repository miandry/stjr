jQuery(function () {
    jQuery('#selectpicker').selectpicker();

        // Handle change event to update the selected values with '>' separator
        jQuery('#selectpicker').on('changed.bs.select', function () {
            // Get the selected options
            var selected = $(this).val();
            // Join the selected options with '>' separator
            var selectedValues = selected ? selected.join(' > ') : 'None selected';
            // Display the selected values in the output
            $('#selected-values').text(selectedValues);
          });
});
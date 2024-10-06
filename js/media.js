// js/media.js

jQuery(document).ready(function($) {
    /**
     * Handle "Convert to WebP" button click
     */
    $(document).on('click', '.convert-to-webp', function(e) {
        e.preventDefault();

        var button = $(this);
        var attachmentId = button.data('attachment-id');

        if (!attachmentId) {
            alert('Invalid attachment ID.');
            return;
        }

        // Disable the button to prevent multiple clicks and update its text
        button.prop('disabled', true);
        var originalButtonText = button.text();
        button.text('Converting...');

        // Prepare the data to be sent in the POST request
        var data = {
            action: 'webp_convert_attachment',
            nonce: webpImageOptimization.nonce,
            attachment_id: attachmentId
        };

        // Send AJAX POST request using jQuery
        $.post(webpImageOptimization.ajax_url, data, function(response) {
            if (response.success) {
                alert('Image successfully converted to WebP.');
                // Optionally, refresh the page or update the image preview
                location.reload(); // Refresh to see changes
            } else {
                alert('Conversion failed: ' + response.data);
                // Re-enable the button in case of failure
                button.prop('disabled', false);
                button.text(originalButtonText);
            }
        }).fail(function(xhr, status, error) {
            alert('An error occurred: ' + error);
            // Re-enable the button in case of error
            button.prop('disabled', false);
            button.text(originalButtonText);
        });
    });

    /**
     * Handle "Restore Original" button click
     */
    $(document).on('click', '.restore-original', function(e) {
        e.preventDefault();

        var button = $(this);
        var attachmentId = button.data('attachment-id');

        if (!attachmentId) {
            alert('Invalid attachment ID.');
            return;
        }

        // Confirm restoration
        if (!confirm('Are you sure you want to restore the original image? This will replace the WebP version with the original file.')) {
            return;
        }

        // Disable the button to prevent multiple clicks and update its text
        button.prop('disabled', true);
        var originalButtonText = button.text();
        button.text('Restoring...');

        // Prepare the data to be sent in the POST request
        var data = {
            action: 'webp_restore_attachment',
            nonce: webpImageOptimization.nonce,
            attachment_id: attachmentId
        };

        // Send AJAX POST request using jQuery
        $.post(webpImageOptimization.ajax_url, data, function(response) {
            if (response.success) {
                alert('Original image successfully restored.');
                // Optionally, refresh the page or update the image preview
                location.reload(); // Refresh to see changes
            } else {
                alert('Restoration failed: ' + response.data);
                // Re-enable the button in case of failure
                button.prop('disabled', false);
                button.text(originalButtonText);
            }
        }).fail(function(xhr, status, error) {
            alert('An error occurred: ' + error);
            // Re-enable the button in case of error
            button.prop('disabled', false);
            button.text(originalButtonText);
        });
    });
});

// js/media.js

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Handle "Convert to WebP" button click
     */
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.convert-to-webp');
        if (!button) return;

        e.preventDefault();

        const attachmentId = button.getAttribute('data-attachment-id');

        if (!attachmentId) {
            alert('Invalid attachment ID.');
            return;
        }

        // Disable the button and store original text
        button.disabled = true;
        const originalButtonText = button.textContent;
        button.textContent = 'Converting...';

        // Prepare the data
        const data = new URLSearchParams();
        data.append('action', 'webp_convert_attachment');
        data.append('nonce', webpImageOptimization.nonce);
        data.append('attachment_id', attachmentId);

        // Send AJAX request using fetch
        fetch(webpImageOptimization.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: data.toString()
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                alert('Image successfully converted to WebP.');
                location.reload();
            } else {
                alert('Conversion failed: ' + response.data);
                button.disabled = false;
                button.textContent = originalButtonText;
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error);
            button.disabled = false;
            button.textContent = originalButtonText;
        });
    });
});

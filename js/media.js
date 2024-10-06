document.addEventListener('DOMContentLoaded', function () {
    // Handle the Convert to WebP button click using event delegation
    document.addEventListener('click', function (e) {
        // Check if the clicked element has the ID 'convert-to-webp'
        // or is a child of an element with that ID
        const button = e.target.closest('#convert-to-webp');
        if (!button) return; // Exit if the clicked element is not the button

        e.preventDefault();

        const attachmentId = button.dataset.attachmentId;

        if (!attachmentId) {
            alert('Invalid attachment ID.');
            return;
        }

        // Disable the button to prevent multiple clicks and update its text
        button.disabled = true;
        const originalButtonText = button.textContent;
        button.textContent = 'Converting...';

        // Prepare the data to be sent in the POST request
        const data = new URLSearchParams();
        data.append('action', 'webp_convert_attachment');
        data.append('nonce', webpImageOptimization.nonce);
        data.append('attachment_id', attachmentId);

        // Send AJAX POST request using fetch
        fetch(webpImageOptimization.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: data.toString()
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                alert('Image successfully converted to WebP. The original image has NOT been deleted from your server. Please refresh to view.');

                // Optionally, update the attachment's image preview to the WebP version
                // This depends on how your Media Library displays images
                // For example:
                const mediaItem = document.getElementById('attachment-' + attachmentId);
                if (mediaItem) {
                    const img = mediaItem.querySelector('img');
                    if (img && response.data.webp_url) {
                        img.src = response.data.webp_url;
                    }
                }

                // Replace the button with a "WebP" label
                const label = document.createElement('span');
                label.className = 'webp-image-optimization__label';
                label.style.color = 'green';
                label.style.fontWeight = 'bold';
                label.textContent = 'WebP conversion succesful, refresh to view.';
                button.parentNode.replaceChild(label, button);
            } else {
                alert('Conversion failed: ' + response.data);
                // Re-enable the button in case of failure
                button.disabled = false;
                button.textContent = originalButtonText;
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error);
            // Re-enable the button in case of error
            button.disabled = false;
            button.textContent = originalButtonText;
        });
    });
});

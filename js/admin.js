document.addEventListener('DOMContentLoaded', function() {

    // JPEG Quality synchronization
    var jpegRangeInput = document.getElementById('jpeg_quality_range');
    var jpegNumberInput = document.getElementById('jpeg_quality_number');
    var jpegValueDisplay = document.getElementById('jpeg_quality_value');

    // Set initial value display
    jpegValueDisplay.textContent = jpegRangeInput.value;

    // Synchronize the values
    jpegRangeInput.addEventListener('input', function() {
        jpegNumberInput.value = jpegRangeInput.value;
        jpegValueDisplay.textContent = jpegRangeInput.value;
    });
    jpegNumberInput.addEventListener('input', function() {
        jpegRangeInput.value = jpegNumberInput.value;
        jpegValueDisplay.textContent = jpegNumberInput.value;
    });

    // PNG Compression synchronization
    var pngRangeInput = document.getElementById('png_compression_range');
    var pngNumberInput = document.getElementById('png_compression_number');
    var pngValueDisplay = document.getElementById('png_compression_value');

    // Set initial value display
    pngValueDisplay.textContent = pngRangeInput.value;

    // Synchronize the values
    pngRangeInput.addEventListener('input', function() {
        pngNumberInput.value = pngRangeInput.value;
        pngValueDisplay.textContent = pngRangeInput.value;
    });
    pngNumberInput.addEventListener('input', function() {
        pngRangeInput.value = pngNumberInput.value;
        pngValueDisplay.textContent = pngNumberInput.value;
    });

});

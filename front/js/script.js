
/*
 * プレビュー表示
 */
function previewImage() {
    const fileInput = document.getElementById('image');
    const file = fileInput.files[0];
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imagePreview = document.createElement('img');
            imagePreview.src = e.target.result;
            imagePreview.classList.add('img-fluid');
            imagePreviewContainer.innerHTML = '';
            imagePreviewContainer.appendChild(imagePreview);
        };
        reader.readAsDataURL(file);
    }
}
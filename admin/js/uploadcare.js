document.addEventListener('DOMContentLoaded', () => {
  const ucInput = document.createElement('input');
  ucInput.setAttribute('type', 'hidden');
  ucInput.setAttribute('role', 'uploadcare-uploader');

  if (document.getElementById('plupload-browse-button') instanceof HTMLElement) {
    document.getElementById('plupload-browse-button').parentElement.appendChild(ucInput);
    return;
  }

  const inlineUpload = document.querySelectorAll('.upload-ui')[0];
  if (inlineUpload instanceof HTMLElement) {
    const uploadBtn = inlineUpload.querySelectorAll('button')[0];
    if (uploadBtn instanceof HTMLElement)
      uploadBtn.parentElement.appendChild(ucInput);
  }
});

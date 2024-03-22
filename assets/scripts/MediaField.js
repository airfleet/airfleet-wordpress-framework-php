document.addEventListener('DOMContentLoaded', () => {
  initializeImageUpload();

  function initializeImageUpload() {
    const selector = '.js-image-upload';
    const imageUploads = document.querySelectorAll(selector);

    if (imageUploads.length > 0) {
      watchImageUploads(imageUploads);
    }
  }

  function watchImageUploads(imageUploads) {
    imageUploads.forEach(imageUpload => {
      watchImageUpload(imageUpload);
    });
  }

  function watchImageUpload(imageUpload) {
    imageUpload.addEventListener('click', event => {
      event.preventDefault();

      let mediaUploader;

      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      let allowMime = [];

      if (imageUpload.dataset.allowMime) {
        allowMime = imageUpload.dataset.allowMime.split(",");
      }

      mediaUploader = wp.media({
        title: 'Choose ' + imageUpload.alt,
        button: {
          text: 'Choose Image'
        },
        library: {
          type: allowMime
        },
        multiple: false
      });

      mediaUploader.on('select', () => {
        const attachment = mediaUploader.state().get('selection').first().toJSON();

        const imageMime = allowMime.includes(attachment.mime);
        const rightImageSize = validateSizes(imageUpload, attachment);

        if ((allowMime.length === 0 || imageMime) && rightImageSize.sizesOk) {
          const imageId = imageUpload.id;
          const hiddenFieldId = `${imageId}_url`;
          const hiddenField = document.querySelector(`#${hiddenFieldId}`);

          imageUpload.src = attachment.url;
          hiddenField.value = attachment.url;
        } else {
          alert(imageUpload.dataset.instructions);
        }
      });

      mediaUploader.open();
    });
  }

  function validateSizes(imageUpload, attachment) {
    const minWidth = parseInt(imageUpload.dataset.minWidth);
    const maxWidth = parseInt(imageUpload.dataset.maxWidth);
    const minHeight = parseInt(imageUpload.dataset.minHeight);
    const maxHeight = parseInt(imageUpload.dataset.maxHeight);

    const minWidthOk = !minWidth || attachment.width >= minWidth;
    const maxWidthOk = !maxWidth || attachment.width <= maxWidth;
    const minHeightOk = !minHeight || attachment.width >= minHeight;
    const maxHeightOk = !maxHeight || attachment.width <= maxHeight;

    return {
      sizesOk: minWidthOk && maxWidthOk && minHeightOk && maxHeightOk,
      sizes: {
        minWidth,
        maxWidth,
        minHeight,
        maxHeight,
      }
    };
  }
});

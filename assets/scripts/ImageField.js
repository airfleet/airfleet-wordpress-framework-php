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

      if (imageUpload.dataset.mediaTypes) {
        allowMime = imageUpload.dataset.mediaTypes.split(",");
      }

      mediaUploader = wp.media({
        title: 'Choose ' + imageUpload.alt,
        button: {
          text: 'Choose File'
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
          let alertMessage = '';

          if (allowMime.length !== 0 && !imageMime) {
            alertMessage = `You must use one of the following file types: ${imageUpload.dataset.mediaTypes}. `
          }

          if (!rightImageSize.sizesOk) {
            if (rightImageSize.sizes.minWidth && !rightImageSize.sizesOkList.minWidthOk) {
              alertMessage = ` Min-width ${rightImageSize.sizes.minWidth}px.`
            }

            if (rightImageSize.sizes.maxWidth && !rightImageSize.sizesOkList.maxWidthOk) {
              alertMessage = `${alertMessage} Max-width ${rightImageSize.sizes.maxWidth}px.`
            }

            if (rightImageSize.sizes.minHeight && !rightImageSize.sizesOkList.minHeightOk) {
              alertMessage = `${alertMessage} Min-height ${rightImageSize.sizes.minHeight}px.`
            }

            if (rightImageSize.sizes.maxHeight && !rightImageSize.sizesOkList.maxHeightOk) {
              alertMessage = `${alertMessage} Max-height ${rightImageSize.sizes.maxHeight}px.`
            }

            alertMessage = `Image size must be:${alertMessage}`
          }

          alert(alertMessage);
        }
      });

      mediaUploader.open();
    });
  }

  function validateSizes(imageUpload, attachment) {
    const minWidth = parseInt(imageUpload.dataset.minWidth) || false;
    const maxWidth = parseInt(imageUpload.dataset.maxWidth) || false;
    const minHeight = parseInt(imageUpload.dataset.minHeight) || false;
    const maxHeight = parseInt(imageUpload.dataset.maxHeight) || false;

    const minWidthOk = !minWidth || attachment.width >= minWidth;
    const maxWidthOk = !maxWidth || attachment.width <= maxWidth;
    const minHeightOk = !minHeight || attachment.height >= minHeight;
    const maxHeightOk = !maxHeight || attachment.height <= maxHeight;

    return {
      sizesOk: minWidthOk && maxWidthOk && minHeightOk && maxHeightOk,
      sizesOkList: {
        minWidthOk,
        maxWidthOk,
        minHeightOk,
        maxHeightOk,
      },
      sizes: {
        minWidth,
        maxWidth,
        minHeight,
        maxHeight,
      }
    };
  }
});

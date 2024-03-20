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

      let mediaTypes = [];

      if (imageUpload.dataset.mediaTypes) {
        mediaTypes = imageUpload.dataset.mediaTypes.split(",");
      }

      mediaUploader = wp.media({
        title: 'Choose ' + imageUpload.alt,
        button: {
          text: 'Choose Image'
        },
        library: {
          type: mediaTypes
        },
        multiple: false
      });

      mediaUploader.on('select', () => {
        const attachment = mediaUploader.state().get('selection').first().toJSON();

        const imageMime = mediaTypes.includes(attachment.mime);
        const rightImageSize = validateSizes(imageUpload, attachment);

        if ((mediaTypes.length === 0 || imageMime) && rightImageSize.sizesOk) {
          const imageId = imageUpload.id;
          const hiddenFieldId = `${imageId}_url`;
          const hiddenField = document.querySelector(`#${hiddenFieldId}`);

          imageUpload.src = attachment.url;
          hiddenField.value = attachment.url;
        } else {
          const alertMessage = getAlertMessage(rightImageSize.sizes, imageUpload.dataset.mediaTypes);
          alert(alertMessage);
        }
      });

      mediaUploader.open();
    });
  }

  function getAlertMessage(sizes, mediaTypesString) {
    let sizesText = getSizesText(sizes);

    if (mediaTypesString) {
      mediaTypesString = ` And file types ${mediaTypesString}.`;
    }

    return `${sizesText}${mediaTypesString}`
  }

  function getSizesText(sizes) {
    let sizesText = '';
    const sizesTextIntro = 'Recommended image sizes:';

    if (sizes.minWidth) {
      sizesText = `${sizesText} Min width ${sizes.minWidth}px.`;
    }

    if (sizes.maxWidth) {
      sizesText = `${sizesText} Max width ${sizes.maxWidth}px.`;
    }

    if (sizes.minHeight) {
      sizesText = `${sizesText} Min height ${sizes.minHeight}px.`;
    }

    if (sizes.maxHeight) {
      sizesText = `${sizesText} Max height ${sizes.maxHeight}px.`;
    }

    return sizesText !== '' ? `${sizesTextIntro}${sizesText}` : '';
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

'use strict';

function ucEditFile(file_id) {
  try {
    tb_remove();
  } catch(e) {}
  var file = uploadcare.fileFrom('uploaded', file_id);
  var dialog = uploadcare.openDialog(file, {crop: true}).done(ucFileDone);
}

function uploadcareMediaButton() {
  var dialog = uploadcare.openDialog().done(ucFileDone);
}

function ucStoreImg(fileInfo, callback) {
  var data = {
    'action': 'uploadcare_handle',
    'file_id': fileInfo.uuid
  };
  uploadcare.jQuery.post(ajaxurl, data, function(response) {
    if (callback) {
      callback(response);
    }
  });
}

function ucAddImg(fileInfo) {
  ucStoreImg(fileInfo, function(response) {
    console.log(response);
    var obj = uploadcare.jQuery.parseJSON(response);
    var fileUrl = obj.fileUrl;
    if (fileInfo.isImage) {
      var $img = '<img src="' + fileUrl + '" alt="' + fileInfo.name + '"/>';
      if(UPLOADCARE_CONF.original) {
        window.send_to_editor('<a href="' + fileUrl + '">' + $img + '</a>');
      } else {
        window.send_to_editor($img);
      }
    } else {
      window.send_to_editor('<a href="' + fileUrl + '">' + fileInfo.name + '</a>');
    }
    window.send_to_editor('\n');
  });
}

function ucFileDone(data) {
  uploadcare.jQuery('#content').prop('disabled', true);
  if(UPLOADCARE_MULTIPLE) {
    data.promise().done(function(fileGroupInfo) {
      var files = data.files();
      for(var idx = 0; idx < files.length; idx++) {
        var file = files[idx];
        file.done(function(fileInfo) {
          ucAddImg(fileInfo);
        });
      }
    }).always(function() {
      uploadcare.jQuery('#content').prop('disabled', false);
    });
  } else {
    var file = data;
    file.done(ucAddImg)
        .always(function() {
          uploadcare.jQuery('#content').prop('disabled', false);
        });
  }
}

function ucPostUploadUiBtn() {
  uploadcare.openDialog([], {
    multiple: true
  }).done(function(data) {
    data.promise().done(function(fileGroupInfo) {
      var files = data.files();
      var stored = 0;
      for(var idx = 0; idx < files.length; idx++) {
        var file = files[idx];
        file.done(function(data) {
          ucStoreImg(data, function(response) {
            if(wp.media) {
              var obj = uploadcare.jQuery.parseJSON(response);
              var attachment = wp.media.attachment(obj.attach_id);
              attachment.fetch();
              var library = null;
              switch(wp.media.frame._state) {
                case 'insert':
                case 'gallery':
                case 'featured-image':
                case 'library':
                  wp.media.frame.content.mode('browse');
                  library = wp.media.frame.content.mode('library').get().collection;
                break;
                case 'edit-attachment':
                  library = wp.media.frame.content.mode('library').view.library;
                break;
              }

              if(library) {
                library.add(attachment);
              }
            }
            stored++;
            if(stored == files.length) {
              // TODO: disable everything until now

              if(wp.media) {
                // switch to attachment browser
                wp.media.frame.content.mode('browse');
                // refresh attachment collection
                // no need for WP 4.7.2 but kept for older WP versions
                try {
                  updateAttachments();
                } catch(ex) {}
              } else if (adminpage == 'media-new-php') {
                location = 'upload.php';
              }
              
            }
          });
        });
      }
    });
  });
}

uploadcare.jQuery(function() {
  // add button to all inputs with .uploadcare-url-field
  uploadcare.jQuery('input.uploadcare-url-field').each(function() {
    var input = uploadcare.jQuery(this);
    var img = uploadcare.jQuery('<img />');
    var preview = function() {
      if(input.val().length > 0) {
        img.attr('src', input.val() + '-/preview/300x300/');
      }
    };
    input.before(img);
    preview();
    input.after(uploadcare.jQuery('<a class="button"><span>uc</span></a>').on('click', function() {
      uploadcare.openDialog(null, {multiple: false}).done(function(data) {
        data.done(function(fileInfo) {
          ucStoreImg(fileInfo, function() {
            input.val(fileInfo.cdnUrl);
            preview();
          });
        });
      });
    }));
  });

  // featured image stuff
  var addLink = uploadcare.jQuery('#uc-set-featured-img');
  var removeLink = uploadcare.jQuery('#uc-remove-featured-img');

  function setImg() {
    var url = addLink.data('uc-url');
    if (url) {
      addLink.html('<img src="' + url + '-/resize/255x/' + '">');
      removeLink.removeClass('hidden');
    } else {
      addLink.html('Set featured image');
      removeLink.addClass('hidden');
    }
  }

  addLink.click(function() {
    var url = addLink.data('uc-url');
    var file = null;
    if(url) {
      file = uploadcare.fileFrom('uploaded', url);
    }

    uploadcare.openDialog(file, {multiple: false}).done(function(data) {
      data.done(function(fileInfo) {
        ucStoreImg(fileInfo, function() {
          addLink.data('uc-url', fileInfo.cdnUrl);
          uploadcare.jQuery('#uc-featured-image-input').val(fileInfo.cdnUrl);
          setImg();
        });
      });
    });
  });

  removeLink.click(function() {
    uploadcare.jQuery('#uc-featured-image-input').val('');
    addLink.data('uc-url', '');
    setImg();
  });

  setImg();

  // media tab
  uploadcare.jQuery('#uploadcare-more').on('click', function() {
    uploadcare.jQuery('#uploadcare-more-container').hide();
    uploadcare.jQuery('#uploadcare-lib-container').hide();
    uploadcare.openPanel('#uploadcare-panel-container', [], {
      multiple: true,
      autostore: true
    }).done(function() {
        location.reload();
    });
  });
});

function ucEditFile(file_id) {
  try {
    tb_remove();
  } catch(e) {};
  var file = uploadcare.fileFrom('uploaded', file_id);
  var dialog = uploadcare.openDialog(file, {crop: true}).done(ucFileDone);
}

function uploadcareMediaButton() {
  var dialog = uploadcare.openDialog().done(ucFileDone);
};

function ucStoreImg(fileInfo, callback) {
  var data = {
    'action': 'uploadcare_handle',
    'file_id': fileInfo.uuid
  };
  jQuery.post(ajaxurl, data, function(response) {
    if (callback) {
      callback(response);
    }
  });
}

function ucAddImg(fileInfo) {
  ucStoreImg(fileInfo, function(response) {
    if (fileInfo.isImage) {
      var $img = '<img src="' + fileInfo.cdnUrl + '\" alt="' + fileInfo.name + '"/>';
      if(UPLOADCARE_CONF.original) {
        window.send_to_editor('<a href="' + UPLOADCARE_CDN_BASE + fileInfo.uuid + '/">' + $img + '</a>');
      } else {
        window.send_to_editor($img);
      }
    } else {
      window.send_to_editor('<a href="' + fileInfo.cdnUrl + '\">' + fileInfo.name + '</a>');
    }
    window.send_to_editor('\n');
  });
}

function ucFileDone(data) {
  jQuery('#content').prop('disabled', true);
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
      jQuery('#content').prop('disabled', false);
    });
  } else {
    var file = data;
    file.done(ucAddImg)
        .always(function() {
          jQuery('#content').prop('disabled', false);
        });
  }
}

jQuery(function() {
  // add button to all inputs with .uploadcare-url-field
  jQuery('input.uploadcare-url-field').each(function() {
    var input = jQuery(this);
    var img = jQuery('<img />');
    var preview = function() {
      if(input.val().length > 0) {
        img.attr('src', input.val() + '-/preview/300x300/');
      }
    }
    input.before(img);
    preview();
    input.after(jQuery('<a class="button"><span>uc</span></a>').on('click', function() {
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
  var addLink = jQuery('#uc-set-featured-img');
  var removeLink = jQuery('#uc-remove-featured-img');

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
          jQuery('#uc-featured-image-input').val(fileInfo.cdnUrl);
          setImg();
        });
      });
    });
  });

  removeLink.click(function() {
    jQuery('#uc-featured-image-input').val('');
    addLink.data('uc-url', '');
    setImg();
  });

  setImg();


  // media tab
  jQuery('#uploadcare-more').on('click', function() {
    jQuery('#uploadcare-more-container').hide();
    jQuery('#uploadcare-lib-container').hide();
    uploadcare.openPanel('#uploadcare-panel-container', [], {
      multiple: true,
      autostore: true,
    }).done(function() {
        location.reload();
    });
  });
});

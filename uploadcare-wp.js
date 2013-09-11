function ucEditFile(file_id) {
  try {
    tb_remove();
  } catch(e) {};
  var file = uploadcare.fileFrom('uploaded', file_id);
  var dialog = uploadcare.openDialog(file).done(ucFileDone);
}

function uploadcareMediaButton() {
  var dialog = uploadcare.openDialog().done(ucFileDone);
};  

function ucAddImg(fileInfo) {
  var data = {
    'action': 'uploadcare_handle',
    'file_id': fileInfo.uuid
  };
  jQuery.post(ajaxurl, data, function(response) {
    if (fileInfo.isImage) {
      var $img = '<img src="' + fileInfo.cdnUrl + '\" alt="' + fileInfo.name + '"/>';
      if(UPLOADCARE_WP_ORIGINAL) {
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

// add button to all inputs with .uploadcare-url-field
jQuery(function() {
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
          input.val(fileInfo.cdnUrl);
          preview();
        });
      });
    }));
  });
});

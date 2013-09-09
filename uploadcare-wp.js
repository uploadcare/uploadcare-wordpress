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
      jQuery('#content').prop('disabled', false);
    }).fail(function() {
      jQuery('#content').prop('disabled', false);
    });
  } else {
    file = data;
    file.done(function(fileInfo) {
      ucAddImg(fileInfo);
      jQuery('#content').prop('disabled', false);
    }).fail(function() {
      jQuery('#content').prop('disabled', false);
    });
  }
}

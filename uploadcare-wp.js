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

function ucFileDone(data) {
  if(UPLOADCARE_MULTIPLE) {
    data.promise().done(function(fileGroupInfo) {
      console.log(fileGroupInfo);
      var files = data.files();
      window.send_to_editor('[uc-gallery]\n');
      for(var idx = 0; idx < files.length; idx++) {
        var file = files[idx];
        file.done(function(fileInfo) {
          window.send_to_editor(fileInfo.cdnUrl + '\n');
        });
      }
      window.send_to_editor('[/uc-gallery]\n');
    });
  } else {
    file = data;
    file.done(function(fileInfo) {
        _file_id = fileInfo.uuid;
        url = fileInfo.cdnUrl;
        var data = {
          'action': 'uploadcare_handle',
          'file_id': _file_id
        };
        jQuery.post(ajaxurl, data, function(response) {
            if(UPLOADCARE_WP_ORIGINAL) {
                if (fileInfo.isImage) {
                    window.send_to_editor('<a href=\"https://ucarecdn.com/'+fileInfo.uuid+'/\"><img src=\"'+url+'\" /></a>');
                } else {
                    window.send_to_editor('<a href=\"'+url+'\">'+fileInfo.name+'</a>');
                }
            } else {
                if (fileInfo.isImage) {
                    window.send_to_editor('<img src=\"'+url+'\" />');
                } else {
                    window.send_to_editor('<a href=\"'+url+'\">'+fileInfo.name+'</a>');
                }
            }
        });
    });
}}

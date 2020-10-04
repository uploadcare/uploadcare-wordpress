jQuery(function() {
  var widget = uploadcare.MultipleWidget('.uploadcare-uploader');
  widget.onChange(function(groupInfo) {
    if(groupInfo) {
      var files = groupInfo.files();
        for(var idx = 0; idx < files.length; idx++) {
          var file = files[idx];
          file.done(function(fileInfo) {
            ucStoreUserImg(fileInfo);
        });
      }
    }
  });
});


function ucStoreUserImg(fileInfo, callback) {
  var data = {
    'action': 'uploadcare_shortcode_handle',
    'file_id': fileInfo.uuid,
    'post_id': jQuery('.uploadcare-uploader').data('post-id')
  };
  jQuery.post(UPLOADCARE_CONF.ajaxurl, data, function(response) {
    if (callback) {
      callback(response);
    }
  });
}

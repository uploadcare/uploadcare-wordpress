import './uc-picker-wrapper.scss';
import UcEditor from './imageEdit/editorLoader';

const cdnRegex = new RegExp('ucarecdn.com');
const wpEditor = window.imageEdit;

// /*
window.addEventListener('DOMContentLoaded', evt => {
    console.log(wp.media.model.Attachment.get())
})
// */

/*
window.jQuery(document).on('image-editor-ui-ready', evt => {
    console.log(wpEditor._view.model)
    if (!cdnRegex.test(wpEditor._view.model.attributes.url)) return false;

    console.log(wpEditor._view.model.attributes)
})
*/

/*

wpEditor.init = function(postId) {
    window.imageEdit.postid = postId;
    const model = wpEditor._view.model;

    if (!cdnRegex.test(model.attributes.url))
        return false;

    console.log(UcEditor)
}
*/

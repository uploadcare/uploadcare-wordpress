import './uc-picker-wrapper.scss';
import UcEditor from './imageEdit/editorLoader';
import UploadToLibrary from './UploadToLibrary';

const cdnRegex = new RegExp('ucarecdn.com');
const wpEditor = window.imageEdit;

wpEditor.init = function(postId) {
    window.imageEdit.postid = postId;
    const model = wpEditor._view.model;

    if (!cdnRegex.test(model.attributes.url)) return false;

    const mediaElement = document.getElementById(`media-head-${postId}`);
    if (mediaElement === null) return false;

    const wrapperObject = document.getElementById(`media-head-${postId}`).parentElement.parentElement;
    (new UcEditor()).showPanel(wrapperObject, model)
    .then(() => { window.location.search = ''; })
    .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    const uploader = new UploadToLibrary();
    if (document.getElementById('uploadcare-post-upload-ui-btn') === null) return false;

    document.getElementById('uploadcare-post-upload-ui-btn').addEventListener('click', (e) => {
        e.preventDefault();

        uploader.upload()
        .then(() => { window.location.pathname = window.location.pathname.split('/').slice(0, window.location.pathname.split('/').length - 1).concat(['upload.php']).join('/') })
        .catch(() => {});
    })
})

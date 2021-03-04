import './uc-picker-wrapper.scss';
import UcEditor from './imageEdit/editorLoader';
import UploadToLibrary from './UploadToLibrary';

const wpEditor = window.imageEdit;

wpEditor.init = function(postId) {
    window.imageEdit.postid = postId;
    const ucEditor = new UcEditor();
    const model = wpEditor._view.model;

    if (!new RegExp(ucEditor.getCDN().replace(/https?:\/\//i, '')).test(model.attributes.url)) return false;

    const mediaElement = document.getElementById(`media-head-${postId}`);
    if (mediaElement === null) return false;

    const wrapperObject = document.getElementById(`media-head-${postId}`).parentElement.parentElement;
    ucEditor.showPanel(wrapperObject, model)
    .then(() => { window.location.search = ''; })
    .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    const uploader = new UploadToLibrary();
    if (document.getElementById('uploadcare-post-upload-ui-btn') === null) return false;

    document.getElementById('uploadcare-post-upload-ui-btn').addEventListener('click', async (e) => {
        e.preventDefault();

        uploader.upload().finally(() => {
            const loadingScreen = document.querySelector('.uploadcare-loading-screen');
            if (loadingScreen) loadingScreen.classList.add('uploadcare-hidden');
        });
    })
})

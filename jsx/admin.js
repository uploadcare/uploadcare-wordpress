import './uc-picker-wrapper.scss';
import UcEditor from './imageEdit/editorLoader';
import UploadToLibrary from './UploadToLibrary';

const wpEditor = window.imageEdit;

wpEditor.init = function(postId) {
    window.imageEdit.postid = postId;
    const ucEditor = new UcEditor();

    let imgSrc = null;
    let imgSrcObject = document.querySelector('img.thumbnail');
    if (imgSrcObject !== null) {
        imgSrc = imgSrcObject.getAttribute('src') || null;
        const regex = /(https?:\/\/)(.+\..+?\/)([a-z0-9-]+?\/)(.+)/gm;
        const subst = `$1$2$3`;

        if (imgSrc !== null) imgSrc = imgSrc.replace(regex, subst);
    }
    if (typeof wpEditor._view !== 'undefined') {
        imgSrc = wpEditor._view.model.attributes.url;
    }
    if (imgSrc === null) return false;

    if (!new RegExp(ucEditor.getCDN().replace(/https?:\/\//i, '')).test(imgSrc)) return false;

    const mediaElement = document.getElementById(`media-head-${postId}`);
    if (mediaElement === null) return false;

    const wrapperObject = document.getElementById(`media-head-${postId}`).parentElement.parentElement;
    ucEditor.showPanel(wrapperObject, imgSrc)
    .then(() => { window.location.assign('/wp-admin/upload.php'); })
    .catch(() => {});
}

window.ucb_click = function (el) {
    const uploader = new UploadToLibrary();
    uploader.upload().finally(() => {
        const loadingScreen = document.querySelector('.uploadcare-loading-screen');
        if (loadingScreen) loadingScreen.classList.add('uploadcare-hidden');

        // const mlButton = document.getElementById('menu-item-browse');
        // if (mlButton) mlButton.click();
    });
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

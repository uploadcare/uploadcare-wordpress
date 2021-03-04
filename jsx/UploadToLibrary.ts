import uploadcare from 'uploadcare-widget/uploadcare'
import config from './uc-config';
import UcConfig from './interfaces/UcConfig';
import FileInfoResponse from './interfaces/FileInfoResponse';
import UcUploader from './UcUploader';
import { __ } from '@wordpress/i18n';
import uploadcareTabEffects from 'uploadcare-widget-tab-effects'

export default class UploadToLibrary {
    private readonly config: UcConfig;
    private readonly uploader: UcUploader;

    constructor() {
        config.config.previewStep = Boolean(config.config.previewStep);
        config.config.multiple = true; // always made a multiple uploader
        uploadcare.registerTab('preview', uploadcareTabEffects);
        this.config = config.config;
        this.config.imagesOnly = Boolean(config.config.imagesOnly);
        this.uploader = new UcUploader(this.config);
    }

    public static getLibraryLink(): string {
        return window.location.pathname.split('/').slice(0, window.location.pathname.split('/').length - 1).concat(['upload.php']).join('/');
    }

    protected addMedia(file: FileInfoResponse) {
        const block = document.getElementById('media-items');
        if (!(block instanceof HTMLDivElement)) return;

        const added = document.createElement('div');
        added.classList.add('media-item');
        added.classList.add('child-of-0');
        added.classList.add('open');
        added.setAttribute('id', `media-item-${file.uuid}`);

        const img = new Image();
        img.classList.add('pinkynail');
        if (file.isImage) {
            img.src = file.cdnUrl || ''
        } else {
            img.src = '/wp-includes/images/media/document.png';
        }
        added.appendChild(img);

        const libLink = document.createElement('a');
        libLink.classList.add('edit-attachment');
        libLink.setAttribute('href', UploadToLibrary.getLibraryLink())
        libLink.innerText = __('Back to library')
        added.appendChild(libLink);

        const filenameBlock = document.createElement('div');
        filenameBlock.classList.add('filename');
        const filenameSpan = document.createElement('span');
        filenameSpan.classList.add('title')
        filenameSpan.innerText = file.name || 'file';
        filenameBlock.appendChild(filenameSpan);
        added.appendChild(filenameBlock);

        block.appendChild(added);
    }

    public async upload(): Promise<any> {
        this.uploader.showLoading();
        try {
            const data = await uploadcare.openDialog([], null, this.config).progress().done();

            return Promise.all(data.files()).then(files => {
                files.forEach(async f => {
                    this.uploader.storeImage(f as FileInfoResponse).then((info) => { this.addMedia(info) });
                });
            });
        } catch (err) {
            if (err === 'upload') {
                this.uploader.makeErrorBlock(__('Unable to upload file'));
            }
            return Promise.reject(err);
        }
    }
}

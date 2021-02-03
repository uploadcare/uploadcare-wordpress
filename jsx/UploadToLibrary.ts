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
        this.uploader = new UcUploader(this.config);
    }

    public async upload(): Promise<any> {
        try {
            const data = await uploadcare.openDialog([], null, this.config).done();
            return Promise.all(data.files()).then(files => {
                files.forEach(async f => {
                    await this.uploader.storeImage(f as FileInfoResponse);
                });

                return files;
            });
        } catch (err) {
            if (err === 'upload') { this.uploader.makeErrorBlock(__('Unable to upload file')) }
            return Promise.reject(err);
        }
    }
}

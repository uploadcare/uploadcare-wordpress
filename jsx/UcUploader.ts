import uploadcare from 'uploadcare-widget/uploadcare'
import FileInfoResponse from './interfaces/FileInfoResponse';
import UcConfig from './interfaces/UcConfig';
import uploadcareTabEffects from 'uploadcare-widget-tab-effects'
import effectsConfig from './effects'

export default class UcUploader {
    private loadingScreen: HTMLDivElement = document.querySelector('.uploadcare-loading-screen') || document.createElement('div');
    private spinnerBlock: HTMLDivElement = document.querySelector('.uc-loader') || document.createElement('div');
    private errorBlockWrapper: HTMLDivElement = document.querySelector('.uc-error') || document.createElement('div');
    private errorContent: HTMLParagraphElement = document.createElement('p');
    private readonly config: UcConfig;

    constructor(config: UcConfig) {
        config.previewStep = Boolean(config.previewStep);
        config.multiple = Boolean(config.multiple);
        this.config = config;
        uploadcare.start({effects: effectsConfig.config});

        this.errorBlockWrapper.classList.add('uc-error');
        this.errorBlockWrapper.classList.add('uploadcare-hidden');

        this.loadingScreen.classList.add('uploadcare-loading-screen');
        this.loadingScreen.classList.add('uploadcare-hidden');

        this.spinnerBlock.classList.add('uc-loader');
        this.loadingScreen.append(this.spinnerBlock);
        document.body.append(this.loadingScreen);
    }

    public showLoading(): void {
        this.loadingScreen.classList.remove('uploadcare-hidden');
    }

    public hideLoading(): void {
        this.loadingScreen.classList.add('uploadcare-hidden')
    }

    async upload(mediaUrl?: string): Promise<FileInfoResponse> {
        this.showLoading();
        uploadcare.registerTab('preview', uploadcareTabEffects)
        const dialogPreferences = this.config;
        dialogPreferences.multiple = false;
        dialogPreferences.imagesOnly = true;
        dialogPreferences.previewStep = true;

        const initFile = mediaUrl ? [uploadcare.fileFrom('uploaded', mediaUrl)] : []
        try {
            const data = await uploadcare.openDialog(initFile, null, dialogPreferences).done();
            data.nonce = dialogPreferences.nonce;
            return await this.storeImage(data);
        } catch (err) {
            if (err === 'upload') {
                this.makeErrorBlock('Unable to upload file');
                return Promise.reject(err);
            }
            return Promise.reject();
        } finally {
            this.hideLoading();
        }
    }

    public makeErrorBlock(errorText: string): void {
        const wrapper = document.querySelector('div.block-editor__typewriter') || document.body;

        wrapper.append(this.errorBlockWrapper);
        this.errorBlockWrapper.classList.remove('uploadcare-hidden');
        const errBlock = document.createElement('div');
        errBlock.classList.add('uc-error__block');

        this.errorContent.innerText = errorText;
        errBlock.append(this.errorContent);
        this.errorBlockWrapper.append(errBlock);
    }

    public storeImage(file: FileInfoResponse): Promise<FileInfoResponse> {
        const data = new FormData();
        let nonce = this.config.nonce;
        data.append('action', 'uploadcare_handle');
        data.append('file_url', file.originalUrl as string);
        data.append('nonce', nonce);
        data.append('uploadcare_url_modifiers', file.cdnUrlModifiers as string);

        return window.fetch(this.config.ajaxurl, {
            method: 'POST',
            redirect: 'follow',
            body: data,
        }).then(response => {
            if (response.status !== 200) {
                this.makeErrorBlock(`File ${file.name} not uploaded to cloud`)
                throw new Error('Unable to get valid response from Wordpress engine');
            }
            return response.json().then(d => {
                file.attach_id = d.attach_id;

                return file;
            })
        }).finally(() => {
            this.loadingScreen.classList.add('uploadcare-hidden')
        });
    }
}

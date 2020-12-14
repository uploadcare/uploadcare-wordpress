import uploadcare from 'uploadcare-widget/uploadcare'
import FileInfoResponse from "./FileInfoResponse";
import uploadcareTabEffects from 'uploadcare-widget-tab-effects'

interface UcConfig {
    ajaxurl: string;
    cdnBase: string;
    previewStep: boolean;
    public_key: string;
    secureSignature?: string;
    secureExpire?: string;
    tabs: string;
}

export default class UcUploader {
    private loadingScreen: HTMLDivElement = document.createElement('div');
    private spinnerBlock: HTMLDivElement = document.createElement('div');
    private errorBlockWrapper: HTMLDivElement = document.createElement('div');
    private errorContent: HTMLParagraphElement = document.createElement('p');
    private config: UcConfig;

    constructor(config: UcConfig) {
        config.previewStep = Boolean(config.previewStep);
        this.config = config;

        this.errorBlockWrapper.classList.add('uc-error');
        this.errorBlockWrapper.classList.add('uploadcare-hidden');

        this.loadingScreen.classList.add('uploadcare-loading-screen');
        this.loadingScreen.classList.add('uploadcare-hidden');

        this.spinnerBlock.classList.add('uc-loader');
        this.loadingScreen.append(this.spinnerBlock);
        document.body.append(this.loadingScreen);
    }

    async upload(mediaUrl?: string): Promise<FileInfoResponse> {
        this.loadingScreen.classList.remove('uploadcare-hidden')
        uploadcare.registerTab('preview', uploadcareTabEffects)

        const initFile = mediaUrl ? [uploadcare.fileFrom('uploaded', mediaUrl)] : []
        try {
            const data = await uploadcare.openDialog(initFile, null, {multiple: false}).done();
            return await this.storeImage(data);
        } catch (err) {
            if (err === 'upload') {
                this.makeErrorBlock('Unable to upload file');
                return Promise.reject(err);
            }
            return Promise.reject();
        } finally {
            this.loadingScreen.classList.add('uploadcare-hidden')
        }
    }

    private makeErrorBlock(errorText: string): void {
        const wrapper = document.querySelector('div.block-editor__typewriter') || document.body;

        wrapper.append(this.errorBlockWrapper);
        this.errorBlockWrapper.classList.remove('uploadcare-hidden');
        const errBlock = document.createElement('div');
        errBlock.classList.add('uc-error__block');

        this.errorContent.innerText = errorText;
        errBlock.append(this.errorContent);
        this.errorBlockWrapper.append(errBlock);
    }

    private storeImage(file: FileInfoResponse): Promise<FileInfoResponse> {
        const data = new FormData();
        data.append('action', 'uploadcare_handle');
        data.append('file_url', file.cdnUrl as string);

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

import uploadcare from 'uploadcare-widget/uploadcare'
import FileInfoResponse from "./FileInfoResponse";

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
    }

    async upload(): Promise<any> {
        document.body.append(this.loadingScreen);
        this.loadingScreen.classList.remove('uploadcare-hidden')

        return await uploadcare.openDialog([], null, {multiple: false})
            .done(data => {
                return data.then((fileInfo: FileInfoResponse) => {
                    return this.storeImage(fileInfo).then((fi: FileInfoResponse) => fi);
                }).fail((reason, info) => {
                    this.makeErrorBlock(`File ${info.name} not uploaded to cloud`)
                    return Promise.reject()
                }).always(() => {
                    this.loadingScreen.classList.add('uploadcare-hidden')
                })
            }).fail(() => {
                this.loadingScreen.classList.add('uploadcare-hidden')
                return Promise.reject();
            });
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
        data.append('file_url', file.cdnUrl);

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

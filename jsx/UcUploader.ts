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
        document.body.append(this.loadingScreen);
    }

    async multiUpload(/*init: Array<any>*/): Promise<FileInfoResponse[]> {
        const initFiles = [
            uploadcare.fileFrom('uploaded', '586eabc1-91ff-4942-adb2-e89a60893bc0'),
            uploadcare.fileFrom('uploaded', 'bebd4fb4-e106-4adb-968d-a12e253ce5e7'),
            uploadcare.fileFrom('uploaded', 'ddcbc84e-ee4f-4126-a0d5-46535a03a172'),
        ];

        // todo https://uploadcare.com/docs/file_uploader_api/files_uploads/#file-new-instance
        const data = await uploadcare.openDialog(initFiles, null, {multiple: true}).done();
        return Promise.all(data.files())

        // return files.forEach((file: Promise<FileInfoResponse>) => file.then(res => {
        //     console.log(res)
        //
        //     return res;
        // }));
    }

    async upload(): Promise<FileInfoResponse> {
        this.loadingScreen.classList.remove('uploadcare-hidden')

        try {
            const data = await uploadcare.openDialog([], null, {multiple: false}).done();
            return await this.storeImage(data);
        } catch (err) {
            if (typeof err !== 'undefined') {
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

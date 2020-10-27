import uploadcare from 'uploadcare-widget/uploadcare'

interface UcConfig {
    ajaxurl: string;
    cdnBase: string;
    previewStep: boolean;
    public_key: string;
    secureSignature?: string;
    secureExpire?: string;
    tabs: string;
}

interface ImageInfo {
    color_mode: string;
    datetime_original?: Date;
    dpi?: number;
    format: string;
    geo_location?: [lat: number, lng: number],
    height: number;
    width: number;
    orientation?: string
    sequence: boolean;
}

interface FileInfoResponse {
    cdnUrl: string;
    originalUrl: string;
    cdnUrlModifiers?: string;
    isImage: boolean;
    isStored: boolean;
    mimeType: string;
    name: string;
    originalImageInfo?: ImageInfo;
    size: number;
    sourceInfo: { source: string, file: File };
    uuid: string;
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

    upload(): Promise<FileInfoResponse> {
        document.body.append(this.loadingScreen);
        this.loadingScreen.classList.remove('uploadcare-hidden')

        return uploadcare.openDialog([], null, {multiple: false})
            .done(data => {
                this.loadingScreen.classList.remove('uploadcare-hidden');
                return data.promise().done((fileInfo: FileInfoResponse) => {
                        this.storeImage(fileInfo).then(json => {
                            this.loadingScreen.classList.add('uploadcare-hidden');
                            return json;
                        }).catch(err => {
                            this.makeErrorBlock(err);
                        }).finally(() => {
                            this.loadingScreen.classList.add('uploadcare-hidden')
                        });
                    })
            })
            .fail(() => {
                this.loadingScreen.classList.add('uploadcare-hidden')
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

    private storeImage(file: FileInfoResponse): Promise<Response> {
        const data = new FormData();
        data.append('action', 'uploadcare_handle');
        data.append('file_url', file.cdnUrl);

        return window.fetch(this.config.ajaxurl, {
            method: 'POST',
            redirect: 'follow',
            body: data,
        }).then(response => {
            if (response.status !== 200) {
                throw new Error('Unable to get valid response from Wordpress engine');
            }
            return response.json()
        });
    }
}

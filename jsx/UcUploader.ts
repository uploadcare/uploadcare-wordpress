import uploadcare from 'uploadcare-widget'

interface UcConfig {
    ajaxurl: string;
    cdnBase: string;
    previewStep: boolean;
    public_key: string;
    secureSignature?: string;
    secureExpire?: string;
    tabs: string;
}

export default class UcUploader
{
    private loadingScreen: HTMLDivElement = document.createElement('div');

    constructor(config: UcConfig) {
        config.previewStep = Boolean(config.previewStep);

        this.loadingScreen.classList.add('uploadcare-loading-screen');
        this.loadingScreen.classList.add('uploadcare-hidden')
    }


    upload(): void {
        document.body.append(this.loadingScreen)

        uploadcare.openDialog([], { multiple: false }).done((data) => {
            document.querySelector('.uploadcare-loading-screen')
        })
    }
}

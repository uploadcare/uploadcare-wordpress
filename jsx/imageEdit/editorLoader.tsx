import uploadcare from 'uploadcare-widget/uploadcare'
import config from '../uc-config';
import UcConfig from '../interfaces/UcConfig';
import WpMediaModel from '../interfaces/WpMediaModel';
import uploadcareTabEffects from 'uploadcare-widget-tab-effects'
import UcUploader from '../UcUploader';
import FileInfoResponse from '../interfaces/FileInfoResponse';

export default class UcEditor {
    private panelPlaceholder: HTMLDivElement = document.createElement('div');
    private readonly config: UcConfig;
    private readonly uploader: UcUploader;

    constructor() {
        this.config = config.config;
        this.config.imagesOnly = true;
        this.panelPlaceholder.setAttribute('id', 'uc-panel-placeholder');
        this.uploader = new UcUploader(this.config);
    }

    public getCDN(): string {
        return this.config.cdnBase;
    }

    private static registerStyle(): void {
        const customStyle = document.createElement('style');
        customStyle.innerHTML = '.media-modal * { box-sizing: border-box; } .uploadcare--panel { min-height: 88vh; }'
        document.head.appendChild(customStyle);
    }

    public async showPanel(wrapper: any, url: string) {
        if (!(wrapper instanceof HTMLDivElement))
            return false;
        uploadcare.registerTab('preview', uploadcareTabEffects);
        UcEditor.registerStyle();

        wrapper.innerHTML = '';
        wrapper.style.padding = '1rem';
        wrapper.appendChild(this.panelPlaceholder);

        const ucFile: FileInfoResponse = uploadcare.fileFrom('uploaded', url);

        const localConfig = this.config;
        localConfig.imagesOnly = true;
        localConfig.multiple = false;

        const data = await uploadcare.openPanel(this.panelPlaceholder, ucFile, localConfig).done();

        return this.uploader.storeImage(data as FileInfoResponse);
    }
}

import uploadcare from 'uploadcare-widget/uploadcare'
import config from '../uc-config';
import UcConfig from '../interfaces/UcConfig';
import WpMediaModel from '../interfaces/WpMediaModel';
import uploadcareTabEffects from 'uploadcare-widget-tab-effects'
import UcUploader from '../UcUploader';
import FileInfoResponse from '../interfaces/FileInfoResponse';

config.config.imagesOnly = true;

export default class UcEditor {
    private panelPlaceholder: HTMLDivElement = document.createElement('div');
    private readonly config: UcConfig;
    private readonly uploader: UcUploader;

    constructor() {
        this.config = config.config;
        this.panelPlaceholder.setAttribute('id', 'uc-panel-placeholder');
        this.uploader = new UcUploader(this.config);
    }

    private static registerStyle(): void {
        const customStyle = document.createElement('style');
        customStyle.innerHTML = '.media-modal * { box-sizing: border-box; } .uploadcare--panel { min-height: 88vh; }'
        document.head.appendChild(customStyle);
    }

    public async showPanel(wrapper: any, model: WpMediaModel) {
        if (!(wrapper instanceof HTMLDivElement))
            return false;
        uploadcare.registerTab('preview', uploadcareTabEffects);
        UcEditor.registerStyle();

        wrapper.innerHTML = '';
        wrapper.style.padding = '1rem';
        wrapper.appendChild(this.panelPlaceholder);

        const ucFile: FileInfoResponse = uploadcare.fileFrom('uploaded', model.attributes.url);

        const localConfig = this.config;
        localConfig.imagesOnly = true;
        localConfig.multiple = false;

        const data = await uploadcare.openPanel(this.panelPlaceholder, ucFile, localConfig).done();

        return this.uploader.storeImage(data as FileInfoResponse);
    }
}

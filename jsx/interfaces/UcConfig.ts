export default interface UcConfig {
    ajaxurl: string;
    cdnBase: string;
    previewStep: boolean;
    public_key: string;
    secureSignature?: string;
    secureExpire?: string;
    tabs: string;
    imagesOnly: boolean;
    multiple: boolean;
    effects: Array<string>;
    nonce: string;
}

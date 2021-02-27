// params passed from Wordpress

UPLOADCARE_PUBLIC_KEY = WP_UC_PARAMS.public_key;
UPLOADCARE_MULTIPLE = (WP_UC_PARAMS.multiple === true);
UPLOADCARE_TABS = WP_UC_PARAMS.tabs;
UPLOADCARE_PREVIEW_STEP = (WP_UC_PARAMS.previewStep === true);

UPLOADCARE_CDN_BASE = WP_UC_PARAMS.cdnBase;

UPLOADCARE_CONF = {
    original: (WP_UC_PARAMS.original === true),
    ajaxurl: WP_UC_PARAMS.ajaxurl,
};

if(WP_UC_PARAMS.secureSignature && WP_UC_PARAMS.secureExpire) {
    UPLOADCARE_SECURE_SIGNATURE = WP_UC_PARAMS.secureSignature
    UPLOADCARE_SECURE_EXPIRE = WP_UC_PARAMS.secureExpire
}

if(WP_UC_PARAMS.effects) {
    uploadcare.start({
        effects: WP_UC_PARAMS.effects,
    });
}

// params passed from Wordpress

UPLOADCARE_PUBLIC_KEY = WP_UC_PARAMS.public_key;
UPLOADCARE_MULTIPLE = (WP_UC_PARAMS.multiple === 'true');
UPLOADCARE_TABS = WP_UC_PARAMS.tabs;
UPLOADCARE_PREVIEW_STEP = (WP_UC_PARAMS.previewStep === 'true');

UPLOADCARE_CONF = {
    original: (WP_UC_PARAMS.original === 'true'),
    ajaxurl: WP_UC_PARAMS.ajaxurl,
};

if(WP_UC_PARAMS.effects) {
    uploadcare.start({
        effects: WP_UC_PARAMS.effects,
    });
}

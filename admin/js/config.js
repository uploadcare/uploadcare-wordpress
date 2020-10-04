const UPLOADCARE_PUBLIC_KEY = WP_UC_PARAMS.public_key;
const UPLOADCARE_MULTIPLE = (WP_UC_PARAMS.multiple === 'true');
const UPLOADCARE_TABS = WP_UC_PARAMS.tabs;
const UPLOADCARE_PREVIEW_STEP = (WP_UC_PARAMS.previewStep === 'true');
const UPLOADCARE_CDN_BASE = WP_UC_PARAMS.cdnBase;

const UPLOADCARE_CONF = {
  original: (WP_UC_PARAMS.original === 'true'),
  ajaxurl: WP_UC_PARAMS.ajaxurl,
};

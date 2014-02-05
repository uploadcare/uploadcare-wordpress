// params passed from Wordpress
UPLOADCARE_PUBLIC_KEY = WP_UC_PARAMS.public_key;
UPLOADCARE_WP_ORIGINAL = (WP_UC_PARAMS.original === 'true');
UPLOADCARE_MULTIPLE = (WP_UC_PARAMS.multiple === 'true');


console.log(WP_UC_PARAMS);

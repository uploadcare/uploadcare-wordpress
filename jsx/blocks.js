import enhanceMediaPlaceholder from "./UcButton";
import "./index.scss";

wp.hooks.addFilter('editor.MediaPlaceholder', 'uploadcare/enhance-media-placeholder', enhanceMediaPlaceholder);

import React from "react/index"
import { MediaPlaceholder } from '@wordpress/block-editor';
import { Button } from "@wordpress/components";
import UcUploader from "./UcUploader";
import config from './uc-config'

import "./index.scss";

const { __ } = wp.i18n;
const uploader = new UcUploader(config.config);

const upload = () => {
  uploader.upload()
}


class UcButton extends React.Component
{
  render() {
    return <div className="uploadcare-picker">
      <Button
        className = 'uploadcare-picker__button'
        onClick={upload}
      >
        { __('Upload via Uploadcare') }
      </Button>
    </div>
  }
}

const enhanceMediaPlaceholder = ( MediaPlaceholder ) => {
  return ( props ) => {
    return (
      <MediaPlaceholder { ...props } disableDropZone>
        <UcButton />
      </MediaPlaceholder>
    );
  }
}

wp.hooks.addFilter(
  'editor.MediaPlaceholder',
  'my/plugin/enhance-media-placeholder',
  enhanceMediaPlaceholder,
);

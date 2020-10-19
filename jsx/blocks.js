import { MediaPlaceholder } from '@wordpress/block-editor';
import { Button } from "@wordpress/components";
import "./index.scss";

const { __ } = wp.i18n;

class UcButton extends React.Component
{
  render() {
    return <div className="uploadcare-picker">
      <Button
        className = 'uploadcare-picker__button'
        href = 'javascript:ucPostUploadUiBtn();'
      >
        { __('Upload via Uploadcare') }
      </Button>
    </div>
  }
}

function enhanceMediaPlaceholder( MediaPlaceholder ) {
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

/*
(() => {
  const { __ } = wp.i18n;
  const { PanelBody } = wp.components;
  const { createHigherOrderComponent } = wp.compose;
  const { AlignmentToolbar, BlockControls } = wp.blockEditor;
  const { Fragment } = wp.element;
  const { addFilter } = wp.hooks;

  const withGalleryExtension = createHigherOrderComponent(BlockEdit => {
    return (props) => {
      if ( 'core/image' === props.name ) {
        console.log(props)
      }

      return <BlockEdit { ...props } />;
    }
  }, 'withGalleryExtension');

  addFilter( 'editor.BlockEdit', 'themeisle-gutenberg/gallery-extension', withGalleryExtension );
})();
*/

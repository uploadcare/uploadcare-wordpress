import { registerBlockType } from '@wordpress/blocks';
import React from "react/index";
import { Dashicon } from '@wordpress/components';
import UcUploader from "./UcUploader";
import config from './uc-config';
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

const uploader = new UcUploader(config.config);

const icon = () => (
    <Dashicon icon={'format-image'}/>
)

registerBlockType('uploadcare/image', {
    title: 'Uploadcare Image',
    category: 'media',
    icon: icon(),
    attributes: {
        title: {
            type: 'array',
            source: 'children',
            selector: 'figcaption',
        },
        mediaID: {
            type: 'number',
        },
        mediaURL: {
            type: 'string',
            source: 'attribute',
            selector: 'img',
            attribute: 'src',
        },
    },
    example: {
        attributes: {
            title: __('Uploadcare', 'uploadcare'),
            mediaURL: 'https://ucarecdn.com/6c5b97ee-4ce9-490f-92e9-50cba0271917/intelligence.svg',
            mediaID: '0000'
        }
    },
    edit(props) {
        const {className, attributes: {title, mediaID, mediaURL}, setAttributes} = props;
        const onChangeTitle = (value) => { setAttributes({title: value}); };
        const setImage = () => {
            uploader.upload(false).then((fileInfo) => onSelectImage(fileInfo)).catch(() => {});
        }
        const onSelectImage = (media) => {
            setAttributes({
                mediaURL: media.cdnUrl,
                mediaID: media.attach_id,
            });
        };

        return <figure className={ className }>
            {mediaID ? <img alt={title} src={mediaURL} /> : null}
            <RichText tagName={'figcaption'} value={title} onChange={onChangeTitle}/>
            <Button
                className='uploadcare-picker__button'
                onClick={setImage}
            >
                { __('Upload via Uploadcare', 'uploadcare') }
            </Button>
        </figure>;
    },
    save(props) {
        const {className, attributes: {title, mediaID, mediaURL} } = props;

        return <figure className={ className }>
            { mediaURL ? (<img id={mediaID} src={mediaURL} className={'uploadcare-image'} alt={title} />) : null }
            <RichText.Content tagName="figcaption" value={title} />
        </figure>
    },
});

export default registerBlockType;

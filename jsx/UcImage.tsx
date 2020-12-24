import { registerBlockType } from '@wordpress/blocks';
import React from 'react/index';
import UcUploader from './UcUploader';
import config from './uc-config';
import { __ } from '@wordpress/i18n';
import { RichText, MediaUpload } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import FileInfoResponse from './FileInfoResponse';
import imageIcon from './icons/image';
import WpMedia from './WpMedia';

config.config.imagesOnly = true;
const uploader = new UcUploader(config.config);

const wrapperStyle = {
    backgroundColor: '#fff',
    padding: '.5rem',
};

registerBlockType('uploadcare/image', {
    title: __('Uploadcare Image', 'uploadcare'),
    description: __('Add image with awesome Adaptive Delivery option'),
    category: 'media',
    icon: imageIcon(),
    attributes: {
        title: {
            type: 'array',
            source: 'children',
            selector: 'figcaption',
        },
        mediaID: {
            type: 'number',
        },
        mediaUid: {
            type: 'string',
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
            mediaID: '0000',
            mediaUid: 'no-uuid',
        },
    },
    edit(props) {
        const {className, attributes: {title, mediaID, mediaURL}, setAttributes} = props;
        const onChangeTitle = (value) => {
            setAttributes({title: value});
        };
        const setImage = () => {
            uploader.upload(mediaURL).then((fileInfo: FileInfoResponse) => onUploadImage(fileInfo)).catch(() => {});
        };
        const onUploadImage = (media: FileInfoResponse) => {
            setAttributes({
                mediaURL: media.cdnUrl,
                mediaID: media.attach_id,
                mediaUid: media.uuid,
            });
        };
        const onSelectImage = (wpMedia: WpMedia) => {
            setAttributes({
                mediaURL: wpMedia.url,
                mediaID: wpMedia.id,
                mediaUid: wpMedia.filename,
            });
        }

        return <div className={'uploadcare-handler'}>
            <figure className={className}>
                {mediaID ? <img alt={title} src={mediaURL}/> : null}
                <RichText tagName={'figcaption'} value={title} onChange={onChangeTitle}/>
            </figure>
            <div style={wrapperStyle}>
                <Button
                    className={'uploadcare-picker__button'}
                    onClick={ setImage }
                >
                    {__('Upload via Uploadcare', 'uploadcare')}
                </Button>
                <MediaUpload
                    onSelect={onSelectImage}
                    render={({ open }) => (
                        <Button isTertiary onClick={ open }>{__('WordPress Media Library')}</Button>
                    )}
                    />
            </div>
        </div>
            ;
    },
    save(props) {
        const {className, attributes: {title, mediaID, mediaURL}} = props;

        return <figure className={className}>
            {mediaID ? (<img id={mediaID} src={mediaURL} className={'uploadcare-image'} alt={title}/>) : null}
            <RichText.Content tagName="figcaption" value={title}/>
        </figure>;
    },
});

export default registerBlockType;

import { registerBlockType } from '@wordpress/blocks';
import React from 'react';
import UcUploader from './UcUploader';
import config from './uc-config';
import { __ } from '@wordpress/i18n';
import { RichText, MediaUpload } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import FileInfoResponse from './interfaces/FileInfoResponse';
import imageIcon from './icons/image';
import WpMedia from './interfaces/WpMedia';
import UcMediaMeta from './UcMediaMeta';

config.config.imagesOnly = true;
const uploader = new UcUploader(config.config);
const cdnBase = config.config.cdnBase;

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
        cdnUrlModifiers: {
            type: 'string',
        },
    },
    example: {
        attributes: {
            title: __('Uploadcare', 'uploadcare'),
            mediaURL: 'https://ucarecdn.com/6c5b97ee-4ce9-490f-92e9-50cba0271917/intelligence.svg',
            mediaID: '0000',
            mediaUid: '6c5b97ee-4ce9-490f-92e9-50cba0271917',
            cdnUrlModifiers: '',
        },
    },
    edit(props) {
        const {className, attributes: {title, mediaID, mediaUid, cdnUrlModifiers}, setAttributes} = props;

        if (mediaID && mediaID !== '0000') {
            UcMediaMeta.fetch(mediaID).then(data => {
                if (data.uploadcare_url_modifiers.length > 0) {
                    setAttributes({cdnUrlModifiers: data.uploadcare_url_modifiers[0]})
                }
            })
        }
        const onChangeTitle = (value) => {
            setAttributes({title: value});
        };
        const setImage = () => {
            uploader.upload(mediaUid ? `${cdnBase}/${mediaUid}/${cdnUrlModifiers}`: undefined).then((fileInfo: FileInfoResponse) => onUploadImage(fileInfo)).catch(() => {});
        };
        const onUploadImage = (media: FileInfoResponse) => {
            setAttributes({
                mediaURL: media.originalUrl,
                mediaID: media.attach_id,
                mediaUid: media.uuid,
                cdnUrlModifiers: media.cdnUrlModifiers,
            });
        };
        const onSelectImage = (wpMedia: WpMedia) => {
            setAttributes({
                mediaURL: wpMedia.url,
                mediaID: wpMedia.id,
                mediaUid: wpMedia.meta['uploadcare_uuid'] || wpMedia.filename,
                cdnUrlModifiers: wpMedia.meta['uploadcare_url_modifiers'] || '',
            });
        }

        return <figure className={`${className} uploadcare-handler`}>
            {mediaID ?
                <div className={'imageWrap'}>
                    <img alt={title} src={`${cdnBase}/${mediaUid}/${cdnUrlModifiers}`} />
                    <RichText placeholder={ __('Write a caption') } tagName={'figcaption'} value={title} onChange={onChangeTitle} />
                </div> :
                <div className={'components-placeholder is-large'}>
                    <div className={'components-placeholder__label'}>
                        <span className={'block-editor-block-icon'}>{imageIcon()}</span>
                        {__('Uploadcare image', 'uploadcare')}
                    </div>
                    <div className={'components-placeholder__instructions'}>
                        {__('Upload and edit image with Uploadcare', 'uploadcare')}
                    </div>
                </div>
            }
            <div style={wrapperStyle}>
                <Button
                    className={'uploadcare-picker__button'}
                    onClick={setImage}
                >
                    { mediaID ? __('Edit with Uploadcare', 'uploadcare') : __('Upload via Uploadcare', 'uploadcare') }
                </Button>
                {!mediaID ? <MediaUpload
                    onSelect={onSelectImage}
                    render={({open}) => (
                        <Button onClick={open}>{__('WordPress Media Library')}</Button>
                    )}
                /> : null}
            </div>
        </figure>;
    },
    save(props) {
        const {className, attributes: {title, mediaID, mediaUid, cdnUrlModifiers}} = props;

        return <figure className={className}>
            {mediaID ? (<img id={mediaID} src={`${cdnBase}/${mediaUid}/${cdnUrlModifiers}`} className={'uploadcare-image'} alt={title}/>) : null}
            <RichText.Content tagName="figcaption" value={title}/>
        </figure>;
    },
});

export default registerBlockType;

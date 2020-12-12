import { registerBlockType } from '@wordpress/blocks';
import React from 'react/index';
import config from './uc-config';
import { __ } from '@wordpress/i18n';
import gallery from './icons/gallery';
import UcUploader from './UcUploader';
import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import FileInfoResponse from './FileInfoResponse';
import PhotoswipeItem from './PhotoswipeItem';

const uploader = new UcUploader(config.config);
const wrapperStyle = {
    backgroundColor: '#fff',
    padding: '.5rem',
};

registerBlockType('uploadcare/photoswipe', {
    title: __('Uploadcare Photoswipe', 'uploadcare'),
    description: __('Uploadcare Photoswipe widget', 'uploadcare'),
    category: 'media',
    icon: gallery(),
    attributes: {
        title: {
            type: 'array',
            source: 'children',
            selector: 'figcaption',
        },
        images: {
            type: 'array',
            selector: 'figure div.ucare-photoswipe',
            default: [],
        },
    },
    example: {
        attributes: {
            title: __('Uploadcare', 'uploadcare'),
            images: []
        },
    },
    edit(props) {
        console.log(props.attributes.images)
        const { className, attributes: { title, images }, setAttributes } = props;
        const onChangeTitle = (value) => { setAttributes({title: value}) };

        const NI = [
            {uuid: '586eabc1-91ff-4942-adb2-e89a60893bc0'},
            {uuid: 'bebd4fb4-e106-4adb-968d-a12e253ce5e7'},
            {uuid: 'ddcbc84e-ee4f-4126-a0d5-46535a03a172'},
        ];
        const setImage = () => {
            /*
            uploader.multiUpload().then(data => {
                const newImages = data.map((file: FileInfoResponse) => {
                    return {
                        src: file.cdnUrl,
                        uuid: file.uuid
                    }
                });
            })
            */

            setAttributes({images: NI})
        }

        return <figure className={className}>
            <div className={'ucare-photoswipe'} data-thumbwidth={'auto'} data-thumbheight={'200'}>
                { images.map((elem, k) => {
                    return <i data-src={elem.uuid} key={k} />
                    }) }
            </div>
            <RichText tagName={'figcaption'} value={title} onChange={onChangeTitle}/>
            <div style={wrapperStyle}>
                <Button
                    className={'uploadcare-picker__button'}
                    onClick={setImage}
                >
                    {__('Upload via Uploadcare', 'uploadcare')}
                </Button>
            </div>
        </figure>
    },
    save(props) {
        const { className, attributes: { title, images } } = props;

        const imagesList = (value) => {
            return value.map((item, k) => {
                return <i data-src={item.uuid} key={k} />
            })
        }

        return <figure className={className}>
            <div className={'ucare-photoswipe'} data-thumbwidth={'auto'} data-thumbheight={'200'}>
                { imagesList(images) }
            </div>
            <RichText.Content tagName="figcaption" value={title}/>
        </figure>
    },
});

import React from "react/index"
import { Button } from "@wordpress/components";
import UcUploader from "./UcUploader";
import config from './uc-config';
import FileInfoResponse from "./FileInfoResponse";

const wp = (window as any).wp;
const { __ } = wp.i18n;
const uploader = new UcUploader(config.config);

const upload = () => {
    try {
        const block = wp.data.select('core/block-editor').getSelectedBlock();
        if (block === null) return false;

        uploader.upload().then((data: FileInfoResponse) => {
            if (block.name === 'core/gallery') {
                console.log(data.attach_id, data)
                block.attributes.images.push({
                    fullUrl: data.cdnUrl,
                    url: data.cdnUrl,
                    // id: `"${data.attach_id}"`
                });
                // block.attributes.ids.push(data.attach_id);
            }

            if (block.name === 'core/image') {
                block.attributes.url = data.cdnUrl;
                block.attributes.alt = data.name;
            }

            wp.data.dispatch('core/block-editor').clearSelectedBlock();
            wp.data.dispatch('core/block-editor').replaceBlock(block.clientId, block);
        });
    } catch (err) {
        document.querySelectorAll('div.uploadcare-loading-screen').forEach(el => {
            el.classList.add('uploadcare-hidden')
        });
    }
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

export default function enhanceMediaPlaceholder( MediaPlaceholder ) {
    return ( props ) => {
        return (
            <MediaPlaceholder { ...props } disableDropZone>
                <UcButton />
            </MediaPlaceholder>
        );
    }
}

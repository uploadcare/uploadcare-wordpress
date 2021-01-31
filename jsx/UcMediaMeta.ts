interface UploadcareImageMeta {
    uploadcare_url_modifiers: Array<string>;
    uploadcare_url: Array<string>;
    uploadcare_uuid: Array<string>;
}

export default class UcMediaMeta {
    private static urlTemplate = (mediaId) => `/index.php?rest_route=/wp/v2/media/${mediaId}&_fields=meta`;

    static fetch(mediaId: number): Promise<UploadcareImageMeta> {

        return fetch(this.urlTemplate(mediaId), {
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'same-origin',
            headers: {'Accept': 'application/json'},
            redirect: 'follow',
            referrerPolicy: 'same-origin',
        }).then(r => r.json()).then(data => {
            return data.meta as UploadcareImageMeta;
        })
    }
}

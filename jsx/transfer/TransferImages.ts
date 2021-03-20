import UcConfig from '../interfaces/UcConfig';
import config from '../uc-config';

export default class TransferImages {

    private uploadButtons: NodeListOf<HTMLButtonElement> = document.querySelectorAll('button[data-action="uc-upload"]');
    private downloadButtons: NodeListOf<HTMLButtonElement> = document.querySelectorAll('button[data-action="uc-download"]');
    private readonly uploadAllButton: HTMLElement | null = document.getElementById('uploadAll');
    private readonly progressBarWrapper: HTMLElement | null = null;
    private readonly progressBar: HTMLElement | null = null;
    private config: UcConfig;
    private readonly uploadBtnSelector: string = 'uc-upload';
    private readonly downloadBtnSelector: string = 'uc-download';

    constructor(uploadBtn: String | null = 'uc-upload', downloadBtn: String | null = 'uc-download') {
        this.config = config.config;
        if (uploadBtn !== null) this.uploadBtnSelector = uploadBtn as string;
        if (downloadBtn !== null) this.downloadBtnSelector = downloadBtn as string;

        this.downloadButtons = TransferImages.getNodeList(`button[data-action="${this.downloadBtnSelector}"]`);
        this.uploadButtons = TransferImages.getNodeList(`button[data-action="${this.uploadBtnSelector}"]`);
        this.progressBarWrapper = document.getElementById('transferProgress');
        if (this.progressBarWrapper instanceof HTMLDivElement) {
            this.progressBar = this.progressBarWrapper.querySelector('div');
        }

        this.addActions();
    }

    private checkLocalExists(): boolean {
        const btns = TransferImages.getNodeList(`button[data-action="${this.uploadBtnSelector}"]`);
        const enabled = Array.prototype.slice.call(btns).filter((b: HTMLButtonElement) => {
                return !b.disabled;
            });

        return enabled.length > 0;
    }

    private static getNodeList(selector: string): NodeListOf<HTMLButtonElement> {
        return document.querySelectorAll(selector);
    }

    private addActions(): void {
        this.uploadButtons.forEach((b: HTMLButtonElement) => {
            b.addEventListener('click', ev => {
                this.uploadAction(ev);
            })
        })
        this.downloadButtons.forEach((b: HTMLButtonElement) => {
            b.addEventListener('click', ev => {
                this.downloadAction(ev);
            })
        })
        this.toggleTransferAllAction();
    }

    private toggleTransferAllAction(): void {
        if (this.uploadAllButton === null) return;

        if (this.checkLocalExists()) {
            this.uploadAllButton.removeAttribute('disabled');
            this.uploadAllButton.addEventListener('click', ev => {
                this.uploadAllAction(ev)
            })
        } else {
            this.uploadAllButton.setAttribute('disabled', '1');
        }
    }

    private makeFormData(arr: Array<any>): FormData {
        const data = new FormData();
        arr.forEach(obj => {
            data.append(obj.property, obj.value);
        });

        return data;
    }

    private uploadAllAction(ev: MouseEvent): void {
        ev.preventDefault();

        Array.prototype.slice.call(this.uploadButtons).map((b: HTMLButtonElement) => {
            const postId = b.dataset.post || false;
            if (postId === false)
                return false;
            if ((b.dataset.uuid || '').length > 0) {
                return false;
            }

            const data = this.makeFormData([
                {property: 'action', value: 'uploadcare_transfer'},
                {property: 'postId', value: postId}
            ]);

            this.fetchAction(data, b);
        })
    }

    private uploadAction(ev: MouseEvent): void {
        ev.preventDefault();
        const target = ev.currentTarget;
        if (!(target instanceof HTMLButtonElement))
            return;

        const data = this.makeFormData([
            {property: 'action', value: 'uploadcare_transfer'},
            {property: 'postId', value: target.dataset.post || ''},
        ])

        this.fetchAction(data, target);
    }

    private downloadAction(ev: MouseEvent): void {
        ev.preventDefault()
        const target = ev.currentTarget;
        if (!(target instanceof HTMLButtonElement))
            return;

        const data = this.makeFormData([
            {property: 'action', value: 'uploadcare_down'},
            {property: 'uuid', value: target.dataset.uuid || ''},
            {property: 'postId', value: target.dataset.post || ''},
        ])

        this.fetchAction(data, target);
    }

    private setProgress(val: number | null): void {
        if (val === null) val = 100;

        if (this.progressBar instanceof HTMLDivElement) {
            this.progressBar.style.width = `${val}%`
        }
    }

    private fetchAction(data: FormData, target: HTMLButtonElement): void {
        this.setProgress(null);

        window.fetch(this.config.ajaxurl, {
            method: 'POST',
            redirect: 'follow',
            body: data,
            headers: { 'Accept': 'application/json' }
        }).then(r => {
            if (r.status !== 200) {
                throw r.text();
            }

            return r.json();
        }).then(data => {
            if (!data.hasOwnProperty('fileUrl') || !data.hasOwnProperty('postId')) return;
            const remoteUuid = data.hasOwnProperty('uploadcare_uuid') ? data.uploadcare_uuid : '';
            const targetBtn = document.getElementById(`uc-download-${data.postId}`) || document.createElement('button');

            if (typeof remoteUuid === "string" && remoteUuid.length > 0) {
                targetBtn.dataset.uuid = remoteUuid
            } else {
                targetBtn.dataset.uuid = '';
            }

            this.setAttributes(data.postId, data.fileUrl)
            this.setProgress(0);
        }).catch((e) => {
            if (e instanceof Promise) {
                e.then(data => {
                    TransferImages.showError(data);
                })
            } else {
                console.error(e)
            }
            this.setProgress(0)
        })
    }

    private static showError(data: string): void {
        const errorPlace = document.getElementById('uc-error-place');
        if (!(errorPlace instanceof HTMLElement))
            return;

        errorPlace.innerText = data;
        errorPlace.classList.remove('hidden');
        window.scrollTo(0, 0);
    }

    private setAttributes(postId: number | null, url: string): void {
        if (postId === null) return;

        const image = document.getElementById(`image-${postId}`);
        if ((image instanceof Image)) image.setAttribute('src', url);

        const upload = TransferImages.getUploadButton(postId);
        const download = TransferImages.getDownloadBtn(postId);
        const trIcon = TransferImages.getTransferredIcon(postId);
        const notTrIcon = TransferImages.getNotTransferredIcon(postId);

        if (upload !== null) {
            upload.disabled = !upload.disabled;
            if (upload.style.display === 'none') {
                upload.style.display = 'inline-block';
            } else {
                upload.style.display = 'none';
            }
        }

        if (download !== null) {
            download.disabled = !download.disabled;
            if (download.style.display === 'none') {
                download.style.display = 'inline-block';
            } else {
                download.style.display = 'none';
            }
        }

        if (trIcon instanceof HTMLElement) {
            trIcon.classList.contains('hidden') ? trIcon.classList.remove('hidden') : trIcon.classList.add('hidden');
        }

        if (notTrIcon instanceof HTMLElement) {
            notTrIcon.classList.contains('hidden') ? notTrIcon.classList.remove('hidden') : notTrIcon.classList.add('hidden');
        }

        this.toggleTransferAllAction();
    }

    private static getTransferredIcon(postId: number): HTMLElement | null {
        return document.getElementById(`icon-transferred-${postId}`);
    }

    private static getNotTransferredIcon(postId: number): HTMLElement | null {
        return document.getElementById(`icon-not-transferred-${postId}`);
    }

    private static getDownloadBtn(postId: number): HTMLButtonElement | null {
        const btn = document.getElementById(`uc-download-${postId}`);
        if (btn instanceof HTMLButtonElement)
            return btn;

        return null;
    }

    private static getUploadButton(postId: number): HTMLButtonElement | null {
        const btn = document.getElementById(`uc-upload-${postId}`);
        if (btn instanceof HTMLButtonElement)
            return btn;

        return null;
    }
}

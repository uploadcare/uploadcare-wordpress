import UcConfig from '../interfaces/UcConfig';
import config from '../uc-config';

export default class TransferImages {

    private uploadButtons: NodeListOf<HTMLButtonElement> = document.querySelectorAll('button[data-action="uc-upload"]');
    private downloadButtons: NodeListOf<HTMLButtonElement> = document.querySelectorAll('button[data-action="uc-download"]');
    private readonly progressBarWrapper: HTMLElement|null = null;
    private readonly progressBar: HTMLElement|null = null;
    private config: UcConfig;

    constructor(uploadBtn: String|null, downloadBtn: String|null) {
        this.config = config.config;
        if (uploadBtn !== null) uploadBtn = 'uc-upload';
        if (downloadBtn !== null) downloadBtn = 'uc-download';

        this.downloadButtons =  TransferImages.getNodeList(`button[data-action="${downloadBtn}"]`);
        this.uploadButtons = TransferImages.getNodeList(`button[data-action="${uploadBtn}"]`);
        this.progressBarWrapper = document.getElementById('transferProgress');
        if (this.progressBarWrapper instanceof HTMLDivElement) {
            this.progressBar = this.progressBarWrapper.querySelector('div');
        }

        this.addActions();
    }

    private static getNodeList(selector: string): NodeListOf<HTMLButtonElement> {
        return document.querySelectorAll(selector);
    }

    private addActions(): void {
        this.uploadButtons.forEach((b: HTMLButtonElement) => {
            b.addEventListener('click', ev => { this.uploadAction(ev); })
        })
        this.downloadButtons.forEach((b: HTMLButtonElement) => {
            b.addEventListener('click', ev => { this.downloadAction(ev); })
        })
    }

    private uploadAction(ev: MouseEvent) {
        ev.preventDefault();
        const target = ev.currentTarget;
        if (!(target instanceof HTMLButtonElement))
            return;

        const data = new FormData();
        data.append('action', 'uploadcare_transfer');
        data.append('postId', target.dataset.post || '')

        this.fetchAction(data);
    }

    private downloadAction(ev: MouseEvent) {
        ev.preventDefault()
        const target = ev.currentTarget;
        if (!(target instanceof HTMLButtonElement))
            return;

        const data = new FormData();
        data.append('action', 'uploadcare_down');
        data.append('uuid', target.dataset.uuid || '')
        data.append('postId', target.dataset.post || '')

        this.fetchAction(data);
        this.setProgress(0)
    }

    private setProgress(val: number | null): void {
        if (val === null) val = 100;

        if (this.progressBar instanceof HTMLDivElement) {
            this.progressBar.style.width = `${val}%`
        }
    }

    private fetchAction(data: FormData): void {
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

            TransferImages.setAttributes(data.postId, data.fileUrl)
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

    private static setAttributes(postId: number|null, url: string): void {
        if (postId === null) return;

        const image = document.getElementById(`image-${postId}`);
        if ((image instanceof Image)) image.setAttribute('src', url);

        const upload = TransferImages.getUploadButton(postId);
        const download = TransferImages.getDownloadBtn(postId);
        const trIcon = TransferImages.getTransferredIcon(postId);
        const notTrIcon = TransferImages.getNotTransferredIcon(postId);

        if (upload !== null) {
            upload.disabled = !upload.disabled;
        }

        if (download !== null) {
            download.disabled = !download.disabled;
        }

        if (trIcon instanceof HTMLElement) {
            trIcon.classList.contains('hidden') ? trIcon.classList.remove('hidden') : trIcon.classList.add('hidden');
        }

        if (notTrIcon instanceof HTMLElement) {
            notTrIcon.classList.contains('hidden') ? notTrIcon.classList.remove('hidden') : notTrIcon.classList.add('hidden');
        }
    }

    private static getTransferredIcon(postId: number): HTMLElement|null
    {
        return document.getElementById(`icon-transferred-${postId}`);
    }

    private static getNotTransferredIcon(postId: number): HTMLElement|null
    {
        return document.getElementById(`icon-not-transferred-${postId}`);
    }

    private static getDownloadBtn(postId: number): HTMLButtonElement|null
    {
        const btn = document.getElementById(`uc-download-${postId}`);
        if (btn instanceof HTMLButtonElement)
            return btn;

        return null;
    }

    private static getUploadButton(postId: number): HTMLButtonElement|null
    {
        const btn = document.getElementById(`uc-upload-${postId}`);
        if (btn instanceof HTMLButtonElement)
            return btn;

        return null;
    }
}

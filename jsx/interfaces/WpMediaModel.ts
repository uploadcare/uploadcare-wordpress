import WpMedia from './WpMedia';

export default interface WpMediaModel {
    cid: string;
    attributes: WpMedia;
    _listenId: string;
    _pending: boolean;
}

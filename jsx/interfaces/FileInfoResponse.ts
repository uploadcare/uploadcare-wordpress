import {FileInfo} from '@uploadcare/react-widget';

export default interface FileInfoResponse extends FileInfo {
  attach_id: number;
  nonce: string;
}

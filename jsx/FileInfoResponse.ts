interface ImageInfo {
    color_mode: string;
    datetime_original?: Date;
    dpi?: number;
    format: string;
    geo_location?: [lat: number, lng: number],
    height: number;
    width: number;
    orientation?: string
    sequence: boolean;
}

export default interface FileInfoResponse {
    cdnUrl: string;
    originalUrl: string;
    cdnUrlModifiers?: string;
    isImage: boolean;
    isStored: boolean;
    mimeType: string;
    name: string;
    originalImageInfo?: ImageInfo;
    size: number;
    sourceInfo: { source: string, file: File };
    uuid: string;
    attach_id: number;
}

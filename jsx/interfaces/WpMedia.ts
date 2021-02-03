enum Orientation { portrait = 'portrait', landscape = 'landscape' }

export default interface WpMedia {
    alt?: string;
    author: number;
    authorName: string;
    caption?: string;
    date: Date;
    dateFormatted: string;
    description?: string;
    editLink: string;
    filename: string;
    height: number;
    width: number;
    icon: string;
    id: number;
    link: string;
    mime: string;
    modified: Date;
    name: string;
    orientation: Orientation;
    status: string;
    subtype: string;
    title: string;
    type: string;
    url: string;
    sizes: TSize;
    meta: string;
}

type TSize = {[key: string]: WpMediaSize}

interface WpMediaSize {
    height: number;
    width: number;
    orientation: string;
    url: string
}

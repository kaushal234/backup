export interface ITocFile {
  name: string;
  type: string;
  description?: string;
  blob?: Blob;
  url?: string;
  isMainFile?: boolean;
}

export type IPostContractFileResponse = IContractFile;

interface IContractFile {
  "@context": string;
  "@id": string;
  "@type": string;
  public: boolean;
  description: string | null;
  id: number;
  filePath: string;
  poster: IPeople | null;
  createdAt: string;
  sha: string;
  mimeType: string;
  extension: string;
  size: number;
}

interface IPeople {
  "@id": string;
  "@type": string;
  username: string | null;
  email: string;
  legacyId: number | null;
  firstname: string | null;
  lastname: string | null;
}

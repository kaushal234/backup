import "axios";

declare module "axios" {
  export interface AxiosRequestConfig {
    authRequired?: boolean;
    isBlob?: boolean;
    fetchFirst?: boolean;
    fetchAlways?: boolean;
    contentHeaderRequired?: boolean;
  }
}

export default {};

import "axios";

declare module "axios" {
  export interface AxiosInstance {
    baseURL?: string;
  }
}

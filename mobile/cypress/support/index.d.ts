declare namespace Cypress {
  interface Chainable {
    getBySel(
      selector: string,
      options?: Partial<Cypress.Timeoutable>
    ): Chainable<JQuery<HTMLElement>>;

    getBySelLike(selector: string): Chainable<JQuery<HTMLElement>>;

    loginWithUser(
      username?: string,
      password?: string,
      closeDrawer?: boolean
    ): void;

    interceptApi(url: import("../@types/IApiCall").IApiCall): void;

    waitForApiWithLoader(
      url: import("../@types/IApiCall").IApiCall,
      code: number | null,
      params?: { [key: string]: string | RegExp } | null,
      skipLoader?: boolean
    ): void;

    findBySel(selector: string): Chainable<JQuery<HTMLElement>>;

    findBySelLike(selector: string): Chainable<JQuery<HTMLElement>>;

    getByClassName(
      selector: string,
      className: string
    ): Chainable<JQuery<HTMLElement>>;

    findByIconIdLike(
      selector: string,
      options?: Partial<Cypress.Timeoutable>
    ): Chainable<JQuery<HTMLElement>>;

    clearIndexedDB(): void;
  }
}

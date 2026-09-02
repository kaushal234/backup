const downloadsFolder = Cypress.config("downloadsFolder");

export const TEST_USER_BASIC = {
  username: "user-basic@tld.fr",
  password: "P@ssw0rd15chars",
};

export const TEST_USER_AST = {
  username: "user-ast@tld.fr",
  password: "P@ssw0rd15chars",
};

export const TEST_USER_AST_BU = {
  username: "user-ast-bu2@tld.fr",
  password: "P@ssw0rd15chars",
};

export const TEST_USER_CSM = {
  username: "user-csm@tld.fr",
  password: "P@ssw0rd15chars",
};

export const TEST_USER_SUPER = {
  username: "user-superuser@tld.fr",
  password: "P@ssw0rd15chars",
};

export const API_METHOD = {
  GET: "GET",
  POST: "POST",
  DELETE: "DELETE",
  PUT: "PUT",
} as const;

export const FILE_PATH = {
  sample1: "cypress/files/sample1.jpeg",
  sample2: "cypress/files/sample2.pdf",
  sample3: "cypress/files/sample3.jpg",
} as const;

export const DOWNLOADED_FILE_PATH = {
  logs: {
    sample1: `${downloadsFolder}/sample1.jpeg`,
    sample2: `${downloadsFolder}/sample2.pdf`,
    repeatedSample2: `${downloadsFolder}/sample2pdf.pdf`,
  },
  files: {
    sample1: `${downloadsFolder}/sample1.jpeg`,
    sample2: `${downloadsFolder}/sample2.pdf`,
  },
  manual: `${downloadsFolder}/sing-a-song.pdf`,
  extraSchematics: `${downloadsFolder}/1152377.pdf`,
};

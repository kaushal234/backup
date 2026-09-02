import { ILoginUserParams } from "../../src/@type/ILoginUserParams";
import { LOCAL_STORAGE } from "../../src/constants/constants";

export const isPositiveNumber = (value: string) => {
  const number = parseFloat(value);
  return !Number.isNaN(number) && number >= 0;
};

export const getToken = (window: Cypress.AUTWindow) => {
  const userInfoString = window.localStorage.getItem(LOCAL_STORAGE.userInfo);
  const userInfo: ILoginUserParams | null = JSON.parse(
    userInfoString ?? "null"
  );
  if (userInfo) {
    return userInfo.token;
  }
  return null;
};

export const assertTextMatch = (value: string, equals: string | RegExp) => {
  if (typeof equals === "string") {
    assert.equal(value.toLowerCase(), equals.toLowerCase());
  } else {
    assert.match(value, equals);
  }
};

export const generateRandomNumber = () => {
  const min = 1;
  const max = 10 ** 7;
  return Math.floor(Math.random() * (max - min + 1)) + min;
};

export const generateRandomString = () => {
  const number = generateRandomNumber();
  const date = new Date().toISOString();
  return `${number}_${date}`;
};

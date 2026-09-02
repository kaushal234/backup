/**
 * @jest-environment node
 */
import {
  describe,
  expect,
  it,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import { navigate } from "../../controllers/utils/navigate";

describe("test navigate", () => {
  let assignMock: jest.Mock;

  beforeEach(() => {
    assignMock = jest.fn();
    global.window = {
      location: {
        assign: assignMock,
      },
    } as unknown as Window & typeof globalThis;
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it("should redirect to url", () => {
    navigate("https://exemple.com");

    expect(assignMock).toHaveBeenCalledTimes(1);
    expect(assignMock).toHaveBeenCalledWith("https://exemple.com");
  });

  it("should not be call", () => {
    expect(assignMock).not.toHaveBeenCalled();
  });
});

import { jest } from "@jest/globals";

export const mockAuthService = () => {
  jest.unstable_mockModule(
    "../../../src/common/services/authService.ts",
    () => ({
      isAuthenticated: jest.fn(() => Promise.resolve(true)),
    })
  );
};

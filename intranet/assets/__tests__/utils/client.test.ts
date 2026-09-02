import { describe, expect, jest } from "@jest/globals";
import { fetch } from "../../controllers/utils/client";

describe("client fetch", () => {
  beforeEach(() => {
    Object.defineProperty(window, "user", {
      writable: true,
      value: {
        token: "my-token",
      },
    });

    document.documentElement.lang = "fr";
    const mockFetch = jest.fn() as jest.MockedFunction<typeof fetch>;
    mockFetch.mockResolvedValue({
      ok: true,
    } as Response);
    window.fetch = mockFetch;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it("should call window.fetch with default headers", async () => {
    const url = new URL("https://api.test/users");
    await fetch(url);

    expect(window.fetch).toHaveBeenCalledWith(url, {
      headers: {
        Accept: "application/ld+json",

        "Content-type": "application/ld+json",

        Authorization: "Bearer my-token",

        "Accept-Language": "fr",
      },
    });
  });

  it("should merge custom options", async () => {
    const url = new URL("https://api.test/users");
    await fetch(url, {
      method: "POST",
    });

    expect(window.fetch).toHaveBeenCalledWith(url, {
      method: "POST",
      headers: {
        Accept: "application/ld+json",
        "Content-type": "application/ld+json",
        Authorization: "Bearer my-token",
        "Accept-Language": "fr",
      },
    });
  });

  it("should fallback to default locale", async () => {
    document.documentElement.removeAttribute("lang");
    const url = new URL("https://api.test/users");
    await fetch(url);

    expect(window.fetch).toHaveBeenCalledWith(url, {
      headers: expect.objectContaining({
        "Accept-Language": "en",
      }),
    });
  });

  it("should use null token when user is undefined", async () => {
    Object.defineProperty(window, "user", {
      writable: true,
      value: undefined,
    });
    const url = new URL("https://api.test/users");
    await fetch(url);

    expect(window.fetch).toHaveBeenCalledWith(url, {
      headers: expect.objectContaining({
        Authorization: "Bearer null",
      }),
    });
  });
});

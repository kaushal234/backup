import {
  describe,
  expect,
  it,
  jest,
  beforeEach,
  afterEach,
} from "@jest/globals";
import {
  addCopyButtons,
  renderMarkdown,
} from "../../controllers/utils/render_markdown";

describe("renderMarkdown", () => {
  it("renders basic markdown to HTML", () => {
    const html = renderMarkdown("**bold** and *italic*");

    expect(html).toContain("<strong>bold</strong>");
    expect(html).toContain("<em>italic</em>");
  });

  it("returns an empty string for nullish content", () => {
    expect(renderMarkdown(null as unknown as string)).toBe("");
    expect(renderMarkdown(undefined as unknown as string)).toBe("");
  });

  it("sanitizes dangerous markup", () => {
    const html = renderMarkdown('<img src=x onerror="alert(1)">');

    expect(html).not.toContain("onerror");
  });

  it("strips script tags", () => {
    const html = renderMarkdown("<script>alert('xss')</script>hello");

    expect(html).not.toContain("<script>");
    expect(html).toContain("hello");
  });

  it("highlights fenced code blocks", () => {
    const html = renderMarkdown("```js\nconst a = 1;\n```");

    expect(html).toContain("<pre>");
    expect(html).toContain("<code");
    // highlight.js adds the hljs class on the highlighted element
    expect(html).toContain("hljs");
  });
});

describe("addCopyButtons", () => {
  let writeTextMock: jest.Mock<(text: string) => Promise<void>>;

  beforeEach(() => {
    writeTextMock = jest.fn(() => Promise.resolve());
    Object.assign(navigator, {
      clipboard: { writeText: writeTextMock },
    });
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  const buildContainer = (innerHTML: string): HTMLElement => {
    const container = document.createElement("div");
    container.innerHTML = innerHTML;
    return container;
  };

  it("adds a copy button to each pre block", () => {
    const container = buildContainer(
      "<pre><code>a</code></pre><pre><code>b</code></pre>"
    );

    addCopyButtons(container);

    expect(container.querySelectorAll(".copy-btn")).toHaveLength(2);
  });

  it("does not add a second button when one already exists", () => {
    const container = buildContainer("<pre><code>a</code></pre>");

    addCopyButtons(container);
    addCopyButtons(container);

    expect(container.querySelectorAll(".copy-btn")).toHaveLength(1);
  });

  it("copies the code content to the clipboard on click", async () => {
    const container = buildContainer("<pre><code>const a = 1;</code></pre>");
    // jsdom does not implement innerText, so define it explicitly.
    const code = container.querySelector("code") as HTMLElement;
    Object.defineProperty(code, "innerText", { value: "const a = 1;" });
    addCopyButtons(container);

    jest.useFakeTimers();
    const button = container.querySelector<HTMLButtonElement>(".copy-btn");
    button?.click();

    expect(writeTextMock).toHaveBeenCalledTimes(1);
    expect(writeTextMock).toHaveBeenCalledWith("const a = 1;");

    await Promise.resolve();
    expect(button?.textContent).toBe("Copied !");

    // After the delay the label is reset.
    jest.advanceTimersByTime(1500);
    expect(button?.textContent).toBe("Copy");
    jest.useRealTimers();
  });

  it("shows an error label when copying fails", async () => {
    writeTextMock.mockImplementation(() => Promise.reject(new Error("nope")));
    const container = buildContainer("<pre><code>x</code></pre>");
    addCopyButtons(container);

    const button = container.querySelector<HTMLButtonElement>(".copy-btn");
    button?.click();

    await Promise.resolve();
    await Promise.resolve();
    expect(button?.textContent).toBe("Error");
  });
});

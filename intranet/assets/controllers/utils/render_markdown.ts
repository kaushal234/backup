import { marked } from "marked";
import DOMPurify from "dompurify";
import hljs from "highlight.js";

marked.setOptions({
  gfm: true,
  breaks: true,
});

export function renderMarkdown(content: string) {
  const rawHtml = marked.parse(content ?? "") as string;

  const cleanHtml = DOMPurify.sanitize(rawHtml, {
    USE_PROFILES: { html: true },
  });

  const container = document.createElement("div");
  container.innerHTML = cleanHtml;

  container.querySelectorAll<HTMLElement>("pre code").forEach((block) => {
    hljs.highlightElement(block);
  });

  return container.innerHTML;
}

export function addCopyButtons(container: HTMLElement) {
  container.querySelectorAll<HTMLPreElement>("pre").forEach((pre) => {
    if (pre.querySelector(".copy-btn")) return;

    const button = document.createElement("button");
    button.className = "copy-btn";
    button.type = "button";
    button.textContent = "Copier";

    button.addEventListener("click", async () => {
      const code = pre.querySelector("code")?.innerText ?? "";

      try {
        await navigator.clipboard.writeText(code);
        button.textContent = "Copied !";

        setTimeout(() => {
          button.textContent = "Copy";
        }, 1500);
      } catch (e) {
        button.textContent = "Error";
      }
    });

    const preEl: HTMLPreElement = pre;
    preEl.style.position = "relative";
    preEl.appendChild(button);
  });
}

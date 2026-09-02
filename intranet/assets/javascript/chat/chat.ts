import {
  addCopyButtons,
  renderMarkdown,
} from "../../controllers/utils/render_markdown";

export function extractIdFromIri(iri: string): string | null {
  const parts = iri.split("/");
  return parts[parts.length - 1] || null;
}

export function buildConversationUrl(
  id: string,
  pathname: string = window.location.pathname
): string {
  return `${pathname}/${id}`;
}

export function redirectToConversation(id: string): void {
  window.location.assign(buildConversationUrl(id));
}

export function renderAssistantBubble(
  bubble: HTMLElement,
  content: string
): void {
  const el = bubble;

  el.dataset.markdownContent = content;
  el.innerHTML = renderMarkdown(content);
  addCopyButtons(el);
}

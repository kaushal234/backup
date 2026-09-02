import { describe, expect, test, jest } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import TextareaEditorController from "../controllers/textarea_editor_controller";

jest.mock("@ckeditor/ckeditor5-build-classic");

// Mock ResizeObserver used by CKEditor
global.ResizeObserver = class implements ResizeObserver {
  observe() {}

  unobserve() {}

  disconnect() {}
};

describe("Test TextareaEditorController", () => {
  test("Test CKEditor is working", async () => {
    document.body.innerHTML = `
      <textarea data-controller="textarea-editor"></textarea>
    `;

    const textarea = document.querySelector("textarea") as HTMLTextAreaElement;

    let blurHandler: () => void = () => {};

    (ClassicEditor.create as jest.Mock).mockResolvedValue({
      sourceElement: textarea,
      getData: jest.fn(() => "<p>Hello</p>"),
      editing: {
        view: {
          document: {
            on: (_event: string, cb: () => void) => {
              blurHandler = cb;
            },
          },
        },
      },
    });

    const changeSpy = jest.fn();
    textarea.addEventListener("change", changeSpy);

    const app = Application.start();
    app.register("textarea-editor", TextareaEditorController);

    await new Promise(process.nextTick);
    await Promise.resolve();

    blurHandler();

    expect(textarea.value).toBe("<p>Hello</p>");
    expect(changeSpy).toHaveBeenCalled();
  });
});

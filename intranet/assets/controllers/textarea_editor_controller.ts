import { Controller } from "@hotwired/stimulus";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

export default class extends Controller<HTMLFormElement> {
  connect(): void {
    this.init();
  }

  init(): void {
    ClassicEditor.create(this.element, {
      licenseKey: "GPL",
      plugins: [
        "Bold",
        "Essentials",
        "Heading",
        "Indent",
        "Italic",
        "Link",
        "Paragraph",
        "List",
      ],
      toolbar: {
        items: [
          "undo",
          "redo",
          "|",
          "heading",
          "|",
          "bold",
          "italic",
          "|",
          "bulletedList",
          "numberedList",
          "|",
          "link",
        ],
        shouldNotGroupWhenFull: false,
      },
    }).then((editor: InstanceType<typeof ClassicEditor>) => {
      const textarea: HTMLTextAreaElement = editor.sourceElement;
      this.element.ckeditorInstance = editor;

      editor.editing.view.document.on("blur", () => {
        textarea.value = editor.getData();
        textarea.dispatchEvent(new Event("change", { bubbles: true }));
      });
    });
  }
}

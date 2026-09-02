import { describe, expect, it, afterEach } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import OptionFilterController from "../../../controllers/mis/trouble_ticket/option_filter_controller";

// The controller opens the shared <twig:Modal> by toggling Bootstrap's CSS classes (this app has
// Bootstrap's CSS but not its modal JS). The tests assert OUR behaviour: the message text is set,
// the modal is shown (.show + display + a .modal-backdrop), and the dismiss controls close it.

const ID = "mis--trouble-ticket--option-filter";

const VALUES = `data-controller="${ID}"
  data-${ID}-incident-help-value="Incident help"
  data-${ID}-request-help-value="Request help"
  data-${ID}-notify-message-value="Notify message"
  data-${ID}-purchase-message-value="Purchase message"`;

const SELECTS = `
  <select name="trouble_ticket[typeCategory]"
          data-${ID}-target="category"
          data-action="${ID}#apply">
    <option value=""></option>
    <option value="Incident">Incident</option>
    <option value="Request">Request</option>
  </select>
  <select name="trouble_ticket[type]"
          data-${ID}-target="option"
          data-action="${ID}#onOptionChange">
    <option value=""></option>
    <option value="1" data-category="Incident">Blocking</option>
    <option value="2" data-category="Request" data-notify="1">Access</option>
    <option value="3" data-category="Request" data-purchase-warning="1">Buy</option>
  </select>
  <span class="form-text" data-${ID}-target="help"></span>`;

// The modal carries the message target and a dismiss control, like the <twig:Modal> output.
const MODAL = `
  <div class="modal" id="tts-info-modal">
    <button type="button" class="modal-close" data-bs-dismiss="modal">x</button>
    <p data-${ID}-target="message"></p>
  </div>`;

describe("OptionFilterController", () => {
  let application: Application;

  const boot = async (html: string): Promise<void> => {
    document.body.innerHTML = html;
    application = Application.start();
    application.register(ID, OptionFilterController);
    await Promise.resolve();
  };

  afterEach(() => {
    application?.stop();
    document.body.className = "";
    document.querySelector(".modal-backdrop")?.remove();
  });

  const categoryEl = (): HTMLSelectElement =>
    document.querySelector(
      '[name="trouble_ticket[typeCategory]"]'
    ) as HTMLSelectElement;
  const optionSelectEl = (): HTMLSelectElement =>
    document.querySelector(
      '[name="trouble_ticket[type]"]'
    ) as HTMLSelectElement;
  const helpEl = (): HTMLElement =>
    document.querySelector(`[data-${ID}-target="help"]`) as HTMLElement;
  const messageEl = (): HTMLElement =>
    document.querySelector(`[data-${ID}-target="message"]`) as HTMLElement;
  const modalEl = (): HTMLElement =>
    document.getElementById("tts-info-modal") as HTMLElement;
  const backdropEl = (): HTMLElement | null =>
    document.querySelector(".modal-backdrop");
  const fire = (el: HTMLElement): void => {
    el.dispatchEvent(new Event("change"));
  };

  it("hides every option until a type is chosen", async () => {
    await boot(`<form ${VALUES}>${SELECTS}</form>`);

    expect(
      (optionSelectEl().querySelector('[value="1"]') as HTMLOptionElement)
        .hidden
    ).toBe(true);
    expect(
      (optionSelectEl().querySelector('[value="2"]') as HTMLOptionElement)
        .hidden
    ).toBe(true);
    expect(
      (optionSelectEl().querySelector('[value="3"]') as HTMLOptionElement)
        .hidden
    ).toBe(true);
  });

  it("filters the options by category and sets the incident help", async () => {
    await boot(`<form ${VALUES}>${SELECTS}</form>`);
    categoryEl().value = "Incident";
    fire(categoryEl());

    expect(
      (optionSelectEl().querySelector('[value="1"]') as HTMLOptionElement)
        .hidden
    ).toBe(false);
    expect(
      (optionSelectEl().querySelector('[value="2"]') as HTMLOptionElement)
        .hidden
    ).toBe(true);
    expect(helpEl().textContent).toBe("Incident help");
  });

  it("does not back-fill the type when an option is picked (type-first, no reverse sync)", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2"; // a Request option, picked with no Type chosen
    fire(optionSelectEl());

    expect(categoryEl().value).toBe("");
  });

  it("clears the selected option when it no longer matches the category", async () => {
    await boot(`<form ${VALUES}>${SELECTS}</form>`);
    optionSelectEl().value = "1"; // Incident
    fire(optionSelectEl());
    categoryEl().value = "Request";
    fire(categoryEl());

    expect(optionSelectEl().value).toBe("");
  });

  it("opens the popup with the notify message", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());

    expect(messageEl().textContent).toBe("Notify message");
    expect(modalEl().classList.contains("show")).toBe(true);
    expect(modalEl().style.display).toBe("block");
    expect(backdropEl()).not.toBeNull();
    expect(document.body.classList.contains("modal-open")).toBe(true);
  });

  it("opens the popup with the purchase message", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "3";
    fire(optionSelectEl());

    expect(messageEl().textContent).toBe("Purchase message");
    expect(modalEl().classList.contains("show")).toBe(true);
  });

  it("does not open the popup for a regular option", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "1"; // no data-notify / data-purchase-warning
    fire(optionSelectEl());

    expect(modalEl().classList.contains("show")).toBe(false);
    expect(backdropEl()).toBeNull();
  });

  it("closes the popup when a dismiss control is clicked", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());
    expect(modalEl().classList.contains("show")).toBe(true);

    (
      document.querySelector('[data-bs-dismiss="modal"]') as HTMLElement
    ).click();

    expect(modalEl().classList.contains("show")).toBe(false);
    expect(modalEl().style.display).toBe("none");
    expect(backdropEl()).toBeNull();
    expect(document.body.classList.contains("modal-open")).toBe(false);
  });

  it("reflects a preselected option's category on connect", async () => {
    await boot(
      `<form ${VALUES}>
        <select name="trouble_ticket[typeCategory]" data-${ID}-target="category" data-action="${ID}#apply">
          <option value=""></option>
          <option value="Request">Request</option>
        </select>
        <select name="trouble_ticket[type]" data-${ID}-target="option" data-action="${ID}#onOptionChange">
          <option value="2" data-category="Request" selected>Access</option>
        </select>
        <span data-${ID}-target="help"></span>
      </form>`
    );

    expect(categoryEl().value).toBe("Request");
  });

  it("does nothing when the targets are missing", async () => {
    await boot(`<form ${VALUES}><span data-${ID}-target="help"></span></form>`);

    expect(helpEl().textContent).toBe("");
  });

  it("closes the popup on Escape", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());
    expect(modalEl().classList.contains("show")).toBe(true);

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));

    expect(modalEl().classList.contains("show")).toBe(false);
    expect(backdropEl()).toBeNull();
    expect(document.body.classList.contains("modal-open")).toBe(false);
  });

  it("ignores other keys", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "Enter" }));

    expect(modalEl().classList.contains("show")).toBe(true);
  });

  it("closes the popup when the surface outside the dialog is clicked", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());
    expect(modalEl().classList.contains("show")).toBe(true);

    modalEl().click();

    expect(modalEl().classList.contains("show")).toBe(false);
    expect(backdropEl()).toBeNull();
  });

  it("tidies the popup up on disconnect", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());
    expect(backdropEl()).not.toBeNull();

    (
      document.querySelector(`[data-controller="${ID}"]`) as HTMLElement
    ).remove();
    await Promise.resolve();

    expect(backdropEl()).toBeNull();
    expect(document.body.classList.contains("modal-open")).toBe(false);
  });

  it("does not reopen the popup when one is already open", async () => {
    await boot(`<form ${VALUES}>${SELECTS}${MODAL}</form>`);
    optionSelectEl().value = "2";
    fire(optionSelectEl());
    const first = backdropEl();

    optionSelectEl().value = "3";
    fire(optionSelectEl());

    expect(backdropEl()).toBe(first);
  });

  it("does not throw for a notify option when no modal is present", async () => {
    await boot(`<form ${VALUES}>${SELECTS}</form>`);
    optionSelectEl().value = "2";

    expect(() => fire(optionSelectEl())).not.toThrow();
    expect(backdropEl()).toBeNull();
  });

  it("does nothing on apply when only one select target is present", async () => {
    await boot(
      `<form ${VALUES}>
        <select name="trouble_ticket[typeCategory]" data-${ID}-target="category" data-action="${ID}#apply">
          <option value=""></option>
          <option value="Incident">Incident</option>
        </select>
        <span data-${ID}-target="help"></span>
      </form>`
    );

    categoryEl().value = "Incident";
    expect(() => fire(categoryEl())).not.toThrow();
    expect(helpEl().textContent).toBe("");
  });
});

import { afterAll, beforeAll, describe, expect } from "@jest/globals";
import { Application } from "@hotwired/stimulus";
import FormCollectionController from "../controllers/form_collection_controller";

describe("FormCollectionController", () => {
  let application: Application;
  let addElement: HTMLButtonElement;
  let collection: HTMLDivElement;

  beforeAll(() => {
    document.body.innerHTML = `
        <div
            data-controller="form-collection"
            data-form-collection-index-value="0"
            data-form-collection-prototype-value='
                <div class="collection___name__ item">
                    <button type="button" class="delete" data-action="form-collection#removeCollectionElement"></button>
                </div>
            '
        >
            <div id="collection" data-form-collection-target="collectionContainer"></div>
            <button type="button" id="add" data-action="form-collection#addCollectionElement"></button>
        </div>
    `;
    addElement = document.querySelector("#add") as HTMLButtonElement;
    collection = document.querySelector("#collection") as HTMLDivElement;

    application = Application.start();
    application.register("form-collection", FormCollectionController);
  });

  it("should create 3 childs when click 3 times on add button", () => {
    addElement.click();
    addElement.click();
    addElement.click();

    expect(collection.childElementCount).toBe(3);
  });

  it("should remove child when click on delete button", () => {
    const beforeCount = collection.childElementCount;
    const lastChild = collection.lastChild as HTMLDivElement;
    const deleteElement: HTMLButtonElement =
      lastChild.querySelector("button.delete");

    deleteElement.click();

    expect(beforeCount).toBe(3);
    expect(collection.childElementCount).toBe(2);
  });

  afterAll(() => {
    document.body.innerHTML = "";
    application.stop();
  });
});

describe("FormCollectionController failed with no form-collection-item", () => {
  let application: Application;

  beforeAll(() => {
    document.body.innerHTML = `
        <div
            data-controller="form-collection"
            data-form-collection-index-value="0"
            data-form-collection-prototype-value='
                <div class="collection___name__ item">
                    <button type="button" class="delete" data-action="form-collection#removeCollectionElement"></button>
                </div>
            '
        >
            <div id="collection" data-form-collection-target="collectionContainer">
                <div class="form-collection-item-bad-class">
                    <button type="button" class="delete" data-action="form-collection#removeCollectionElement"></button>
                </div>
            </div>
            <button type="button" id="add" data-action="form-collection#addCollectionElement"></button>
        </div>
    `;

    application = Application.start();
    application.register("form-collection", FormCollectionController);
  });

  it("should throw error when item not found", () => {
    const deleteButton = document.querySelector(
      "#collection .delete"
    ) as HTMLButtonElement;

    let thrownError: Error | null = null;

    application.handleError = (error: Error) => {
      thrownError = error;
    };

    deleteButton.click();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError.message).toBe(
      'Collection item with class "form-collection-item" not found.'
    );
  });

  afterAll(() => {
    document.body.innerHTML = "";
    application.stop();
  });
});

describe("FormCollectionController failed with form-collection-item out of collection", () => {
  let application: Application;

  beforeAll(() => {
    document.body.innerHTML = `
        <div class="form-collection-item">
          <div
              data-controller="form-collection"
              data-form-collection-index-value="0"
              data-form-collection-prototype-value='
                  <div class="collection___name__ item">
                      <button type="button" class="delete" data-action="form-collection#removeCollectionElement"></button>
                  </div>
              '
          >
              <div id="collection" data-form-collection-target="collectionContainer">
                  <div>
                      <button type="button" class="delete" data-action="form-collection#removeCollectionElement"></button>
                  </div>
              </div>
              <button type="button" id="add" data-action="form-collection#addCollectionElement"></button>
          </div>
        </div>
    `;

    application = Application.start();
    application.register("form-collection", FormCollectionController);
  });

  it("should throw error when item not found", () => {
    const deleteButton = document.querySelector(
      "#collection .delete"
    ) as HTMLButtonElement;

    let thrownError: Error | null = null;

    application.handleError = (error: Error) => {
      thrownError = error;
    };

    deleteButton.click();

    expect(thrownError).toBeInstanceOf(Error);
    expect(thrownError.message).toBe(
      'Item "form-collection-item" is not part of the collection.'
    );
  });

  afterAll(() => {
    document.body.innerHTML = "";
    application.stop();
  });
});

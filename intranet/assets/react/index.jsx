/* eslint-disable import/no-import-module-exports */
import React from "react";
import { Provider } from "react-redux";
import { createRoot } from "react-dom/client";
import { Route, Routes, BrowserRouter as Router } from "react-router-dom";
import { syncHistoryWithStore } from "react-router-redux";
import Localization from "react-widgets/Localization";
import moment from "moment";
import MomentLocalizer from "react-widgets-moment";
import $ from "jquery";
import Translator from "bazinga-translator";
import Search from "./containers/Search/Search";
import store, { history } from "./store";
import MenuLine from "./components/Search/MenuLine";
import NotFound from "./components/NotFound";
import { FaqFileUploader } from "./components/FirstArticleQualification/FaqFileUploader";
import SalesForecastQuickEditForm from "./containers/SFR/SalesForecastQuickEditForm";
import Activity from "./containers/Activity/Activity";
import SymfonySelect from "./components/Forms/SymfonySelect";
import EquipmentRecordSelectMultiple from "./components/Forms/EquipmentRecordSelectMultiple";
import ProductsQuickEdit from "./containers/Catalogue/ProductsQuickEdit";
import FinanceFamiliesQuickEdit from "./containers/Finance/FinanceFamiliesQuickEdit";
import SalesForecastForm from "./containers/SFR/SalesForecastCreationForm";
import { FileUploader } from "./components/FileUploader";
import Subscriptions from "./containers/Common/Subscriptions";
import MarketIntelligenceWriteForm from "./containers/MarketIntelligence/MarketIntelligenceWriteForm";
import { MarketIntelligenceFileUploader } from "./components/MarketIntelligence/MarketIntelligenceFileUploader";
import MarketIntelligenceSubsciptionCreationForm from "./containers/MarketIntelligence/MarketIntelligenceSubscriptionCreationForm";
import ProductManufacturingForm from "./containers/Manufacturing/ProductManufacturingForm";
import CustomersWatchListQuickEdit from "./containers/Customer/CustomersWatchListQuickEdit";
import PeopleQuickSearch from "./components/Forms/PeopleQuickSearch";
import ListActivityButton from "./components/Activity/ListActivityButton";
import TOCSparePartsRequestForm from "./containers/SparePartsRequest/TOCSparePartsRequestForm";
import SBSparePartsRequestForm from "./containers/SparePartsRequest/SBSparePartsRequestForm";
import SparePartsRequestAddressForm from "./containers/SparePartsRequest/SparePartsRequestAddressForm";
import CustomerRelationshipTeamForm from "./containers/CustomerRelationshipTeam/CustomerRelationshipTeamForm";
import PartsForm from "./containers/Part/PartsForm";
import OnTimeDeliveryEditForm from "./containers/Support/OnTimeDeliveryEditForm";
import { EquipmentShippingRecordFileUploader } from "./components/EquipmentShippingRecord/EquipmentShippingRecordFileUploader";
import { CustomerServiceRecordRecordFileUploader } from "./components/Service/CustomerServiceRecordFileUploader";
import { TroubleTicketFileUploader } from "./components/MIS/TroubleTicketFileUploader";
import ActivityCustomerServiceRecords from "./containers/Activity/ActivityCustomerServiceRecords";
import ActivityTechnicianOnCall from "./containers/Activity/ActivityTechnicianOnCall";
import UserStoryForm from "./containers/Specification/UserStoryForm";
import { UserStoryFileUploader } from "./components/Specification/UserStoryFileUploader";
import UserStoryShow from "./containers/Specification/UserStoryShow";
import { TaskFileUploader } from "./components/Task/TaskFileUploader";
import TechnicianOnCallForm from "./containers/TechnicianOnCall/TechnicianOnCallForm";
import { TechnicianOnCallParts } from "./containers/Service/TechnicianOnCallParts";
import EventForm from "./containers/HumanResources/Event/EventForm";
import { ProjectFileUploader } from "./components/MIS/ProjectFileUploader";
import TaskForm from "./containers/Task/TaskForm";
import { PictogramFileUploader } from "./components/Engineering/PictogramFileUploader";
import SupplierCorrectiveActionRequestCreateForm from "./containers/Quality/SupplierCorrectiveActionRequest/SupplierCorrectiveActionRequestCreateForm";
import SupplierCorrectiveActionRequestUpdateForm from "./containers/Quality/SupplierCorrectiveActionRequest/SupplierCorrectiveActionRequestUpdateForm";
import SupportTeamForm from "./containers/MIS/SupportTeamForm";
import { ContractFileUploader } from "./components/Legal/ContractFileUploader";
import ContractAiAnalyze from "./components/Legal/ContractAiAnalyze";
import SampleForm from "./containers/SampleForm/SampleForm";
import { GlobalChat } from "./containers/GlobalChat/GlobalChat";
import DocumentTranslationPage from "./components/DocumentTranslationPage/DocumentTranslationPage";
import AddContract from "./containers/AddContract/AddContract";
import EditContract from "./containers/EditContract/EditContract";
import ListContractCategory from "./containers/ListContractCategory/ListContractCategory";
import ListContractSubCategory from "./containers/ListContractSubCategory/ListContractSubCategory";
import AddProductFamily from "./containers/AddProductFamily/AddProductFamily";
import EditProductFamily from "./containers/EditProductFamily/EditProductFamily";
import { SparePartsRequestFileUploader } from "./components/SPR/SparePartsRequestFileUploader";
import ProjectChart from "./containers/ProjectChart/ProjectChart";
import AddAircraftCompatibilityForm from "./containers/AddAircraftCompatibilityForm/AddAircraftCompatibilityForm";
import EditAircraftCompatibilityForm from "./containers/EditAircraftCompatibilityForm/EditAircraftCompatibilityForm";
import AddAircraftForm from "./containers/AddAircraftForm/AddAircraftForm";
import EditAircraftForm from "./containers/EditAircraftForm/EditAircraftForm";
import AddAircraftCompatibilityFileForm from "./containers/AddAircraftCompatibilityFileForm/AddAircraftCompatibilityFileForm";
import SampleDataTable from "./containers/SampleDataTable/SampleDataTable";
import AddContactCampaign from "./containers/AddContactCampaign/AddContactCampaign";
import EditContactCampaign from "./containers/EditContactCampaign/EditContactCampaign";
import ContractStatusForm from "./components/ContractForm/ContractStatusForm";
import QrCodePlate from "./containers/QrCodePlate/QrCodePlate";
import EditContractCategory from "./containers/EditContractCategory/EditContractCategory";
import EditContractSubCategory from "./containers/EditContractSubCategory/EditContractSubCategory";
import TechnicianOnCallDuplicateForm from "./containers/TechnicianOnCall/TechnicianOnCallDuplicateForm";
import { TechnicianOnCallFileUploader } from "./components/TechnicianOnCall/TechnicianOnCallFileUploader";
import { TechnicianOnCallStatus } from "./containers/TechnicianOnCallStatus/TechnicianOnCallStatus";
import { TechnicianOnCallSurvey } from "./containers/Service/TechnicianOnCallSurvey";
import { TechnicianOnCallAiSearch } from "./containers/TechnicianOnCallAiSearch/TechnicianOnCallAiSearch";

const customers = window?.SPR_APP_PROPS?.customers;
if (customers && typeof customers === "object" && !Array.isArray(customers)) {
  window.SPR_APP_PROPS.customers = Object.values(customers);
}

function isValidJSON(str) {
  if (typeof str !== "string") {
    return false;
  }
  try {
    JSON.parse(str);
    return true;
  } catch (e) {
    return false;
  }
}

moment.locale("en");
const localizer = new MomentLocalizer(moment);

if (Translator.locale === "zh_CN") {
  Translator.locale = "zh-CN";
}

const menu = [];
if (document.querySelector("#searchBar")) {
  $("#side-menu")
    .find("ul.accordion-collapse li a")
    .each(function () {
      const $this = $(this);
      if ($this.attr("href") !== "#") {
        menu.push(
          new MenuLine(
            $this.attr("href"),
            $this.text(),
            $this.closest("li.accordion-item").find("span").first().text(),
            $this.closest("ul.thirdLevel").siblings("a").first().text(),
            $this.data("agr"),
            $this.attr("title")
          )
        );
      }
    });

  const rootElement = document.querySelector("#searchBar");
  const root = createRoot(rootElement);
  root.render(
    <Provider store={store}>
      <Search menuLines={menu} />
    </Provider>
  );
}

syncHistoryWithStore(history, store);
if (document.querySelector("#react")) {
  const element = document.querySelector("#react");
  const root = createRoot(element);
  root.render(
    <Localization date={localizer}>
      <Provider store={store}>
        <Router>
          <Routes>
            <Route
              exact
              path="/en/private/quality/first-article-qualifications/:id/files"
              element={<FaqFileUploader />}
            />
            <Route
              exact
              path="/en/private/sales/market-intelligences/:id/files"
              element={<MarketIntelligenceFileUploader />}
            />
            <Route
              exact
              path="/en/private/sales/sales-forecasts/quick-edit"
              element={<SalesForecastQuickEditForm {...element.dataset} />}
            />
            <Route
              exact
              path="/en/private/sales/orders/:id/files"
              element={<FileUploader />}
            />
            <Route
              exact
              path="/en/private/parts/transportation-note/:id/show"
              element={<FileUploader />}
            />
            <Route
              exact
              path="/en/private/sales/catalogue/products"
              element={<ProductsQuickEdit />}
            />
            <Route
              exact
              path="/en/private/sales/catalogue/products/:visibility"
              element={<ProductsQuickEdit />}
            />
            <Route
              exact
              path="/en/private/sales/market-intelligences/add"
              element={
                <MarketIntelligenceWriteForm
                  {...element.dataset}
                  {...window.MIM_APP_PROPS}
                />
              }
            />
            <Route
              exact
              path="/en/private/sales/market-intelligences/:id/Edit"
              element={
                <MarketIntelligenceWriteForm {...window.MIM_APP_PROPS} />
              }
            />
            <Route
              exact
              path="/en/private/sales/market-intelligence-subscriptions/add"
              element={<MarketIntelligenceSubsciptionCreationForm />}
            />
            <Route
              exact
              path="/en/private/finance/finance-families"
              element={<FinanceFamiliesQuickEdit {...element.dataset} />}
            />
            <Route
              exact
              path="/en/private/manufacturing/product-manufacturing"
              element={<ProductManufacturingForm {...element.dataset} />}
            />
            <Route
              exact
              path="/en/private/sales/customers/watch-list"
              element={<CustomersWatchListQuickEdit />}
            />
            <Route
              exact
              path="/en/private/human-resources/jobs/:id/show"
              element={<FileUploader />}
            />
            <Route
              exact
              path="/en/private/sales/sales-forecasts/add"
              element={<SalesForecastForm />}
            />
            <Route
              exact
              path="/en/private/sales/sales-forecasts/:id/edit"
              element={<SalesForecastForm {...element.dataset} />}
            />
            <Route
              exact
              path="/en/private/parts/spare-parts-requests/toc/:id/add-parts"
              element={
                <TOCSparePartsRequestForm
                  {...element.dataset}
                  {...window.SPR_APP_PROPS}
                />
              }
            />
            <Route
              exact
              path="/en/private/parts/spare-parts-requests/sb/:id/create"
              element={
                <SBSparePartsRequestForm
                  {...element.dataset}
                  {...window.SPR_APP_PROPS}
                />
              }
            />
            <Route
              exact
              path="/en/private/parts/spare-parts-requests/:id/edit-address"
              element={
                <SparePartsRequestAddressForm
                  {...element.dataset}
                  {...window.SPR_APP_PROPS}
                />
              }
            />
            <Route
              exact
              path="/en/private/sales/customer-relationship-teams/add"
              element={
                <CustomerRelationshipTeamForm {...window.CRT_APP_PROPS} />
              }
            />
            <Route
              exact
              path="/en/private/sales/customer-relationship-teams/:id/edit"
              element={
                <CustomerRelationshipTeamForm {...window.CRT_APP_PROPS} />
              }
            />
            <Route
              exact
              path="/en/private/sales/customer-relationship-teams/:id/duplicate"
              element={
                <CustomerRelationshipTeamForm {...window.CRT_APP_PROPS} />
              }
            />
            <Route
              exact
              path="/en/private/quality/non-conformities/:id/admin-parts"
              element={<PartsForm {...window.NCR_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/quality/supplier-corrective-action-requests/:id/admin-parts"
              element={<PartsForm {...window.SCAR_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/purchasing/vendor-warranty-claims/:id/admin-parts"
              element={<PartsForm {...window.VWC_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/sales/equipment_shipping_records/:id/show"
              element={<EquipmentShippingRecordFileUploader />}
            />
            <Route
              exact
              path="/en/private/service/customer-service-records/:id/edit"
              element={<CustomerServiceRecordRecordFileUploader />}
            />
            <Route
              path="/en/private/support/on_time_delivery_planning/edit"
              element={<OnTimeDeliveryEditForm {...window.ODP_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/mis/trouble-tickets/:id/show/files"
              element={<TroubleTicketFileUploader />}
            />
            <Route
              exact
              path="/en/private/mis/modules/:moduleId/specifications/:id/user-stories/add"
              element={<UserStoryForm {...window.SPECIFICATION_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/mis/modules/:moduleId/specifications/:id/user-stories/:userStoryId/edit"
              element={<UserStoryForm {...window.SPECIFICATION_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/mis/modules/:moduleId/specifications/:id/files"
              element={
                <UserStoryFileUploader {...window.SPECIFICATION_APP_PROPS} />
              }
            />
            <Route
              exact
              path="/en/private/mis/modules/:moduleId/specifications/:id/show"
              element={<UserStoryShow {...window.SPECIFICATION_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/human-resources/events/add"
              element={<EventForm />}
            />
            <Route
              exact
              path="/en/private/human-resources/events/:id/edit"
              element={<EventForm {...window.SF_INITIAL_STORE_STATE} />}
            />
            <Route
              exact
              path="/en/private/tasks/:id/show"
              element={<TaskFileUploader />}
            />
            <Route
              exact
              path="/en/private/mis/projects/:id/show"
              element={<ProjectFileUploader />}
            />
            <Route
              exact
              path="/en/private/tasks/add"
              element={<TaskForm {...window.TASK_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/tasks/:id/edit"
              element={<TaskForm {...window.TASK_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/sample-form"
              element={<SampleForm />}
            />
            <Route
              exact
              path="/en/private/mis/projects/chart"
              element={<ProjectChart />}
            />
            <Route
              path="/en/private/sample-data-table"
              element={<SampleDataTable />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/ai-analyze"
              element={
                <ContractAiAnalyze {...window.CONTRACT_AI_ANALYZE_PROPS} />
              }
            />
            <Route
              exact
              path="/en/private/legal/contracts/add"
              element={<AddContract {...window.CONTRACT_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/:id/edit"
              element={<EditContract />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/:id/status"
              element={<ContractStatusForm />}
            />
            <Route
              exact
              path="/en/private/engineering/pictograms/:id"
              element={<PictogramFileUploader />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/:id/show"
              element={<ContractFileUploader />}
            />
            <Route
              exact
              path="/en/private/parts/spare-parts-requests/:id/show"
              element={<SparePartsRequestFileUploader />}
            />
            <Route
              exact
              path="/en/private/quality/supplier-corrective-action-requests/add"
              element={
                <SupplierCorrectiveActionRequestCreateForm
                  {...window.SUPPLER_CORRECTIVE_ACTION_REQUEST_APP_PROPS}
                />
              }
            />
            <Route
              exact
              path="/en/private/quality/supplier-corrective-action-requests/:id/edit"
              element={
                <SupplierCorrectiveActionRequestUpdateForm
                  {...window.SUPPLER_CORRECTIVE_ACTION_REQUEST_APP_PROPS}
                />
              }
            />
            <Route
              exact
              path="/en/private/mis/support-teams/:id/edit"
              element={<SupportTeamForm {...window.MIS_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/mis/support-teams/add"
              element={<SupportTeamForm {...window.MIS_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/contact-campaigns/add"
              element={<AddContactCampaign />}
            />
            <Route
              exact
              path="/en/private/contact-campaigns/:id/edit"
              element={<EditContactCampaign />}
            />
            <Route
              exact
              path="/en/private/translator_document"
              element={<DocumentTranslationPage />}
            />
            <Route
              exact
              path="/en/private/legal/contract/categories"
              element={<ListContractCategory />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/categories/:id/edit"
              element={<EditContractCategory />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/sub-categories"
              element={<ListContractSubCategory />}
            />
            <Route
              exact
              path="/en/private/legal/contracts/sub-categories/:id/edit"
              element={<EditContractSubCategory />}
            />
            <Route
              exact
              path="/en/private/sales/catalogue/types/:id/add_family"
              element={<AddProductFamily />}
            />
            <Route
              exact
              path="/en/private/sales/catalogue/families/:id/edit"
              element={<EditProductFamily />}
            />
            <Route
              exact
              path="/en/private/sales/aircraft-compatibilities/add"
              element={<AddAircraftCompatibilityForm />}
            />
            <Route
              exact
              path="/en/private/sales/aircraft-compatibilities/:id/edit"
              element={<EditAircraftCompatibilityForm />}
            />
            <Route
              exact
              path="/en/private/sales/aircrafts/add"
              element={<AddAircraftForm />}
            />
            <Route
              exact
              path="/en/private/sales/aircrafts/:id/edit"
              element={<EditAircraftForm />}
            />
            <Route
              exact
              path="/en/private/sales/aircraft-compatibilities/:id/add-file"
              element={<AddAircraftCompatibilityFileForm />}
            />
            <Route
              exact
              path="/en/private/support/equipment_records/:id/name-plate"
              element={<QrCodePlate />}
            />
            <Route
              exact
              path="/en/private/service/technician-on-calls/add"
              element={<TechnicianOnCallForm {...window.TOC_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/service/technician-on-calls/:id/edit"
              element={<TechnicianOnCallForm {...window.TOC_APP_PROPS} />}
            />
            <Route
              exact
              path="/en/private/service/technician-on-calls/:id/duplicate"
              element={
                <TechnicianOnCallDuplicateForm {...window.TOC_APP_PROPS} />
              }
            />
            <Route element={<NotFound />} />
          </Routes>
        </Router>
      </Provider>
    </Localization>
  );
}

function mountLogsBlocks(root = document) {
  const elements = root.querySelectorAll(".logs-block");

  if (!elements.length) {
    return;
  }

  elements.forEach((element) => {
    if (element.dataset.reactMounted === "true") {
      return;
    }

    element.setAttribute("data-react-mounted", "true");

    const rootReact = createRoot(element);
    rootReact.render(
      <Provider store={store}>
        <Activity {...element.dataset} />
      </Provider>
    );
  });
}

function mountSubscriptions(root = document) {
  const elements = root.querySelectorAll(".subscriptions");

  if (!elements.length) {
    return;
  }

  elements.forEach((element) => {
    if (element.dataset.reactMounted === "true") {
      return;
    }

    element.setAttribute("data-react-mounted", "true");

    const rootReact = createRoot(element);
    rootReact.render(
      <Provider store={store}>
        <Subscriptions {...element.dataset} />
      </Provider>
    );
  });
}

// init initial
mountLogsBlocks();
mountSubscriptions();

window.mountLogsBlocks = mountLogsBlocks;
window.mountSubscriptions = mountSubscriptions;

if (document.querySelector(".logs-customer-service-records")) {
  Array.from(
    document.getElementsByClassName("logs-customer-service-records")
  ).forEach((element) => {
    // valid data attributes: data-data, data-resource, data-hide-logs, data-hide-comments, data-show-comment-form
    const root = createRoot(element);
    root.render(
      <Provider store={store}>
        <ActivityCustomerServiceRecords {...element.dataset} />
      </Provider>
    );
  });
}

if (document.querySelector(".logs-technician_on_call")) {
  Array.from(
    document.getElementsByClassName("logs-technician_on_call")
  ).forEach((element) => {
    // valid data attributes: data-data, data-resource, data-hide-logs, data-hide-comments, data-show-comment-form
    const root = createRoot(element);
    root.render(
      <Provider store={store}>
        <ActivityTechnicianOnCall {...element.dataset} />
      </Provider>
    );
  });
}

if (document.querySelector(".toc-files-dropZone")) {
  Array.from(document.getElementsByClassName("toc-files-dropZone")).forEach(
    (element) => {
      const parsedDataset = {};
      Object.entries(element.dataset).forEach(([key, val]) => {
        if (isValidJSON(val)) {
          parsedDataset[key] = JSON.parse(val);
        } else {
          parsedDataset[key] = val;
        }
      });
      const root = createRoot(element);
      root.render(
        <Provider store={store}>
          <TechnicianOnCallFileUploader {...parsedDataset} refreshOnSuccess />
        </Provider>
      );
    }
  );
}

if (document.querySelector(".subscriptions")) {
  Array.from(document.getElementsByClassName("subscriptions")).forEach(
    (element) => {
      // valid data attributes: data-resource, data-show-create-people, data-show-delete, data-show-delete-myself
      const root = createRoot(element);
      root.render(
        <Provider store={store}>
          <Subscriptions {...element.dataset} />
        </Provider>
      );
    }
  );
}

if (document.querySelector(".react-select")) {
  Array.from(document.getElementsByClassName("react-select")).forEach(
    (element) => {
      const newElement = document.createElement("div");
      element.parentNode.append(newElement);
      const { value, name } = element;
      const parsedDataset = {};
      Object.entries(element.dataset).forEach(([key, val]) => {
        if (isValidJSON(val)) {
          parsedDataset[key] = JSON.parse(val);
        } else {
          parsedDataset[key] = val;
        }
      });

      element.remove();

      // valid data attributes: data-component-alias, data-label, data-name, data-inline, data-filter
      const root = createRoot(newElement);
      root.render(
        <Provider store={store}>
          <SymfonySelect {...parsedDataset} name={name} initialValue={value} />
        </Provider>
      );
    }
  );
}

if (document.querySelector(".react-equipment-record-select-custom")) {
  Array.from(
    document.getElementsByClassName("react-equipment-record-select-custom")
  ).forEach((element) => {
    const newElement = document.createElement("div");
    element.parentNode.append(newElement);
    element.remove();
    const root = createRoot(newElement);
    root.render(
      <Provider store={store}>
        <EquipmentRecordSelectMultiple {...element.dataset} />
      </Provider>
    );
  });
}

if (document.querySelector("#people-quick-search")) {
  const element = document.querySelector("#people-quick-search");
  const root = createRoot(element);
  root.render(
    <Provider store={store}>
      <PeopleQuickSearch />
    </Provider>
  );
}

if (document.querySelector("#tts-file-uploader")) {
  const element = document.querySelector("#tts-file-uploader");
  const root = createRoot(element);
  root.render(
    <Provider store={store}>
      <Router>
        <TroubleTicketFileUploader id={element.dataset.id} />
      </Router>
    </Provider>
  );
}

if (document.querySelector("#technician-on-call-parts")) {
  const element = document.querySelector("#technician-on-call-parts");
  const root = createRoot(element);
  root.render(
    <Provider store={store}>
      <TechnicianOnCallParts {...element.dataset} />
    </Provider>
  );
}

if (document.querySelector("#technician-on-call-survey")) {
  const element = document.querySelector("#technician-on-call-survey");
  const root = createRoot(element);
  root.render(
    <Provider store={store}>
      <TechnicianOnCallSurvey {...element.dataset} />
    </Provider>
  );
}

if (document.querySelector(".activity-list")) {
  Array.from(document.getElementsByClassName("activity-list")).forEach(
    (element) => {
      // valid data attributes: data-resource, data-title
      const root = createRoot(element);
      root.render(
        <Provider store={store}>
          <ListActivityButton {...element.dataset} />
        </Provider>
      );
    }
  );
}

if (document.querySelector("#global-chat")) {
  const element = document.querySelector("#global-chat");
  const root = createRoot(element);
  root.render(<GlobalChat />);
}

if (document.querySelector("#technician-on-call-status")) {
  const element = document.querySelector("#technician-on-call-status");
  const root = createRoot(element);
  root.render(
    <Localization date={localizer}>
      <Provider store={store}>
        <TechnicianOnCallStatus />
      </Provider>
    </Localization>
  );
}

if (document.querySelector("#technician-on-call-ai-search")) {
  const element = document.querySelector("#technician-on-call-ai-search");
  const root = createRoot(element);
  root.render(
    <Localization date={localizer}>
      <Provider store={store}>
        <TechnicianOnCallAiSearch />
      </Provider>
    </Localization>
  );
}

if (module.hot) {
  module.hot.accept();
}

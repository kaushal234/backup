import { combineReducers, Reducer } from "redux";
import { routerReducer } from "react-router-redux";
import { reducer as formReducer } from "redux-form";
import searchReducer from "./search/searchReducer";
import userReducer from "./user/userReducer";
import erpReducer from "./erp/erpReducer";
import locationReducer from "./location/locationReducer";
import salesForecastReducer from "./sfr/salesForecastReducer";
import activityReducer from "./activity/activityReducer";
import airportReducer from "./apc/airportReducer";
import customerReducer from "./customer/customerReducer";
import countryReducer from "./country/countryReducer";
import emissionRatingReducer from "./emissionRating/emissionRatingReducer";
import productReducer from "./catalogue/productReducer";
import financeFamilyReducer from "./finance/financeFamilyReducer";
import dmsReducer from "./dms/dmsReducer";
import subscriptionsReducer from "./common/subscriptionsReducer";
import competitorReducer from "./competitor/competitorReducer";
import productTypeReducer from "./catalogue/productTypeReducer";
import marketIntelligenceReducer from "./marketIntelligence/marketIntelligenceReducer";
import marketIntelligenceSubscriptionReducer from "./marketIntelligence/marketIntelligenceSubscriptionReducer";
import competitorPricingReducer from "./competitorPricing/competitorPricingReducer";
import equipmentRecordReducer from "./equipmentRecord/equipmentRecordReducer";
import businessUnitReducer from "./businessUnit/businessUnitReducer";
import extranetUserReducer from "./extranetUser/extranetUserReducer";
import extranetUserTypedReducer from "./extranetUserTyped/extranetUserTypedSlice";
import productFamilyReducer from "./catalogue/productFamilyReducer";
import printersReducer from "./common/printersReducer";
import sparePartsRequestReducer from "./sparePartsRequest/sparePartsRequestReducer";
import customerRelationshipTeamReducer from "./customerRelationshipTeam/customerRelationshipTeamReducer";
import partReducer from "./part/partReducer";
import nonConformityReducer from "./nonConformity/nonConformityReducer";
import misReducer from "./mis/misReducer";
import roleReducer from "./role/roleReducer";
import specificationReducer from "./userStory/specificationReducer";
import eventReducer from "./humanResources/event/eventReducer";
import taskReducer from "./task/taskReducer";
import counterReducer from "./counter/counterSlice";
import technicianOnCallReducer from "./technicianOnCall/technicianOnCallReducer";
import tagReducer from "./tag/tagReducer";
import dataReducer from "./data/dataSlice";
import supplierCorrectiveActionRequestReducer from "./quality/supplierCorrectiveRequest/SupplierCorrectiveActiveRequestReducer";
import loaderReducer from "./loader/loaderSlice";
import dataTableReducer from "./dataTable/dataTableSlice";
import tocDetailReducer from "./tocDetail/tocDetailSlice";

export const reducers = {
  search: searchReducer,
  user: userReducer,
  erp: erpReducer,
  location: locationReducer,
  sfr: salesForecastReducer,
  activity: activityReducer,
  form: formReducer,
  task: taskReducer,
  event: eventReducer,
  routing: routerReducer,
  apc: airportReducer,
  catalogue: productReducer,
  dms: dmsReducer,
  customer: customerReducer,
  country: countryReducer,
  emissionRating: emissionRatingReducer,
  product: productReducer,
  finance: financeFamilyReducer,
  common: subscriptionsReducer,
  competitor: competitorReducer,
  productType: productTypeReducer,
  marketIntelligence: marketIntelligenceReducer,
  marketIntelligenceSubscription: marketIntelligenceSubscriptionReducer,
  competitorPricing: competitorPricingReducer,
  equipmentRecord: equipmentRecordReducer,
  businessUnit: businessUnitReducer,
  extranetUser: extranetUserReducer,
  extranetUserTyped: extranetUserTypedReducer,
  productFamily: productFamilyReducer,
  printers: printersReducer,
  spr: sparePartsRequestReducer,
  crt: customerRelationshipTeamReducer,
  part: partReducer,
  nonConformity: nonConformityReducer,
  mis: misReducer,
  specification: specificationReducer,
  role: roleReducer,
  counter: counterReducer,
  data: dataReducer,
  supplierCorrectiveActionRequest: supplierCorrectiveActionRequestReducer,
  loader: loaderReducer,
  dataTable: dataTableReducer,
  technicianOnCall: technicianOnCallReducer as Reducer<any, any>,
  tag: tagReducer as Reducer<any, any>,
  tocDetail: tocDetailReducer,
};

export default combineReducers(reducers);

export { default as searchReducer } from "./search/searchReducer";
export { default as userReducer } from "./user/userReducer";
export { reducer as formReducer } from "redux-form";

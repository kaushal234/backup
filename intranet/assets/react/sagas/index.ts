import { all } from "redux-saga/effects";
import commentsSagas from "./activity/commentsSagas";
import termsOfDeliverySagas from "./incoterm/termsOfDeliverySagas";
import currenciesSagas from "./currency/currenciesSagas";
import businessPartnersSagas from "./erp/businessPartnersSagas";
import userSagas from "./user/userSagas";
import salesForecastSagas from "./sfr/salesForecastSagas";
import logSagas from "./activity/logSagas";
import airportSagas from "./apc/airportSagas";
import customersSagas from "./customer/customersSagas";
import countriesSagas from "./country/countriesSagas";
import productsSagas from "./catalogue/productsSagas";
import financeFamiliesSagas from "./finance/financeFamiliesSagas";
import dmsSagas from "./dms/dmsSagas";
import subscriptionsSagas from "./common/subscriptionsSagas";
import competitorSagas from "./competitor/competitorSagas";
import marketIntelligenceSagas from "./marketIntelligence/marketIntelligenceSagas";
import marketIntelligenceSubscriptionSagas from "./marketIntelligence/marketIntelligenceSubscriptionSagas";
import competitorPricingSagas from "./competitorPricing/competitorPricingSagas";
import productManufacturingSagas from "./productManufacturing/productManufacturingSagas";
import equipmentRecordSagas from "./equipmentRecord/equipmentRecordSagas";
import businessUnitSagas from "./businessUnit/businessUnitSagas";
import extranetUserSagas from "./extranetUser/extranetUserSagas";
import productFamilySagas from "./catalogue/productFamilySagas";
import printersSagas from "./common/printersSagas";
import sparePartsRequestSagas from "./sparePartsRequest/sparePartsRequestSagas";
import customerRelationshipTeamSagas from "./customerRelationshipTeam/customerRelationshipTeamSagas";
import extranetUserAclSagas from "./extranetUser/extranetUserAclSagas";
import itemSagas from "./erp/itemSagas";
import itemMonologisticSagas from "./part/itemMonologisticSagas";
import partSagas from "./part/partSagas";
import nonConformitySagas from "./nonConformity/nonConformitySagas";
import misSagas from "./mis/misSagas";
import preDeliveryInspectionSaga from "./inspection/preDeliveryInspectionSaga";
import roleSagas from "./role/roleSagas";
import userStorySagas from "./userStory/userStorySagas";
import eventSagas from "./humanResources/event/eventSagas";
import taskSagas from "./task/taskSagas";
import locationSagas from "./location/locationSagas";
import supplierCorrectiveActionRequestSaga from "./quality/supplierCorrectiveActionRequest/SupplierCorrectiveActionRequestSaga";
import tagSagas from "./tag/tagSagas";

export default function* root() {
  yield all([
    commentsSagas(),
    termsOfDeliverySagas(),
    currenciesSagas(),
    userSagas(),
    businessPartnersSagas(),
    salesForecastSagas(),
    logSagas(),
    airportSagas(),
    customersSagas(),
    countriesSagas(),
    dmsSagas(),
    productsSagas(),
    financeFamiliesSagas(),
    subscriptionsSagas(),
    competitorSagas(),
    marketIntelligenceSagas(),
    marketIntelligenceSubscriptionSagas(),
    competitorPricingSagas(),
    productManufacturingSagas(),
    equipmentRecordSagas(),
    businessUnitSagas(),
    extranetUserSagas(),
    productFamilySagas(),
    printersSagas(),
    sparePartsRequestSagas(),
    customerRelationshipTeamSagas(),
    extranetUserAclSagas(),
    itemSagas(),
    itemMonologisticSagas(),
    partSagas(),
    nonConformitySagas(),
    misSagas(),
    preDeliveryInspectionSaga(),
    roleSagas(),
    userStorySagas(),
    eventSagas(),
    taskSagas(),
    locationSagas(),
    supplierCorrectiveActionRequestSaga(),
    tagSagas(),
  ]);
}

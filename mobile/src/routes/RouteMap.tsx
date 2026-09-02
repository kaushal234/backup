import React from "react";
import { BrowserRouter, Route, Routes } from "react-router";
import { ROUTES } from "../constants/routes";
import NavigationWrapper from "../components/NavigationWrapper/NavigationWrapper";
import AuthenticatedRoute from "./AuthenticatedRoute";
import HomeAppBar from "../components/HomeAppBar/HomeAppBar";
import PreLoginRoute from "./PreLoginRoute";
import Home from "../pages/Home/Home";
import Login from "../pages/Login/Login";
import TocList from "../pages/TocList/TocList";
import CsrList from "../pages/CsrList/CsrList";
import TocFilter from "../pages/TocFilter/TocFilter";
import TocDetails from "../pages/TocDetails/TocDetails";
import ErFinder from "../pages/ErFinder/ErFinder";
import ErList from "../pages/ErList/ErList";
import ErDetails from "../pages/ErDetails/ErDetails";
import ErFilter from "../pages/ErFilter/ErFilter";
import CsrDetails from "../pages/CsrDetails/CsrDetails";
import CsrFilter from "../pages/CsrFilter/CsrFilter";
import ManualDetails from "../pages/ManualDetails/ManualDetails";
import ManualDocument from "../pages/ManualDocument/ManualDocument";
import Profile from "../pages/Profile/Profile";
import NetworkStatusBar from "../components/NetworkStatusBar/NetworkStatusBar";
import NotFound from "../pages/NotFound/NotFound";
import TocCreate from "../pages/TocCreate/TocCreate";
import TocUpdate from "../pages/TocUpdate/TocUpdate";
import Settings from "../pages/Settings/Settings";
import TocPartsCreate from "../pages/TocPartsCreate/TocPartsCreate";
import TocPartsUpdate from "../pages/TocPartsUpdate/TocPartsUpdate";
import TocAiSearch from "../pages/TocAiSearch/TocAiSearch";
import CsrUpdate from "../pages/CsrUpdate/CsrUpdate";
import CsrSurvey from "../pages/CsrSurvey/CsrSurvey";
import FeatureRoute from "./FeatureRoute";
import ExtranetUser from "../pages/ExtranetUser/ExtranetUser";
import AzureRedirect from "../pages/AzureRedirect/AzureRedirect";
import UnauthenticatedRoute from "./UnauthenticatedRoute";
import TocSprCreate from "../pages/TocSprCreate/TocSprCreate";

function RouteMap() {
  return (
    <BrowserRouter>
      <Routes>
        <Route
          path={ROUTES.home}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <Home />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.azure}
          element={
            <NavigationWrapper>
              <UnauthenticatedRoute>
                <NetworkStatusBar />
                <AzureRedirect />
              </UnauthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.login}
          element={
            <NavigationWrapper>
              <PreLoginRoute>
                <NetworkStatusBar />
                <Login />
              </PreLoginRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.toc.home}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocList />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.csr.home}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <CsrList />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.toc.filter}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocFilter />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.toc.details}/:tocId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocDetails />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.er.home}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ErList />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.er.finder}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ErFinder />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.er.details}/:erId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ErDetails />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.er.filter}`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ErFilter />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.csr.details}/:csrId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <CsrDetails />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.csr.filter}`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <CsrFilter />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.er.details}/:erId/${ROUTES.er.manual.home}/:manualId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ManualDetails />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.er.details}/:erId/${ROUTES.er.manual.home}/:manualId/${ROUTES.er.manual.document}/:manualDocumentId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ManualDocument />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.profile}/:profileId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <Profile />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.toc.create}`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocCreate />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.toc.update}/:tocId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <FeatureRoute features={["FEATURE_TECHNICIAN_ON_CALL_EDIT"]}>
                  <HomeAppBar noCard>
                    <TocUpdate />
                  </HomeAppBar>
                </FeatureRoute>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.settings}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <Settings />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.toc.details}/:tocId/${ROUTES.toc.parts.create}`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocPartsCreate />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.toc.details}/:tocId/${ROUTES.toc.parts.update}/:tocPartId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocPartsUpdate />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.toc.details}/:tocId/${ROUTES.toc.spr.create}`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <FeatureRoute
                  features={["FEATURE_SPARE_PARTS_REQUESTS_CREATE", "MOO_SPR"]}
                >
                  <HomeAppBar noCard>
                    <TocSprCreate />
                  </HomeAppBar>
                </FeatureRoute>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={ROUTES.toc.search}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <TocAiSearch />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path="*"
          element={
            <NavigationWrapper>
              <NetworkStatusBar />
              <NotFound />
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.csr.update}/:csrId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <FeatureRoute
                  features={["FEATURE_CUSTOMER_SERVICE_RECORD_EDIT"]}
                >
                  <HomeAppBar noCard>
                    <CsrUpdate />
                  </HomeAppBar>
                </FeatureRoute>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.csr.details}/:csrId/${ROUTES.csr.survey.home}`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <CsrSurvey />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
        <Route
          path={`${ROUTES.extranetUser}/:profileId`}
          element={
            <NavigationWrapper>
              <AuthenticatedRoute>
                <HomeAppBar noCard>
                  <ExtranetUser />
                </HomeAppBar>
              </AuthenticatedRoute>
            </NavigationWrapper>
          }
        />
      </Routes>
    </BrowserRouter>
  );
}

export default RouteMap;

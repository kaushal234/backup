import React from "react";
import "./CsrFilter.css";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import { useDrawer } from "../../hooks/useDrawer";
import CsrSearch from "../../components/CsrSearch/CsrSearch";
import CsrFilterForm from "../../components/CsrFilterForm/CsrFilterForm";

const breadcrumbs: Array<IBreadcrumb> = [
  { title: "breadcrumb.csr.home", link: ROUTES.csr.home },
  { title: "breadcrumb.csr.filters", link: ROUTES.csr.filter },
];

function CsrFilter() {
  useDrawer(ROUTES.csr.home);
  useBreadcrumbs(breadcrumbs);

  return (
    <div className="csr_filter__wrapper">
      <CsrSearch />
      <CsrFilterForm />
    </div>
  );
}

export default CsrFilter;

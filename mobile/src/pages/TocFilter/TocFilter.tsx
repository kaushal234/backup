import React from "react";
import "./TocFilter.css";
import { IBreadcrumb } from "../../@type/IBreadcrumb";
import { useBreadcrumbs } from "../../hooks/useBreadcrumbs";
import { ROUTES } from "../../constants/routes";
import TocFilterForm from "../../components/TocFilterForm/TocFilterForm";
import TocSearch from "../../components/TocSearch/TocSearch";
import { useDrawer } from "../../hooks/useDrawer";

const pageBreadcrumbs: Array<IBreadcrumb> = [
  { title: "drawer.toc.home", link: ROUTES.toc.home },
  { title: "drawer.toc.filter", link: "" },
];

function TocFilter() {
  useDrawer(ROUTES.toc.home);
  useBreadcrumbs(pageBreadcrumbs);

  return (
    <div className="toc_filter__wrapper">
      <TocSearch />
      <TocFilterForm />
    </div>
  );
}

export default TocFilter;

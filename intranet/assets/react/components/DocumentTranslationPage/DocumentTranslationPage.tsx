import React from "react";
import Translator from "bazinga-translator";
import RefreshIcon from "@mui/icons-material/Refresh";
import { IconButton } from "@mui/material";
import DocumentTranslationUploadFile from "../DocumentTranslationUploadFile/DocumentTranslationUploadFile";
import DocumentTranslationList from "../DocumentTranslationList/DocumentTranslationList";
import "./DocumentTranslationPage.css";
import DocumentTranslationInfo from "../DocumentTranslationInfo/DocumentTranslationInfo";
import Initializer from "../../containers/Initializer/Initializer";

export default function DocumentTranslationPage() {
  const [reloadKey, setReloadKey] = React.useState(0);

  return (
    <Initializer>
      <div className="translator">
        <div className="row translator__row">
          <div className="col-lg-6">
            <div className="ibox translator__card translator__card--upload">
              <div className="ibox-title translator__card_title">
                <h5 className="translator__card-heading">
                  {Translator.trans("upload.title")}
                </h5>
                <div className="ibox-tools translator__card_tools">
                  <a
                    className="collapse-link"
                    href="#"
                    role="button"
                    aria-label="Collapse"
                  >
                    <i className="fa fa-chevron-up" />
                  </a>
                </div>
              </div>

              <div className="ibox-content translator__card-content">
                <DocumentTranslationUploadFile
                  onUploaded={() => setReloadKey((k) => k + 1)}
                />
              </div>
            </div>
          </div>

          <div className="col-md-6">
            <DocumentTranslationInfo />
          </div>
        </div>

        <div className="col-lg-12">
          <div className="ibox translator__card translator__card--list">
            <div className="ibox-title translator__card_title translator__list_title">
              <h5 className="translator__card-heading">
                {Translator.trans("upload_list.table.title")}
              </h5>

              <div className="translator__list_right">
                <span className="translator__list_note">
                  {Translator.trans("upload_list.information")}
                </span>
                <span className="label label-danger translator__list-badge">
                  {Translator.trans("upload_list.important")}
                </span>
                <IconButton onClick={() => setReloadKey((k) => k + 1)}>
                  <RefreshIcon />
                </IconButton>
              </div>

              <div className="ibox-tools translator__card-tools">
                <a
                  className="collapse-link"
                  href="#"
                  role="button"
                  aria-label="Collapse"
                >
                  <i className="fa fa-chevron-up" />
                </a>
              </div>
            </div>

            <div className="ibox-content translator__card-content">
              <DocumentTranslationList refreshList={reloadKey} />
            </div>
          </div>
        </div>
      </div>
    </Initializer>
  );
}

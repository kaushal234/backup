import React from "react";
import Translator from "bazinga-translator";
import { TechnicianOnCallFileUploader } from "./TechnicianOnCallFileUploader";

interface IProps {
  data: any;
}

export function TechnicianOnCallFiles({ data }: IProps) {
  return (
    <div className="d-flex justify-content-center gap-4">
      <div className="ibox float-e-margins" style={{ width: "500px" }}>
        <div className="ibox-title">
          <h5>{Translator.trans("toc.files.add_main_file")}</h5>
        </div>
        <div className="ibox-content">
          <TechnicianOnCallFileUploader id={data.id} mainFile />
        </div>
      </div>
      <div className="ibox float-e-margins" style={{ minWidth: "500px" }}>
        <div className="ibox-title">
          <h5>{Translator.trans("toc.files.add_files")}</h5>
        </div>
        <div className="ibox-content">
          <TechnicianOnCallFileUploader id={data.id} />
        </div>
      </div>
    </div>
  );
}

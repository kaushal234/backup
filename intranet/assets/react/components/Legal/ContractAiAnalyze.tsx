import React from "react";
import Translator from "bazinga-translator";
import UppyDropZone from "../../utils/UppyDropZone";

interface IProps {
  actionUrl: string;
}

const MAX_SIZE = 32 * 1024 * 1024; // 32 MB

function ContractAiAnalyze({ actionUrl }: IProps) {
  const onSuccess = (response: any) => {
    if (response?.responseUrl) {
      window.location.href = response.responseUrl;

      return;
    }

    window.location.href = actionUrl.replace(/ai-analyze\/?$/, "add");
  };

  return (
    <div className="row">
      <div className="col-md-6">
        <div className="ibox float-e-margins">
          <div className="ibox-title">
            <h5>{Translator.trans("legal.title.ai_analyze", {}, "legal")}</h5>
          </div>
          <div className="ibox-content">
            <UppyDropZone
              url={actionUrl}
              fieldName="contract_ai_analyze[file]"
              onSuccess={onSuccess}
              maxSize={MAX_SIZE}
              maxFiles={1}
              allowedFileTypes={["application/pdf"]}
            />
          </div>
        </div>
      </div>
    </div>
  );
}

export default ContractAiAnalyze;

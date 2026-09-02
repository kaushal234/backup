import React from "react";
import Translator from "bazinga-translator";
import { useAppSelector } from "../../hooks/hooks";

function TechnicianOnCallAiSearch() {
  const data = useAppSelector((state) => state.tocDetail.data);

  const handleEvent = (eventName: string) => {
    if (data) {
      window.dispatchEvent(
        new CustomEvent(eventName, {
          detail: data,
        })
      );
    }
  };

  return (
    <>
      <button
        className="nav-link m-0 d-flex align-items-center action-primary"
        onClick={() => handleEvent("searchTocWithAi")}
        type="button"
      >
        <span className="ms-1">
          <i className="fa-solid fa-magnifying-glass me-1" />
          {Translator.trans("toc.button.ai_search", null, "technician_on_call")}
        </span>
      </button>
      <button
        className="nav-link m-0 d-flex align-items-center action-primary"
        onClick={() => handleEvent("summaryTocWithAi")}
        type="button"
      >
        <span className="ms-1">
          <i className="fa-solid fa-magnifying-glass me-1" />
          {Translator.trans(
            "toc.button.ai_summary",
            null,
            "technician_on_call"
          )}
        </span>
      </button>
    </>
  );
}

export { TechnicianOnCallAiSearch };

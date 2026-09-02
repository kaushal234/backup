import React, { useState } from "react";
import Translator from "bazinga-translator";
import Accordion from "../../components/Accordion/Accordion";
import TechnicianOnCallStatusModal from "../../components/TechnicianOnCallStatusModal/TechnicianOnCallStatusModal";
import FullScreenLoader from "../../components/FullScreenLoader/FullScreenLoader";
import { useAppSelector } from "../../hooks/hooks";

function TechnicianOnCallStatus() {
  const data = useAppSelector((state) => state.tocDetail.data);
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <>
      <FullScreenLoader />
      <TechnicianOnCallStatusModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        thirdPartyName={data?.thirdPartyName ?? null}
        tocData={data}
      />
      <Accordion title={Translator.trans("toc.status.section.title")}>
        {data && (
          <div className="d-flex flex-row-reverse justify-content-between">
            <div className="text-end">
              <button
                disabled={!data}
                type="button"
                className="btn-primary btn"
                onClick={() => setIsModalOpen(true)}
              >
                {Translator.trans("toc.status.section.update_button")}
              </button>
            </div>
          </div>
        )}
      </Accordion>
    </>
  );
}

export { TechnicianOnCallStatus };

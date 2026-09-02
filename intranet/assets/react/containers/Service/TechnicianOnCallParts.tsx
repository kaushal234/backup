import React, { useEffect, useState } from "react";
import { TechnicianOnCallPartForm } from "../../components/Service/TechnicianOnCallPartForm";
import { TechnicianOnCallPartList } from "../../components/Service/TechnicianOnCallPartList";
import Loader from "../../components/Loader";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { fetchTechnicianOnCallThunk } from "../../thunk/fetchTechnicianOnCall";

interface IProps {
  tocId: any;
}

function TechnicianOnCallParts({ tocId }: IProps) {
  const dispatch = useAppDispatch();
  const technicianOnCall = useAppSelector((state) => state.tocDetail.data);
  const [displayFormPart, setDisplayFormPart] = useState(false);
  const [editableTocPartId, setEditableTocPartId] = useState(null);
  const isLoading = useAppSelector((state) => state.loader.isLoading);

  const hideForm = () => {
    setDisplayFormPart(!displayFormPart);
    setEditableTocPartId(null);
  };

  useEffect(() => {
    dispatch(fetchTechnicianOnCallThunk({ tocId }));
  }, []);

  useEffect(() => {
    if (!technicianOnCall) return;
    const sprPartsCount = technicianOnCall.sparePartsRequests.reduce(
      (acc: number, spr: any) =>
        acc + spr.parts.length + spr.deletedParts.length,
      0
    );
    const total = technicianOnCall.parts.length + sprPartsCount;
    document.dispatchEvent(
      new CustomEvent("tocPartsCountUpdated", { detail: { total } })
    );
  }, [technicianOnCall]);

  return (
    <>
      {isLoading && (
        <div
          style={{
            position: "fixed",
            top: "0",
            left: "0",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            width: "100vw",
            height: "100vh",
            zIndex: "25000",
          }}
        >
          <div
            style={{
              position: "fixed",
              top: "0",
              left: "0",
              width: "100%",
              height: "100%",
              backgroundColor: "rgba(0, 0, 0, 0.5)",
            }}
          />
          <Loader />
        </div>
      )}

      {displayFormPart && (
        <TechnicianOnCallPartForm
          tocId={tocId}
          editableTocPartId={editableTocPartId}
        />
      )}

      <TechnicianOnCallPartList
        technicianOnCall={technicianOnCall}
        setDisplayFormPart={setDisplayFormPart}
        setEditableTocPartId={setEditableTocPartId}
        hideForm={hideForm}
        displayFormPart={displayFormPart}
      />
    </>
  );
}

export { TechnicianOnCallParts };

import React, { useEffect, useState } from "react";
import "./QrCodePlate.css";
import Translator from "bazinga-translator";
import { useParams } from "react-router";
import moment from "moment";
import QrCodePlateDrawing from "../../components/QrCodePlateDrawing/QrCodePlateDrawing";
import QrCodePlateForm, {
  QR_CODE_PLATE_FORM_NAME,
} from "../../components/QrCodePlateForm/QrCodePlateForm";
import { getEquipmentRecordById } from "../../api/getEquipmentRecordById";
import { IEquipmentRecord } from "../../types/IGetEquipmentRecordByIdResponse";
import FullScreenLoader from "../../components/FullScreenLoader/FullScreenLoader";
import { useAppDispatch, useAppSelector } from "../../hooks/hooks";
import { showGlobalLoader } from "../../reducers/loader/loaderSlice";
import { IQrCodePlateFormData } from "../../types/IQrCodePlateFormData";
import { getQrCodeForEquipmentRecord } from "../../api/getQrCodeForEquipmentRecord";
import Accordion from "../Accordion/Accordion";
import { getLocationById } from "../../api/getLocationById";

function QrCodePlate() {
  const { id } = useParams();
  const dispatch = useAppDispatch();
  const [erData, setErData] = useState<IEquipmentRecord | null>(null);
  const [qrDataUrl, setQrDataUrl] = useState<string | null>(null);
  const [mfgLocation, setMfgLocation] = useState<string | null>(null);
  const [isAero, setIsAero] = useState<boolean>(false);
  const [svgString, setSvgString] = useState("");
  const [focusInput, setFocusInput] = useState<string>("");

  const formValues: IQrCodePlateFormData | undefined = useAppSelector(
    (state) => state.form[QR_CODE_PLATE_FORM_NAME]?.values
  );

  const fetchData = async () => {
    dispatch(showGlobalLoader(true));
    const erPromise = getEquipmentRecordById({ id: id ?? "" });
    const qrPromise = getQrCodeForEquipmentRecord({ id: id ?? "" });
    const [erResponse, qrResponse] = await Promise.all([erPromise, qrPromise]);
    const locationIri = erResponse.data?.manufacturerLocation?.["@id"];
    const locationId = locationIri?.replace("/locations/", "");
    const locationResponse = await getLocationById({ id: locationId ?? "" });
    dispatch(showGlobalLoader(false));
    if (erResponse.data) {
      setErData(erResponse.data);
    }
    if (qrResponse.data) {
      setQrDataUrl(qrResponse.data);
    }
    if (locationResponse.data) {
      const location = `${locationResponse.data.name}${
        locationResponse.data.address.city
          ? `, ${locationResponse.data.address.city}`
          : ""
      }${
        locationResponse.data.address.country
          ? `, ${locationResponse.data.address.country}`
          : ""
      }`;
      setMfgLocation(location.toUpperCase());
    }
    if (erResponse.data?.manufacturerLocation?.["@id"] === "/locations/39") {
      setIsAero(true);
      setMfgLocation("A/S, BOISE, US");
    }
  };

  useEffect(() => {
    if (id) {
      fetchData();
    }
  }, [id]);

  if (!erData) return <FullScreenLoader />;

  const unladenKg = formValues?.unladenKg ?? "";
  const unladenLbs = formValues?.unladenLbs ?? "";

  const ratedPowerKw = formValues?.ratedPowerKw ?? "";
  const ratedPowerHp = formValues?.ratedPowerHp ?? "";

  const model = erData.model?.toUpperCase() ?? "";
  const serialNumber = erData.serialNumber.toUpperCase();

  return (
    <>
      <FullScreenLoader />
      <div className="qr_code_plate__wrapper">
        <div className="qr_code_plate__content">
          <div className="qr_code_plate__form">
            <Accordion
              title={Translator.trans("support.qr_code_plate.heading")}
              hideToggle
            >
              <QrCodePlateForm
                svgString={svgString}
                initialValues={{
                  mfgLocation: mfgLocation ?? "",
                  model,
                  serialNumber,
                }}
                onFocusChange={(name) => setFocusInput(name)}
              />
            </Accordion>
          </div>
          <div className="qr_code_plate__drawing">
            <QrCodePlateDrawing
              isAero={isAero}
              hasCELogo={formValues?.logo}
              mfgLocation={mfgLocation ?? ""}
              model={model}
              mfgDate={
                formValues?.mfgDate
                  ? moment(formValues.mfgDate).format("MM/YYYY")
                  : ""
              }
              serialNumber={serialNumber}
              unladenWeightKg={unladenKg}
              unladenWeightLbs={unladenLbs}
              ratedPowerKw={ratedPowerKw}
              ratedPowerHp={ratedPowerHp}
              qrDataUrl={qrDataUrl ?? ""}
              onSvgChange={(value) => setSvgString(value)}
              focusElements={[focusInput]}
            />
          </div>
        </div>
      </div>
    </>
  );
}

export default QrCodePlate;

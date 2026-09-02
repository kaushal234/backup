import React, { useEffect, useState } from "react";
import SvgGenerator from "../SvgGenerator/SvgGenerator";
import {
  SVG_DRAWING,
  AERO_ELEMENTS,
  CE_ELEMENT,
  ELEMENT_ID,
  TLD_ELEMENTS,
} from "../../constants/drawing";
import tldLogo from "../../images/tld_outline_black.png";
import aeroLogo from "../../images/aero_outline_black.png";
import { IGroupElement } from "../SvgGenerator/lib/types/IGroupElement";
import "./QrCodePlateDrawing.css";

interface IProps {
  isAero?: boolean;
  hasCELogo?: boolean;
  mfgLocation?: string;
  model?: string;
  mfgDate?: string;
  serialNumber?: string;
  unladenWeightKg?: string;
  unladenWeightLbs?: string;
  ratedPowerKw?: string;
  ratedPowerHp?: string;
  qrDataUrl?: string;
  onSvgChange?: (value: string) => void;
  focusElements?: Array<string>;
}

function QrCodePlateDrawing(props: IProps) {
  const {
    isAero,
    hasCELogo,
    mfgLocation,
    model,
    mfgDate,
    serialNumber,
    unladenWeightKg,
    unladenWeightLbs,
    ratedPowerKw,
    ratedPowerHp,
    qrDataUrl,
    onSvgChange,
    focusElements,
  } = props;
  const [drawing, setDrawing] = useState<IGroupElement>(SVG_DRAWING);

  const loadData = async () => {
    const newDrawing: IGroupElement = JSON.parse(JSON.stringify(SVG_DRAWING));
    if (hasCELogo) {
      newDrawing.elements.push(CE_ELEMENT);
    }
    if (isAero) {
      newDrawing.elements.push(...AERO_ELEMENTS);
    } else {
      newDrawing.elements.push(...TLD_ELEMENTS);
    }
    for (let i = 0; i < newDrawing.elements.length; i++) {
      const element = newDrawing.elements[i];
      if (element.type === "Image") {
        switch (element.id) {
          case ELEMENT_ID.qrCode: {
            element.base64DataUrl = qrDataUrl;
            break;
          }
          case ELEMENT_ID.logo: {
            element.base64DataUrl = isAero ? aeroLogo : tldLogo;
            break;
          }
          default: {
            break;
          }
        }
      }
      if (element.type === "Field") {
        switch (element.id) {
          case ELEMENT_ID.mfgLocation: {
            element.middleContent = { text: mfgLocation };
            break;
          }
          case ELEMENT_ID.model: {
            element.middleContent = { text: model };
            break;
          }
          case ELEMENT_ID.mfgDate: {
            element.middleContent = { text: mfgDate };
            break;
          }
          case ELEMENT_ID.serialNumber: {
            element.middleContent = { text: serialNumber };
            break;
          }
          default: {
            break;
          }
        }
      }
      if (element.type === "TextInRectangle") {
        switch (element.id) {
          case ELEMENT_ID.unladenWeightKg: {
            element.text = unladenWeightKg ?? "";
            break;
          }
          case ELEMENT_ID.unladenWeightLbs: {
            element.text = unladenWeightLbs ?? "";
            break;
          }
          case ELEMENT_ID.ratedPowerKw: {
            element.text = ratedPowerKw ?? "";
            break;
          }
          case ELEMENT_ID.ratedPowerHp: {
            element.text = ratedPowerHp ?? "";
            break;
          }
          default: {
            break;
          }
        }
      }
    }
    setDrawing(newDrawing);
  };

  useEffect(() => {
    loadData();
  }, [
    mfgLocation,
    model,
    mfgDate,
    serialNumber,
    unladenWeightKg,
    unladenWeightLbs,
    ratedPowerKw,
    ratedPowerHp,
    isAero,
    hasCELogo,
    qrDataUrl,
  ]);

  return (
    <div className="qr_code_plate_drawing__wrapper">
      <SvgGenerator
        drawing={drawing}
        onSvgChange={onSvgChange}
        config={{ focusElements }}
      />
    </div>
  );
}

export default QrCodePlateDrawing;

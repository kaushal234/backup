import React from "react";
import { svg2pdf } from "svg2pdf.js";
import { jsPDF as JsPDF } from "jspdf";
import Translator from "bazinga-translator";
import fontBase64 from "../SvgGenerator/lib/constants/font-ttf.txt";

const svgStringToA4Pdf = async (svgString: string, filename: string) => {
  const parser = new DOMParser();
  const doc = parser.parseFromString(svgString, "image/svg+xml");
  const svgEl = doc.documentElement as unknown as SVGSVGElement;

  const widthCm = parseFloat(
    (svgEl.getAttribute("width") ?? "0cm").replace("cm", "")
  );
  const heightCm = parseFloat(
    (svgEl.getAttribute("height") ?? "0cm").replace("cm", "")
  );

  const widthMm = widthCm * 10;
  const heightMm = heightCm * 10;

  const pdf = new JsPDF({ orientation: "portrait", unit: "mm", format: "a4" });
  const pageW = pdf.internal.pageSize.getWidth();
  const pageH = pdf.internal.pageSize.getHeight();

  const fontName = "arial-condensed-bold";
  pdf.addFileToVFS(`${fontName}.ttf`, fontBase64);
  pdf.addFont(`${fontName}.ttf`, fontName, "normal");
  pdf.addFont(`${fontName}.ttf`, fontName, "bold");

  const x = (pageW - widthMm) / 2;
  const y = (pageH - heightMm) / 2;

  await svg2pdf(svgEl, pdf, {
    x,
    y,
    width: widthMm,
    height: heightMm,
  });

  pdf.save(`${filename}.pdf`);
};

interface IProps {
  svgString: string;
  filename: string;
  isSubmit?: boolean;
  disabled?: boolean;
  allowDownload?: boolean;
}

function SvgDownloader(props: IProps) {
  const {
    svgString,
    filename,
    isSubmit,
    disabled,
    allowDownload = true,
  } = props;

  const handleDownload = async () => {
    if (!svgString || !allowDownload) return;
    setTimeout(async () => {
      await svgStringToA4Pdf(svgString, filename);
    }, 0);
  };

  return (
    <button
      className="btn btn-primary mt-3"
      type={isSubmit ? "submit" : "button"}
      disabled={!svgString || disabled}
      onClick={handleDownload}
    >
      {Translator.trans("support.qr_code_plate.download")}
    </button>
  );
}

export default SvgDownloader;

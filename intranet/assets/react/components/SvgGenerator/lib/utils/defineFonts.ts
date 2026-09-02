import { Svg } from "@svgdotjs/svg.js";
import fontBase64 from "../constants/font-woff2.txt";

interface IDefineFonts {
  draw: Svg;
}

export const defineFonts = (params: IDefineFonts) => {
  const { draw } = params;

  const defs = draw.defs();
  const style = document.createElement("style");
  style.textContent = `
    @font-face {
      font-family: 'arial-condensed-bold';
      src: url("data:font/woff2;base64,${fontBase64}") format("woff2");
      font-weight: bold;
    }`;
  defs.node.appendChild(style);
};

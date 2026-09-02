import React, { useEffect, useRef } from "react";
import { Svg } from "@svgdotjs/svg.js";
import "./SvgGenerator.css";
import { drawSvg } from "./lib/draw/drawSvg";
import { IGroupElement } from "./lib/types/IGroupElement";
import { IDrawConfig } from "./lib/types/IDrawParams";

interface IProps {
  drawing: IGroupElement;
  onSvgChange?: (svgString: string) => void;
  config?: IDrawConfig;
}

function SvgGenerator(props: IProps) {
  const { drawing, onSvgChange, config } = props;

  const containerRef = useRef<HTMLDivElement | null>(null);
  const drawRef = useRef<Svg | null>(null);

  useEffect(() => {
    if (containerRef.current) {
      const draw = drawSvg({
        elementRef: containerRef.current,
        drawing,
        config,
      });
      drawRef.current = draw;
      onSvgChange?.(drawRef.current.svg());
    }

    return () => {
      drawRef.current?.remove();
      drawRef.current = null;
    };
  }, [JSON.stringify(drawing), JSON.stringify(config)]);

  return <div ref={containerRef} className="svg_generator__wrapper" />;
}

export default SvgGenerator;

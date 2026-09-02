import React, { useEffect, useRef, useState } from "react";
import QrScanner from "qr-scanner";
import "./QRScanner.css";
import { Skeleton, Typography } from "@mui/material";
import { useTranslation } from "react-i18next";

const CAMERA_STATES = {
  requested: "requested",
  allowed: "allowed",
  denied: "denied",
} as const;

type ICameraState = keyof typeof CAMERA_STATES;

interface IProps {
  onScan: (value: string) => void;
}

function QRScanner(props: IProps) {
  const { onScan } = props;
  const { t } = useTranslation();
  const videoRef = useRef<HTMLVideoElement>(null);
  const [scannedText, setScannedText] = useState("");
  const [cameraState, setCameraState] = useState<ICameraState>(
    CAMERA_STATES.requested
  );

  const handleScan = (result: QrScanner.ScanResult) => {
    setScannedText(result.data);
  };

  useEffect(() => {
    const video = videoRef.current;
    let qrScanner: QrScanner | undefined;
    if (video) {
      qrScanner = new QrScanner(video, handleScan, {
        preferredCamera: "environment",
        highlightScanRegion: true,
        highlightCodeOutline: true,
      });

      qrScanner
        .start()
        .then(() => {
          setCameraState(CAMERA_STATES.allowed);
        })
        .catch(() => {
          setCameraState(CAMERA_STATES.denied);
        });
    }
    return () => {
      if (qrScanner) {
        qrScanner.stop();
        qrScanner.destroy();
      }
    };
  }, []);

  useEffect(() => {
    if (scannedText) {
      onScan(scannedText);
    }
  }, [scannedText]);

  return (
    <div className="qr_scanner__wrapper">
      <video ref={videoRef} />
      {cameraState === CAMERA_STATES.requested && (
        <div className="qr_scanner__loading_wrapper">
          <Skeleton />
        </div>
      )}
      {cameraState === CAMERA_STATES.denied && (
        <div className="qr_scanner__error_wrapper">
          <Typography variant="body1" gutterBottom>
            {t("common.camera_denied")}
          </Typography>
        </div>
      )}
    </div>
  );
}

export default QRScanner;

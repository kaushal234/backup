import React from "react";
import "./SchematicCard.css";
import { Avatar, IconButton, Paper, Typography } from "@mui/material";
import { useTranslation } from "react-i18next";
import DownloadIcon from "@mui/icons-material/Download";
import { IEquipmentSerial } from "../../@type/IGetAllEquipmentSerialResponse";
import { getSchematicFileById } from "../../api/getSchematicFileById";
import { IEquipmentRecord } from "../../@type/IGetEquipmentRecordResponse";
import { downloadFile } from "../../utils/utils";
import { useAppDispatch } from "../../hooks/hooks";
import { toastError } from "../../utils/api";

interface IProps {
  data: IEquipmentSerial;
  erData: IEquipmentRecord;
  dataCy?: string;
}

function SchematicCard(props: IProps) {
  const { t } = useTranslation();
  const { data, erData, dataCy } = props;
  const dispatch = useAppDispatch();

  const handleDownload = async () => {
    const response = await getSchematicFileById({
      ...(erData.greenTagDate && { date: erData.greenTagDate }),
      site: erData.manufacturerLocation?.erp?.toString() ?? "",
      product: data.serial ?? "",
    });
    if (response.data?.url) {
      downloadFile({
        fileName: `${data.serial}.pdf`,
        url: response.data?.url,
      });
    } else {
      toastError(dispatch, response);
    }
  };

  return (
    <Paper elevation={1} className="schematic_card__wrapper" data-cy={dataCy}>
      <div className="schematic_card__section_wrapper">
        <div className="schematic_card__left_section">
          <div className="schematic_card__first_row">
            <Typography
              variant="button"
              gutterBottom
              className="cui_one_line"
              data-cy={`${dataCy}-value`}
            >
              {data.component?.name?.toString() ?? "---"}
            </Typography>
          </div>
          <div className="schematic_card__middle_row">
            <div>
              <Typography
                variant="body1"
                gutterBottom
                data-cy={`${dataCy}-value`}
              >
                {data.model ?? "---"}
              </Typography>
            </div>
          </div>
          <div className="schematic_card__details">
            <Typography
              variant="caption"
              gutterBottom
              data-cy={`${dataCy}-value`}
            >
              {data.serial ?? "---"}
            </Typography>
            <Typography
              variant="caption"
              gutterBottom
              data-cy={`${dataCy}-value`}
            >
              {`${t("er_details.schematics.brand")} : ${data.brand ?? "---"}`}
            </Typography>
          </div>
        </div>
        <div className="schematic_card__right_section">
          <Avatar className="schematic_card__add_file" color="primary">
            <IconButton onClick={handleDownload} data-cy={`${dataCy}-download`}>
              <DownloadIcon />
            </IconButton>
          </Avatar>
        </div>
      </div>
    </Paper>
  );
}

export default SchematicCard;

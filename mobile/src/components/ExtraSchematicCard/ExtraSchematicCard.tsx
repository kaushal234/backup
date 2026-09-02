import React from "react";
import "./ExtraSchematicCard.css";
import { Avatar, IconButton, Paper, Typography } from "@mui/material";
import DownloadIcon from "@mui/icons-material/Download";
import { ICustomizedBillOfMaterialsItem } from "../../@type/IGetAllCustomizedBillOfMaterialResponse";
import { getSchematicFileById } from "../../api/getSchematicFileById";
import { downloadFile } from "../../utils/utils";
import { IEquipmentRecord } from "../../@type/IGetEquipmentRecordResponse";
import { useAppDispatch } from "../../hooks/hooks";
import { toastError } from "../../utils/api";

interface IProps {
  data: ICustomizedBillOfMaterialsItem;
  erData: IEquipmentRecord;
  dataCy?: string;
}

function ExtraSchematicCard(props: IProps) {
  const { data, erData, dataCy } = props;
  const dispatch = useAppDispatch();

  const handleDownload = async () => {
    const response = await getSchematicFileById({
      ...(erData.greenTagDate && { date: erData.greenTagDate }),
      site: erData.manufacturerLocation?.erp?.toString() ?? "",
      product: data.partNumber,
    });
    if (response.data?.url) {
      downloadFile({
        fileName: `${data.partNumber}.pdf`,
        url: response.data?.url,
      });
    } else {
      toastError(dispatch, response);
    }
  };

  return (
    <Paper
      elevation={1}
      className="extra_schematic_card__wrapper"
      data-cy={dataCy}
    >
      <div className="extra_schematic_card__section_wrapper">
        <div className="extra_schematic_card__left_section">
          <div className="extra_schematic_card__first_row">
            <Typography
              variant="button"
              gutterBottom
              className="cui_one_line_regular"
              data-cy={`${dataCy}-value`}
            >
              {data.itemDescription?.toString() ?? "---"}
            </Typography>
          </div>
          <div className="extra_schematic_card__details">
            <Typography
              variant="caption"
              gutterBottom
              data-cy={`${dataCy}-value`}
            >
              {data.partNumber ?? "---"}
            </Typography>
          </div>
        </div>
        <div className="extra_schematic_card__right_section">
          <Avatar className="extra_schematic_card__add_file" color="primary">
            <IconButton onClick={handleDownload} data-cy={`${dataCy}-download`}>
              <DownloadIcon />
            </IconButton>
          </Avatar>
        </div>
      </div>
    </Paper>
  );
}

export default ExtraSchematicCard;

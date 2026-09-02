import React, { useEffect, useState } from "react";
import "./ErDetailSchematics.css";
import { useTranslation } from "react-i18next";
import { Typography } from "@mui/material";
import { IEquipmentRecord } from "../../@type/IGetEquipmentRecordResponse";
import SchematicCard from "../SchematicCard/SchematicCard";
import { getAllEquipmentSerial } from "../../api/getAllEquipmentSerial";
import { IEquipmentSerial } from "../../@type/IGetAllEquipmentSerialResponse";
import { getAllCustomizedBillOfMaterial } from "../../api/getAllCustomizedBillOfMaterial";
import { ICustomizedBillOfMaterialsItem } from "../../@type/IGetAllCustomizedBillOfMaterialResponse";
import { useAppDispatch } from "../../hooks/hooks";
import { showMainLoader } from "../../redux/slices/loaderSlice";
import ExtraSchematicCard from "../ExtraSchematicCard/ExtraSchematicCard";
import { toastError } from "../../utils/api";

interface IProps {
  data: IEquipmentRecord;
}

export default function ErDetailSchematics(props: IProps) {
  const { data } = props;
  const { t } = useTranslation();
  const dispatch = useAppDispatch();
  const [schematics, setSchematics] = useState<Array<IEquipmentSerial>>([]);
  const [extraSchematics, setExtraSchematics] = useState<
    Array<ICustomizedBillOfMaterialsItem>
  >([]);

  const fetchSchematics = async () => {
    dispatch(showMainLoader(true));
    const schematicsPromise = getAllEquipmentSerial({
      serialNumber: data.serialNumber,
    });
    const extraSchematicsPromise = getAllCustomizedBillOfMaterial({
      site: data.manufacturerLocation?.erp?.toString() ?? "",
      project: data.projectNumber ?? "",
    });
    const responses = await Promise.all([
      schematicsPromise,
      extraSchematicsPromise,
    ]);
    dispatch(showMainLoader(false));
    if (responses[0].data) {
      setSchematics(responses[0].data["hydra:member"]);
    } else {
      toastError(dispatch, responses[0]);
    }
    if (responses[1].data) {
      setExtraSchematics(responses[1].data.items);
    } else {
      toastError(dispatch, responses[1]);
    }
  };

  useEffect(() => {
    fetchSchematics();
  }, []);

  return (
    <div className="er_detail_schematics__wrapper">
      {!schematics.length && !extraSchematics.length && (
        <div
          className="er_detail_schematics__no_list"
          data-cy="er-schematics-no-content"
        >
          {t("er_details.schematics.no_files")}
        </div>
      )}
      {!!schematics.length && (
        <div className="er_detail_schematics_list_wrapper">
          <Typography variant="h5" data-cy="er-schematics-heading">
            {t("er_details.schematics.schematic_title")}
          </Typography>
          {schematics.map((schematic, idx) => (
            <SchematicCard
              key={schematic.id}
              data={schematic}
              erData={data}
              dataCy={`er-schematics-card-${idx}`}
            />
          ))}
        </div>
      )}
      {!!extraSchematics.length && (
        <div className="er_detail_schematics_list_wrapper">
          <Typography variant="h5" data-cy="er-extra-schematics-heading">
            {t("er_details.schematics.extra_schematic_title")}
          </Typography>
          {extraSchematics.map((extraSchematic, idx) => (
            <ExtraSchematicCard
              key={extraSchematic["@id"]}
              data={extraSchematic}
              erData={data}
              dataCy={`er-extra-schematics-card-${idx}`}
            />
          ))}
        </div>
      )}
    </div>
  );
}

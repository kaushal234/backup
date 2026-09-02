import React from "react";
import { Badge, OverlayTrigger, Tooltip } from "react-bootstrap";
import Translator from "bazinga-translator";

interface IProps {
  item: any;
}

export function ActivityItemTechnicianOnCallsBadges({ item }: IProps) {
  const confidentialityChange = item.metadata?.confidential ?? null;

  return (
    <>
      {confidentialityChange !== null &&
        (confidentialityChange ? (
          <OverlayTrigger
            placement="top"
            overlay={
              <Tooltip>
                {Translator.trans("toc.fields.confidential.tooltip")}
              </Tooltip>
            }
          >
            <Badge bg="warning" text="secondary">
              {Translator.trans("toc.badge.confidential_open")}
            </Badge>
          </OverlayTrigger>
        ) : (
          <Badge bg="success" text="light">
            {Translator.trans("toc.badge.confidential_close")}
          </Badge>
        ))}
      {item.metadata?.factoryFlag === "OPEN_FACTORY_FLAG" && (
        <Badge bg="danger" text="light">
          {Translator.trans("toc.badge.factory_flag_open")}
        </Badge>
      )}
      {item.metadata?.factoryFlag === "CLOSE_FACTORY_FLAG" && (
        <Badge bg="success" text="light">
          {Translator.trans("toc.badge.factory_flag_close")}
        </Badge>
      )}
      {item.user?.["@type"] === "ExtranetUser" && (
        <Badge bg="warning" text="dark">
          Cust
        </Badge>
      )}
      {item.public && item.discriminator === "TOC" && (
        <OverlayTrigger
          placement="top"
          overlay={<Tooltip>{Translator.trans("toc.badge.public")}</Tooltip>}
        >
          <Badge bg="info" text="light">
            <i className="fa fa-eye" />
          </Badge>
        </OverlayTrigger>
      )}
      <Badge
        bg="secondary"
        text="light"
        className="activity_item_technician_on_calls__module_badge"
      >
        {item.discriminator}
      </Badge>
    </>
  );
}

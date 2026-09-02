import React from "react";
import moment from "moment/moment";

interface IPartUser {
  firstname: string;
  lastname: string;
}

interface ITracking {
  trackingNumber: string | null;
  carrier: string | null;
  shippedQuantity: number | null;
  unitOfMeasure: string | null;
  trackingLink: string | null;
}

interface IPart {
  id: number | string;
  partNumber: string;
  description: string;
  quantity: number;
  createdAt: string | Date;
  createdBy: IPartUser;
  comment?: string;
  trackings?: Array<ITracking>;
}

interface IProps {
  part: IPart;
  sparePartsRequestId: number;
  sparePartsRequestStatus: string;
}

function TechnicianOnCallPartLine({
  part,
  sparePartsRequestId,
  sparePartsRequestStatus,
}: IProps) {
  const trackings = (part.trackings ?? []).map((tracking) => {
    const { trackingNumber } = tracking;
    let trackingLink: string | null | JSX.Element = trackingNumber;

    if (tracking.trackingLink !== null) {
      trackingLink = (
        <a href={tracking.trackingLink} target="_blank" rel="noreferrer">
          {tracking.trackingNumber}
        </a>
      );
    }

    return (
      <span>
        {trackingLink} - {tracking.carrier} ({tracking.shippedQuantity}{" "}
        {tracking.unitOfMeasure})
      </span>
    );
  });

  const result = trackings.reduce<React.ReactNode[]>(
    (accumulator, element, i) => {
      if (i === 0) {
        return [element];
      }

      return [...accumulator, ", ", element];
    },
    []
  );

  return (
    <tr key={part.id}>
      <td>{part.partNumber}</td>
      <td>{part.description}</td>
      <td>{part.quantity}</td>
      <td>{moment(part.createdAt).format("YYYY-MM-DD")}</td>
      <td>
        {part.createdBy.firstname} {part.createdBy.lastname}
      </td>
      <td>
        <a
          href={`/en/private/parts/spare-parts-requests/${sparePartsRequestId}/show`}
        >
          #{sparePartsRequestId}
        </a>
      </td>
      <td>{sparePartsRequestStatus}</td>
      <td>{result}</td>
      <td>{part.comment}</td>
    </tr>
  );
}

export { TechnicianOnCallPartLine };

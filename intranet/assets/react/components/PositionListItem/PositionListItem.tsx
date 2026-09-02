import React from "react";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import "./PositionListItem.css";

interface IProps {
  label: string;
}

function PositionListItem(props: IProps) {
  const { label } = props;
  const { attributes, listeners, setNodeRef, transform, transition } =
    useSortable({ id: label });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  return (
    <div
      className="position_list_item"
      ref={setNodeRef}
      style={style}
      {...attributes}
      {...listeners}
    >
      {label}
    </div>
  );
}

export default PositionListItem;

import React, { useEffect, useState } from "react";
import {
  DndContext,
  closestCenter,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
  DragEndEvent,
} from "@dnd-kit/core";
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import PositionListItem from "../PositionListItem/PositionListItem";

interface IProps {
  list: Array<string>;
  onChange: (list: Array<string>) => void;
}

function PositionList(props: IProps) {
  const { list, onChange } = props;

  const [items, setItems] = useState(list);

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;

    if (over && active.id !== over.id) {
      setItems((prevItems) => {
        const oldIndex = prevItems.indexOf(`${active.id}`);
        const newIndex = prevItems.indexOf(`${over.id}`);

        return arrayMove(prevItems, oldIndex, newIndex);
      });
    }
  };

  useEffect(() => {
    setItems(list);
  }, [JSON.stringify(list)]);

  useEffect(() => {
    onChange(items);
  }, [JSON.stringify(items)]);

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragEnd={handleDragEnd}
    >
      <SortableContext items={items} strategy={verticalListSortingStrategy}>
        {items.map((item) => (
          <PositionListItem key={item} label={`${item}`} />
        ))}
      </SortableContext>
    </DndContext>
  );
}

export default PositionList;

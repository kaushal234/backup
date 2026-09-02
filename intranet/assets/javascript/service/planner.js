import { Sortable, MultiDrag } from 'sortablejs';
import {CanvasDragImage} from './CanvasDragImage';
import clickOnDraggableOverEvent from './clickOnDraggableOverEvent';
import {InterventionPersister} from "./InterventionPersister";
import map from './map';

Sortable.mount(new MultiDrag());
const canvasDragImage = new CanvasDragImage();
const interventionPersister = new InterventionPersister()

const plannerConfig= {
    multiDrag: true,
    scroll: true,
    avoidImplicitDeselect: true,
    selectedClass: 'sortable-selected',
    group: 'shared-csr',
    animation: 0,
    setData: function (/** DataTransfer */dataTransfer, /** HTMLElement*/dragEl) {
        dataTransfer.setDragImage(canvasDragImage.create('sortable-selected', 'CSR'), 0, 0)
    },
    onEnd: function (event) {
        for (let item of event.items) {
            Sortable.utils.deselect(item);
        }

        const newType = event.item.closest('.planner-container') ? event.item.closest('.planner-container').dataset.week : null
        if (newType !== event.from.dataset.week) {
            const items = 0 === event.items.length ? [event.item] : event.items
            const errorsPromises = interventionPersister.putCollection(items)
            for (let errorsPromise of errorsPromises) {
                errorsPromise.then((item) => {
                    if (!item) {
                        return null
                    }

                    let fragment = document.createDocumentFragment();
                    fragment.appendChild(item);
                    event.from.prepend(fragment);
                })
            }
        }

        canvasDragImage.remove()
    }
}

// Create draggable containers
let plannerContainers = document.getElementsByClassName('planner-container');
for (let plannerContainer of plannerContainers) {
    Sortable.create(plannerContainer, plannerConfig);
}

// Collapsed ibox for current week on mouse over
let currentWeekBoxes = document.getElementsByClassName('planner-current-week-box')
clickOnDraggableOverEvent(currentWeekBoxes, 'collapse-link')

// Select user on tab when mouse over
let subordinatesList = document.getElementById('planner-subordinates')
if (subordinatesList) {
    let buttons = subordinatesList.getElementsByTagName("button");
    clickOnDraggableOverEvent(buttons)
}

map();
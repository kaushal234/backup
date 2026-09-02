let timeoutDraggable = undefined;
export default function clickOnDraggableOverEvent(collection, classNameForClick = null) {
    for (let item of collection)  {
        item.addEventListener('dragover', (event) => {
            event.preventDefault()
            if (undefined === timeoutDraggable) {
                timeoutDraggable = setTimeout(function () {
                    if (null !== classNameForClick) {
                        const clickableElement = item.getElementsByClassName(classNameForClick)[0]
                        if (clickableElement) {
                            clickableElement.click()
                        }
                    } else {
                        item.click();
                    }
                }, 500)
            }
        });

        item.parentNode.parentNode.addEventListener('dragleave', event => {
            event.preventDefault()
            clearTimeout(timeoutDraggable)
            timeoutDraggable = undefined
        });
    }
}
export class CanvasDragImage {
    dragImageClass = 'drag-image'

    create(elementClass, initialText) {
        let numberSelectedElements = document.getElementsByClassName(elementClass).length
        const text = (0 === numberSelectedElements ? 1 : numberSelectedElements) + ' ' + initialText;
        const canvas = document.createElement('canvas');
        canvas.classList.add(this.dragImageClass)
        const canvasContext = canvas.getContext("2d");
        canvasContext.font = "15px Open Sans, Helvetica Neue, Helvetica, Arial, sans-serif";
        const width = canvasContext.measureText(text).width;
        canvasContext.fillStyle = "#2F4050";
        canvasContext.fillRect(15, 5, width + 10, 20);
        canvasContext.fillStyle = "#a7b1c2";
        canvasContext.fillText(text, 20,20);

        document.body.append(canvas);

        return canvas
    }

    remove() {
        let canvasDragImages = document.getElementsByClassName(this.dragImageClass)

        for (let canvasDragImage of canvasDragImages) {
            canvasDragImage.remove()
        }
    }
}
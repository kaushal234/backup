export class PlannerNotification {
    notificationContainer

    constructor() {
        const notificationContainer = document.getElementById('planner-notification')

        if (notificationContainer) {
            this.notificationContainer = notificationContainer
        } else {
            const container = document.body
            const notificationContainer = document.createElement("div");

            notificationContainer.id = 'planner-notification'
            this.notificationContainer = notificationContainer

            container.append(notificationContainer)
        }
    }

    createCard(type, text) {
        const card = `
            <div class="alert alert-${type} alert-dismissible fade show rounded-0" role="alert">
                ${text}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const parser = new DOMParser();

        // Parse the element string
        const doc = parser.parseFromString(card, 'text/html');
        const element = doc.body.firstChild;
        this.notificationContainer.prepend(element);

        setTimeout(function() {
            element.remove()
        }, 5000);
    }
}
import axios from "axios";
import {PlannerNotification} from "./PlannerNotification";

export class InterventionPersister {
    client
    plannerNotification
    errors = []

    constructor() {
        this.plannerNotification = new PlannerNotification()

        const jwt = window.user && window.user.hasOwnProperty('token') ? window.user.token : null
        const locale = $('html').attr('lang')
        
        this.client = axios.create({
            baseURL: process.env.WEBPACK_API_URI,
            headers: {
                'Accept': 'application/ld+json',
                'Content-type': 'application/ld+json',
                'Authorization': `Bearer ${jwt}`,
                'accept-language': locale
            }
        })
    }

    putCollection(items) {
        for (let item of items) {
            const error = this.put(item)

            if (null !== error) {
                this.errors.push(error)
            }
        }

        return this.errors
    }

    async put(item) {
        const data = this.getData(item)
        try {
            return this.client.put(data.customerServiceRecord, data)
                .then((response) => {
                    // let name = 'to ' + response.data.leader.firstname + ' ' + response.data.leader.lastname
                    let name = ' '
                    this.plannerNotification.createCard('success', `CSR ${data.customerServiceRecordId} successfully added ${name} for week TO DEFINE`)
                    return null;
                })
                .catch((error) => {
                    let errorMessage = error.message;
                    if (error.response?.data?.violations) {
                        errorMessage = error.response.data.violations
                            .map(v => `<br> ${v.message}`)
                            .join(',');
                    }

                    this.plannerNotification.createCard('danger', `Error when CSR ${data.customerServiceRecordId} has been posted : ` + errorMessage)
                    return item
                })
        } catch (error) {
            this.plannerNotification.createCard('danger', `Error when CSR ${data.customerServiceRecordId} has been posted :` + error.message)
            return item
        }
    }
    
    getData(item) {
        const week = item.closest('.planner-container') ? item.closest('.planner-container').dataset.week : null
        const user = item.closest('.tab-pane') ? item.closest('.tab-pane').dataset.userIri : null
        const csrCard = item.getElementsByClassName('csr-card-planner')[0]
        const customerServiceRecordIri = csrCard.dataset.iri
        const customerServiceRecordId = csrCard.dataset.id
        let today = new Date();
        let date = null

        switch (week) {
            case 'current':
                date = today
                break;
            case 'next':
                date = today
                date.setDate(date.getDate() + 7)
                break;
            case 'in-two':
                date = today
                date.setDate(date.getDate() + 7 * 2)
                break;
            case 'in-three':
                date = today
                date.setDate(date.getDate() + 7 * 3)
                break;
            case 'in-four':
                date = today
                date.setDate(date.getDate() + 7 * 4)
                break;
        }

        return {
            plannedAt: date,
            leader: user,
            customerServiceRecord: customerServiceRecordIri,
            customerServiceRecordId: customerServiceRecordId
        }
    }
}
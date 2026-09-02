import { Controller } from "@hotwired/stimulus";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(relativeTime);
interface NotificationModule {
  name: string;
  notificationColor: string;
}

interface NotificationTemplate {
  module: NotificationModule;
}

interface Notification {
  id: number;
  unread: boolean;
  createdAt: string;
  textDisplayed: string;
  url: string;
  template: NotificationTemplate;
}

declare const Routing: {
  generate(route: string, params?: Record<string, unknown>): string;
};

export default class NotificationController extends Controller {
  static targets = ["list", "badge", "count"];

  declare readonly listTarget: HTMLElement;
  declare readonly badgeTarget: HTMLElement;
  declare readonly countTarget: HTMLElement;

  connect(): void {
    this.loadNotifications();
  }

  async loadNotifications(): Promise<void> {
    try {
      const response = await fetch(Routing.generate("notifications_ajax"));
      const notifications: Notification[] = await response.json();

      let unread = 0;

      notifications.forEach((notification) => {
        if (notification.unread) unread++;
        this.appendNotification(notification);
      });

      if (unread > 0) {
        this.countTarget.textContent = String(unread);
      }
    } catch (e) {
      console.error("Erreur chargement notifications", e);
    }
  }

  appendNotification(notification: Notification): void {
    const createdAt = dayjs(notification.createdAt).fromNow();
    const li = document.createElement("li");
    li.id = `notification-line-${notification.id}`;
    li.classList.add("notification-line");
    if (notification.unread) li.classList.add("unread");

    li.innerHTML = `
            <a data-id="${notification.id}"
               data-action="click->notification#read"
               href="${notification.url}"
               style="text-decoration:none; color:inherit;"
            >
                ${notification.textDisplayed} - <small class="fw-lighter fst-italic">${createdAt}</small>
            </a>
        `;

    li.insertAdjacentHTML(
      "beforeend",
      `
            <span class="badge badge-pill" style="background-color: ${notification.template.module.notificationColor}; color: white">
                ${notification.template.module.name}
            </span>
            &nbsp;<a style="cursor:pointer"
                     data-action="click->notification#remove"
                     data-id="${notification.id}"
                     data-unread="${notification.unread}"
            ><i class="fa fa-times"></i></a>
        `
    );

    this.listTarget.appendChild(li);
  }

  read(event: Event): void {
    const id = (event.currentTarget as HTMLElement).getAttribute("data-id");
    fetch(Routing.generate("read_notifications_ajax", { id }));
  }

  remove(event: Event): void {
    event.stopPropagation();
    event.preventDefault();

    const target = event.currentTarget as HTMLElement;
    const id = target.getAttribute("data-id");
    const unread = target.getAttribute("data-unread");

    fetch(Routing.generate("remove_notifications_ajax", { id }));

    target.closest(".notification-line")?.remove();

    if (unread === "true") {
      const current = parseInt(this.countTarget.textContent ?? "0", 10) - 1;
      if (current <= 0) {
        this.countTarget.textContent = "";
        this.badgeTarget.classList.add("d-none");
      } else {
        this.countTarget.textContent = String(current);
      }
    }
  }

  readAll(event: Event): void {
    event.stopPropagation();
    event.preventDefault();

    fetch(Routing.generate("read_all_notifications_ajax"));

    this.listTarget
      .querySelectorAll(".notification-line")
      .forEach((el) => el.classList.remove("unread"));

    this.countTarget.textContent = "";
    this.badgeTarget.classList.add("d-none");
  }

  clearAll(event: Event): void {
    event.stopPropagation();
    event.preventDefault();

    fetch(Routing.generate("delete_all_notifications_ajax"));

    this.listTarget
      .querySelectorAll(".notification-line")
      .forEach((el) => el.remove());

    this.countTarget.textContent = "";
    this.badgeTarget.classList.add("d-none");
  }
}

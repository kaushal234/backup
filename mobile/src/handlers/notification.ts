export const handleNotification = (
  event: NotificationEvent,
  self: ServiceWorkerGlobalScope
) => {
  event.notification.close();
  event.waitUntil(self.clients.openWindow("/"));
};

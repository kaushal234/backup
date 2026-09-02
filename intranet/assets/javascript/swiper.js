import Swiper from "swiper";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import { Navigation, Pagination, Zoom } from "swiper/modules";

$(document).ready(function () {
  const swiperEl = document.querySelector(".swiper");

  const swiper = new Swiper(swiperEl, {
    direction: "horizontal",
    loop: true,
    zoom: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
      renderBullet(index, className) {
        return `<span class="${className}">${index + 1}</span>`;
      },
    },
    modules: [Navigation, Pagination, Zoom],
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    scrollbar: {
      el: ".swiper-scrollbar",
    },
  });

  const fullscreenBtn = swiperEl?.querySelector(".swiper-fullscreen-btn");

  fullscreenBtn?.addEventListener("click", () => {
    swiperEl.classList.toggle("fullscreen");

    if (!document.fullscreenElement) {
      swiperEl.requestFullscreen().catch((err) => {
        console.warn("Impossible to activate full screen :", err);
      });
    } else {
      document.exitFullscreen();
    }

    setTimeout(() => swiper.update(), 300);
  });

  document.addEventListener("fullscreenchange", () => {
    if (!document.fullscreenElement) {
      swiperEl.classList.remove("fullscreen");
      swiper.update();
    }
  });
});

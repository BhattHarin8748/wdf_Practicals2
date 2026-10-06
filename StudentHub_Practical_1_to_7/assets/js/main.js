document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;
  const navWrap = document.querySelector(".nav-wrap");

  if (navWrap) {
    const themeButton = document.createElement("button");
    themeButton.className = "theme-toggle";
    themeButton.type = "button";
    themeButton.setAttribute("aria-label", "Switch to dark theme");
    themeButton.textContent = "Dark mode";
    navWrap.appendChild(themeButton);
    if (localStorage.getItem("studenthub-theme") === "dark") body.classList.add("dark-theme");
    const updateThemeButton = () => {
      const dark = body.classList.contains("dark-theme");
      themeButton.textContent = dark ? "Light mode" : "Dark mode";
      themeButton.setAttribute("aria-label", dark ? "Switch to light theme" : "Switch to dark theme");
    };
    updateThemeButton();
    themeButton.addEventListener("click", () => {
      body.classList.toggle("dark-theme");
      localStorage.setItem("studenthub-theme", body.classList.contains("dark-theme") ? "dark" : "light");
      updateThemeButton();
    });
  }

  const notice = document.createElement("div");
  notice.className = "notification-banner";
  notice.setAttribute("role", "status");
  notice.innerHTML = '<span>StudentHub events are open for registration.</span><button type="button" aria-label="Dismiss notification">×</button>';
  body.prepend(notice);
  notice.querySelector("button").addEventListener("click", () => notice.remove());

  const menuBtn = document.querySelector(".menu-btn");
  const nav = document.querySelector("#mainNav");
  if (menuBtn && nav) menuBtn.addEventListener("click", () => nav.classList.toggle("show"));

  document.querySelectorAll(".faq-question").forEach(btn => {
    const answer = btn.nextElementSibling;
    btn.setAttribute("aria-expanded", "false");
    btn.addEventListener("click", () => {
      btn.classList.toggle("open");
      btn.setAttribute("aria-expanded", btn.classList.contains("open"));
      if (answer) answer.hidden = !btn.classList.contains("open");
      const icon = btn.querySelector("span");
      if (icon) icon.textContent = btn.classList.contains("open") ? "−" : "+";
    });
  });

  const modal = document.querySelector("#eventModal");
  if (modal) {
    const modalStorageKey = "studenthub-event-modal-state";
    modal.hidden = true;
    modal.style.display = "none";
    const openModal = () => {
      modal.style.display = "grid";
      modal.hidden = false;
      body.classList.add("modal-open");
      sessionStorage.setItem(modalStorageKey, "open");
      modal.querySelector(".modal-close").focus();
    };
    const closeModal = () => {
      modal.hidden = true;
      modal.style.display = "none";
      body.classList.remove("modal-open");
      sessionStorage.setItem(modalStorageKey, "closed");
    };
    if (sessionStorage.getItem(modalStorageKey) !== "closed") openModal();
    document.querySelectorAll(".details-btn").forEach(btn => btn.addEventListener("click", () => {
      openModal();
    }));
    modal.querySelector(".modal-close").addEventListener("click", closeModal);
    modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
    document.addEventListener("keydown", e => { if (e.key === "Escape" && !modal.hidden) closeModal(); });
  }

  const slider = document.querySelector(".content-slider");
  if (slider && slider.querySelector(".slider-prev") && slider.querySelector(".slider-next") && slider.querySelector(".slider-status")) {
    const slides = [...slider.querySelectorAll(".slide")];
    let current = 0;
    const showSlide = index => {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => slide.toggleAttribute("hidden", i !== current));
      slider.querySelector(".slider-status").textContent = `${current + 1} / ${slides.length}`;
    };
    slider.querySelector(".slider-prev").addEventListener("click", () => showSlide(current - 1));
    slider.querySelector(".slider-next").addEventListener("click", () => showSlide(current + 1));
    showSlide(0);
  }

  const loginForm = document.querySelector("#loginForm");
  if (loginForm) loginForm.addEventListener("submit", e => {
    e.preventDefault();
    const msg = document.querySelector("#loginMessage");
    msg.hidden = false;
    msg.textContent = "Demo login successful. Opening dashboard...";
    setTimeout(() => location.href = "dashboard.html", 700);
  });

  document.querySelectorAll("form:not(#registerForm):not(#loginForm)").forEach(form => {
    form.addEventListener("submit", e => {
      e.preventDefault();
      alert("Thank you! This frontend demo has received your submission.");
    });
  });
});
const sidebarToggle = document.querySelector("#sidebar-toggle");
const sidebar = document.querySelector("#sidebar");
const sidebarOverlay = document.getElementById("sidebar-overlay");
const wrapper = document.querySelector(".wrapper");

sidebarToggle.addEventListener("click", () => {
    if (window.innerWidth <= 768) {
        // Small screen: toggle floating sidebar and overlay
        sidebar.classList.toggle("show");
        sidebarOverlay.classList.toggle("show");
    } else {
        // Large screen: toggle collapsed class on wrapper
        wrapper.classList.toggle("sidebar-collapsed");
    }
});

// Clicking overlay hides sidebar on small screen
sidebarOverlay.addEventListener("click", () => {
    sidebar.classList.remove("show");
    sidebarOverlay.classList.remove("show");
});

const sidebarCloseBtn = document.getElementById("sidebar-close");

if (sidebarCloseBtn) {
  sidebarCloseBtn.addEventListener("click", () => {
    sidebar.classList.remove("show");
    sidebarOverlay.classList.remove("show");
  });
}

// Theme Toggle
const themeToggle = document.querySelector(".theme-toggle");
if (themeToggle) {
    themeToggle.addEventListener("click", () => {
        const currentTheme = document.documentElement.getAttribute("data-bs-theme") || "light";
        const newTheme = currentTheme === "dark" ? "light" : "dark";
        document.documentElement.setAttribute("data-bs-theme", newTheme);
        localStorage.setItem("theme", newTheme);
    });
}

// Initialize theme from localStorage
const savedTheme = localStorage.getItem("theme");
if (savedTheme) {
    document.documentElement.setAttribute("data-bs-theme", savedTheme);
}

// Footer year updater
function updateYear(elementId) {
    const year = new Date().getFullYear();
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = `© ${year}`;
    }
}

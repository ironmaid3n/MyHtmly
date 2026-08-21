/*!
 * Editorial theme — main.js
 * Small, dependency-free progressive enhancement.
 * The page is fully usable and correctly styled without this file;
 * it only adds the dark/light toggle persistence, the back-to-top
 * button visibility, and closing the mobile menu after a tap.
 */
(function () {
  "use strict";

  /* ---- Dark / light toggle -------------------------------------- */
  var root = document.documentElement;
  var toggle = document.getElementById("theme-toggle");

  function currentTheme() {
    return root.getAttribute("data-theme") ||
      (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
  }

  if (toggle) {
    toggle.addEventListener("click", function () {
      var next = currentTheme() === "dark" ? "light" : "dark";
      root.setAttribute("data-theme", next);
      try { localStorage.setItem("editorial-theme", next); } catch (e) {}
      toggle.setAttribute("aria-pressed", next === "dark" ? "true" : "false");
    });
  }

  /* ---- Back to top ------------------------------------------------ */
  var toTop = document.querySelector(".to-top");
  if (toTop) {
    var onScroll = function () {
      if (window.scrollY > 640) {
        toTop.classList.add("visible");
      } else {
        toTop.classList.remove("visible");
      }
    };
    document.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  /* ---- Close mobile nav after choosing a link --------------------- */
  var navToggle = document.getElementById("nav-toggle");
  if (navToggle) {
    document.querySelectorAll(".main-nav a").forEach(function (link) {
      link.addEventListener("click", function () { navToggle.checked = false; });
    });
  }
})();

function toggleStep(form) {
    const button = form.querySelector("button");

    if (button === null || button.disabled) {
        return;
    }

    const card = button.closest(".step-card");
    const completedInput = form.querySelector('input[name="completed"]');

    if (card === null || completedInput === null) {
        return;
    }

    const wasCompleted = card.classList.contains("completed");
    const completed = wasCompleted ? 0 : 1;

    card.classList.toggle("completed", completed === 1);
    button.textContent = completed === 1 ? "Mark as Incomplete" : "Mark as Completed";
    button.disabled = true;

    fetch(form.action, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "Accept": "application/json",
        },
        body: new URLSearchParams(new FormData(form)),
    }).then((response) => {
        return response.json().catch(() => null).then((payload) => ({ response, payload }));
    }).then(({ response, payload }) => {
        if (!response.ok || payload === null || payload.ok !== true) {
            throw new Error("Progress update failed.");
        }

        completedInput.value = completed === 1 ? "0" : "1";
        updateProgress();
    }).catch(() => {
        card.classList.toggle("completed", wasCompleted);
        button.textContent = wasCompleted ? "Mark as Incomplete" : "Mark as Completed";
        updateProgress();
    }).finally(() => {
        button.disabled = false;
    });
}

function updateProgress() {
    const steps = document.querySelectorAll(".step-card");
    const completed = document.querySelectorAll(".step-card.completed");

    const progress =
        steps.length === 0 ? 0 : Math.round((completed.length / steps.length) * 100);

    const progressText = document.getElementById("progressText");
    const progressFill = document.getElementById("progressFill");
    const message = document.getElementById("completedMessage");

    if (progressText && progressFill) {
        progressText.textContent = progress + "%";
        progressFill.style.width = progress + "%";
    }

    if (message) {
        message.style.display = progress === 100 ? "block" : "none";
    }
}

function activeTheme() {
    if (document.documentElement.dataset.theme === "light" || document.documentElement.dataset.theme === "dark") {
        return document.documentElement.dataset.theme;
    }

    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

function updateThemeToggle(button) {
    const theme = activeTheme();
    const nextTheme = theme === "dark" ? "light" : "dark";
    const visibleLabel = button.querySelector("[aria-hidden='true']");

    button.setAttribute("aria-pressed", String(theme === "dark"));
    button.setAttribute("aria-label", `Switch to ${nextTheme} theme`);

    if (visibleLabel) {
        visibleLabel.textContent = nextTheme === "dark" ? "Dark" : "Light";
    }
}

function initializeNavigation() {
    const menuButton = document.querySelector(".menu-toggle");
    const navigation = document.querySelector("#primary-navigation");
    const themeButton = document.querySelector(".theme-toggle");

    if (themeButton) {
        updateThemeToggle(themeButton);
        themeButton.addEventListener("click", () => {
            const nextTheme = activeTheme() === "dark" ? "light" : "dark";
            document.documentElement.dataset.theme = nextTheme;

            try {
                localStorage.setItem("guidemypc-theme", nextTheme);
            } catch (error) {
                // The visual preference still works when browser storage is unavailable.
            }

            updateThemeToggle(themeButton);
        });
    }

    if (menuButton && navigation) {
        menuButton.addEventListener("click", () => {
            const isOpen = menuButton.getAttribute("aria-expanded") === "true";
            menuButton.setAttribute("aria-expanded", String(!isOpen));
            navigation.classList.toggle("is-open", !isOpen);
        });

        navigation.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                menuButton.setAttribute("aria-expanded", "false");
                navigation.classList.remove("is-open");
            });
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                menuButton.setAttribute("aria-expanded", "false");
                navigation.classList.remove("is-open");
                menuButton.focus();
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    initializeNavigation();
    updateProgress();

    document.querySelectorAll(".step-progress-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            toggleStep(form);
        });
    });
});

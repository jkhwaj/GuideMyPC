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

function initializeSearchAutocomplete() {
    document.querySelectorAll("[data-search-autocomplete]").forEach((form) => {
        const input = form.querySelector('input[name="q"]');
        const list = document.getElementById(form.dataset.suggestionList || "");
        const endpoint = form.dataset.suggestionUrl;
        let suggestions = [];
        let selectedIndex = -1;
        let timer = null;

        if (input === null || list === null || endpoint === undefined) {
            return;
        }

        const close = () => {
            suggestions = [];
            selectedIndex = -1;
            list.replaceChildren();
            list.hidden = true;
            input.setAttribute("aria-expanded", "false");
        };

        const select = (index) => {
            const suggestion = suggestions[index];

            if (suggestion === undefined) {
                return;
            }

            window.location.assign(suggestion.url);
        };

        const render = () => {
            list.replaceChildren();
            suggestions.forEach((suggestion, index) => {
                const option = document.createElement("button");
                option.type = "button";
                option.className = "search-suggestion";
                option.setAttribute("role", "option");
                option.setAttribute("aria-selected", String(index === selectedIndex));
                option.textContent = `${suggestion.label} (${suggestion.type})`;
                option.addEventListener("mousedown", (event) => {
                    event.preventDefault();
                    select(index);
                });
                list.append(option);
            });
            list.hidden = suggestions.length === 0;
            input.setAttribute("aria-expanded", String(suggestions.length > 0));
        };

        const load = () => {
            const query = input.value.trim();

            if (query.length < 2) {
                close();
                return;
            }

            const url = new URL(endpoint, window.location.href);
            url.searchParams.set("q", query);
            fetch(url, { headers: { Accept: "application/json" } })
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    suggestions = Array.isArray(payload?.data?.suggestions) ? payload.data.suggestions : [];
                    selectedIndex = -1;
                    render();
                })
                .catch(close);
        };

        input.addEventListener("input", () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(load, 180);
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                close();
            } else if (event.key === "ArrowDown" && suggestions.length > 0) {
                event.preventDefault();
                selectedIndex = (selectedIndex + 1) % suggestions.length;
                render();
            } else if (event.key === "ArrowUp" && suggestions.length > 0) {
                event.preventDefault();
                selectedIndex = (selectedIndex - 1 + suggestions.length) % suggestions.length;
                render();
            } else if (event.key === "Enter" && selectedIndex >= 0) {
                event.preventDefault();
                select(selectedIndex);
            }
        });

        input.addEventListener("blur", () => window.setTimeout(close, 150));
    });
}

function initializeSearchSelectionTracking() {
    document.querySelectorAll("[data-search-event-url]").forEach((container) => {
        const endpoint = container.dataset.searchEventUrl;
        const query = container.dataset.searchQuery;

        if (endpoint === undefined || query === undefined) {
            return;
        }

        container.querySelectorAll("[data-search-selection]").forEach((link) => {
            link.addEventListener("click", () => {
                const data = new URLSearchParams({
                    query,
                    result_type: link.dataset.searchResultType || "search",
                });
                fetch(endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded", Accept: "application/json" },
                    body: data,
                    keepalive: true,
                }).catch(() => {});
            });
        });
    });
}

function initializeGuideVideoConsent() {
    document.querySelectorAll("[data-video-consent]").forEach((button) => {
        button.addEventListener("click", () => {
            const url = button.dataset.videoUrl;
            const frame = button.parentElement?.querySelector("[data-video-frame]");

            if (url === undefined || frame === null) {
                return;
            }

            const iframe = document.createElement("iframe");
            iframe.src = url;
            iframe.title = "Guide video walkthrough";
            iframe.loading = "lazy";
            iframe.referrerPolicy = "strict-origin-when-cross-origin";
            iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
            iframe.allowFullscreen = true;
            frame.replaceChildren(iframe);
            button.remove();
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initializeNavigation();
    initializeSearchAutocomplete();
    initializeSearchSelectionTracking();
    initializeGuideVideoConsent();
    updateProgress();

    document.querySelectorAll(".step-progress-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            toggleStep(form);
        });
    });
});

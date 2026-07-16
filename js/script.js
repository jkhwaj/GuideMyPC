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

document.addEventListener("DOMContentLoaded", () => {
    updateProgress();

    document.querySelectorAll(".step-progress-form").forEach((form) => {
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            toggleStep(form);
        });
    });
});

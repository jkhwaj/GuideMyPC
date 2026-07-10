function toggleStep(button) {
    const card = button.closest(".step-card");
    const stepId = button.dataset.stepId;

    card.classList.toggle("completed");

    const completed = card.classList.contains("completed") ? 1 : 0;

    button.textContent = completed ? "✓ Completed" : "Mark as Completed";

    fetch("save_progress.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `step_id=${stepId}&completed=${completed}`,
    });

    updateProgress();
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

document.addEventListener("DOMContentLoaded", updateProgress);
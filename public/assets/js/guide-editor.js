(() => {
    const container = document.getElementById("stepsContainer");
    const addButton = document.getElementById("add-step");
    const sourceContainer = document.getElementById("sourcesContainer");
    const addSourceButton = document.getElementById("add-source");

    if (container === null || addButton === null || sourceContainer === null || addSourceButton === null) {
        return;
    }

    const fields = [
        ["title", "Title", "input"],
        ["text", "Action", "textarea"],
        ["expected_result", "Expected result", "textarea"],
        ["warning_text", "Warning", "textarea"],
        ["recovery_text", "Recovery path", "textarea"],
        ["image_url", "Image URL", "input"],
        ["image_alt", "Image alt text", "input"],
        ["video_timestamp", "Video timestamp (seconds)", "input"],
    ];

    function reindex() {
        Array.from(container.querySelectorAll(".step-editor")).forEach((fieldset, index) => {
            fieldset.querySelector("legend").textContent = `Step ${index + 1}`;
            fieldset.querySelectorAll("[data-step-field]").forEach((field) => {
                field.name = `steps[${index}][${field.dataset.stepField}]`;
            });
        });
    }

    function reindexSources() {
        Array.from(sourceContainer.querySelectorAll(".source-editor")).forEach((fieldset, index) => {
            fieldset.querySelector("legend").textContent = `Source ${index + 1}`;
            fieldset.querySelectorAll("[data-source-field]").forEach((field) => {
                field.name = `sources[${index}][${field.dataset.sourceField}]`;
            });
        });
    }

    function actionButton(label, attribute, value = "") {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = label;
        button.setAttribute(attribute, value);
        return button;
    }

    function createStep() {
        const fieldset = document.createElement("fieldset");
        fieldset.className = "step-editor";
        fieldset.append(document.createElement("legend"));

        fields.forEach(([name, labelText, elementName]) => {
            const label = document.createElement("label");
            const field = document.createElement(elementName);
            label.append(`${labelText} `);
            field.dataset.stepField = name;

            if (name === "text") {
                field.required = true;
            } else if (name === "image_url") {
                field.type = "url";
                field.maxLength = 255;
            } else if (name === "image_alt") {
                field.maxLength = 255;
            } else if (name === "title") {
                field.maxLength = 180;
            } else if (name === "video_timestamp") {
                field.type = "number";
                field.min = "0";
                field.max = "86400";
            }

            label.append(field);
            fieldset.append(label);
        });

        const actions = document.createElement("div");
        actions.className = "step-editor-actions";
        actions.append(actionButton("Move up", "data-step-move", "up"));
        actions.append(actionButton("Move down", "data-step-move", "down"));
        actions.append(actionButton("Remove step", "data-step-remove"));
        fieldset.append(actions);

        return fieldset;
    }

    function createSource() {
        const fieldset = document.createElement("fieldset");
        fieldset.className = "source-editor";
        fieldset.append(document.createElement("legend"));

        [["title", "Title", "text", 180], ["official_url", "HTTPS URL", "url", 255]].forEach(([name, labelText, type, maxLength]) => {
            const label = document.createElement("label");
            const field = document.createElement("input");
            label.append(`${labelText} `);
            field.dataset.sourceField = name;
            field.type = type;
            field.maxLength = maxLength;
            label.append(field);
            fieldset.append(label);
        });

        const actions = document.createElement("div");
        actions.className = "source-editor-actions";
        actions.append(actionButton("Move up", "data-source-move", "up"));
        actions.append(actionButton("Move down", "data-source-move", "down"));
        actions.append(actionButton("Remove source", "data-source-remove"));
        fieldset.append(actions);

        return fieldset;
    }

    addButton.addEventListener("click", () => {
        container.append(createStep());
        reindex();
    });

    addSourceButton.addEventListener("click", () => {
        sourceContainer.append(createSource());
        reindexSources();
    });

    container.addEventListener("click", (event) => {
        const button = event.target.closest("button");

        if (button === null) {
            return;
        }

        const fieldset = button.closest(".step-editor");

        if (fieldset === null) {
            return;
        }

        if (button.hasAttribute("data-step-remove")) {
            if (container.querySelectorAll(".step-editor").length > 1) {
                const persistedStep = fieldset.querySelector('[data-step-field="id"]');

                if (persistedStep !== null && !window.confirm("Remove this step? Saved progress for this step will be deleted.")) {
                    return;
                }

                fieldset.remove();
            }
        } else if (button.dataset.stepMove === "up" && fieldset.previousElementSibling !== null) {
            container.insertBefore(fieldset, fieldset.previousElementSibling);
        } else if (button.dataset.stepMove === "down" && fieldset.nextElementSibling !== null) {
            container.insertBefore(fieldset.nextElementSibling, fieldset);
        }

        reindex();
    });

    sourceContainer.addEventListener("click", (event) => {
        const button = event.target.closest("button");

        if (button === null) {
            return;
        }

        const fieldset = button.closest(".source-editor");

        if (fieldset === null) {
            return;
        }

        if (button.hasAttribute("data-source-remove")) {
            if (sourceContainer.querySelectorAll(".source-editor").length > 1) {
                fieldset.remove();
            }
        } else if (button.dataset.sourceMove === "up" && fieldset.previousElementSibling !== null) {
            sourceContainer.insertBefore(fieldset, fieldset.previousElementSibling);
        } else if (button.dataset.sourceMove === "down" && fieldset.nextElementSibling !== null) {
            sourceContainer.insertBefore(fieldset.nextElementSibling, fieldset);
        }

        reindexSources();
    });

    reindex();
    reindexSources();
})();

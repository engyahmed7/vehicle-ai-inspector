document.addEventListener("DOMContentLoaded", function () {
    const fileInputs = document.querySelectorAll(".file-input");
    const analyzeBtn = document.getElementById("analyzeBtn");
    const uploadForm = document.getElementById("uploadForm");
    const progressBar = document.querySelector(".progress-bar");
    const progressFill = document.querySelector(".progress-fill");

    fileInputs.forEach((input) => {
        input.addEventListener("change", function (e) {
            const uploadItem = this.closest(".upload-item");
            const fileName = uploadItem.querySelector(".file-name");

            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                uploadItem.classList.add("has-file");
            } else {
                fileName.textContent = "";
                uploadItem.classList.remove("has-file");
            }

            updateSubmitButton();
        });
    });

    function updateSubmitButton() {
        const hasFiles = Array.from(fileInputs).some(
            (input) => input.files.length > 0
        );
        analyzeBtn.disabled = !hasFiles;
    }

    uploadForm.addEventListener("submit", function (e) {
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = "⏳ Analyzing...";
        progressBar.style.display = "block";

        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressFill.style.width = progress + "%";
        }, 200);

        setTimeout(() => {
            clearInterval(interval);
            progressFill.style.width = "100%";
        }, 1000);
    });

    updateSubmitButton();

    fileInputs.forEach((input) => {
        const uploadItem = input.closest(".upload-item");

        uploadItem.addEventListener("dragover", function (e) {
            e.preventDefault();
            this.style.borderColor = "#3498db";
            this.style.backgroundColor = "#f0f8ff";
        });

        uploadItem.addEventListener("dragleave", function (e) {
            e.preventDefault();
            this.style.borderColor = "#dee2e6";
            this.style.backgroundColor = "#f8f9fa";
        });

        uploadItem.addEventListener("drop", function (e) {
            e.preventDefault();
            this.style.borderColor = "#dee2e6";
            this.style.backgroundColor = "#f8f9fa";

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                input.dispatchEvent(new Event("change"));
            }
        });
    });
});

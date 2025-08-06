function getImageTypeIcon(type) {
    const icons = {
        front: "🚗",
        rear: "🔄",
        left: "⬅️",
        right: "➡️",
        interior_front: "🪑",
        interior_rear: "🛋️",
        dashboard: "📊",
        license_close: "🏷️",
        vin_area: "🔢",
    };
    return icons[type] || "📷";
}

function getImageTypeName(type) {
    const names = {
        front: "Front View",
        rear: "Rear View",
        left: "Left Side",
        right: "Right Side",
        interior_front: "Interior Front",
        interior_rear: "Interior Rear",
        dashboard: "Dashboard",
        license_close: "License Plate",
        vin_area: "VIN Area",
    };
    return (
        names[type] ||
        type.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase())
    );
}

function renderResults(data) {
    const resultsContent = document.getElementById("resultsContent");
    let html = "";

    if (data.vin_area && data.vin_area.vehicle_info) {
        const vehicleInfo = data.vin_area.vehicle_info.basic_info;
        html += `
            <div class="summary-card">
                <div class="summary-title">
                    🚗 Vehicle Summary
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div class="spec-item">
                        <div class="spec-label">Make & Model</div>
                        <div class="spec-value">${vehicleInfo.Make || "N/A"} ${
            vehicleInfo.Model || "N/A"
        }</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Year</div>
                        <div class="spec-value">${
                            vehicleInfo.Year || "N/A"
                        }</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">VIN</div>
                        <div class="spec-value">${
                            data.vin_area.vin || "N/A"
                        }</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Eligibility</div>
                        <div class="eligibility-badge ${
                            data.vin_area.vehicle_age_eligible.includes("✅")
                                ? "eligible"
                                : "not-eligible"
                        }">
                            ${data.vin_area.vehicle_age_eligible}
                        </div>
                    </div>
                </div>
                ${
                    data.vin_area.vehicle_preview
                        ? `<p style="color: #7f8c8d; font-style: italic;">${data.vin_area.vehicle_preview}</p>`
                        : ""
                }
            </div>
        `;
    }

    html += '<div class="results-grid">';

    Object.entries(data).forEach(([type, result]) => {
        if (result.error) {
            html += `
                <div class="result-card">
                    <div class="card-header">
                        <div class="card-icon">${getImageTypeIcon(type)}</div>
                        <div class="card-title">${getImageTypeName(type)}</div>
                    </div>
                    <div class="card-body">
                        <div class="data-item">
                            <div class="data-label">Status</div>
                            <div class="data-value error-value">❌ ${
                                result.error
                            }</div>
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        html += `
            <div class="result-card ${
                type === "vin_area" ? "vehicle-info-card" : ""
            }">
                <div class="card-header ${
                    type === "vin_area" ? "vehicle-info-header" : ""
                }">
                    <div class="card-icon">${getImageTypeIcon(type)}</div>
                    <div class="card-title">${getImageTypeName(type)}</div>
                </div>
                <div class="card-body">
                    ${
                        result.image_url
                            ? `<img src="${
                                  result.image_url
                              }" alt="${getImageTypeName(
                                  type
                              )}" class="image-preview">`
                            : ""
                    }

                    <div class="data-item">
                        <div class="data-label">Status</div>
                        <div class="data-value success-value">✅ Processed Successfully</div>
                    </div>
        `;

        if (result.license_plate) {
            html += `
                <div class="data-item">
                    <div class="data-label">License Plate</div>
                    <div class="data-value">
                        <div class="license-plate">${result.license_plate}</div>
                    </div>
                </div>
            `;
        }

        if (result.odometer) {
            html += `
                <div class="data-item">
                    <div class="data-label">Odometer Reading</div>
                    <div class="data-value">
                        <div class="odometer-reading">${result.odometer}</div>
                    </div>
                </div>
            `;
        }

        if (result.fuel_level) {
            const fuelPercentage = parseInt(result.fuel_level) || 0;
            html += `
                <div class="data-item">
                    <div class="data-label">Fuel Level</div>
                    <div class="data-value">
                        <div class="fuel-indicator">
                            <span>${result.fuel_level}</span>
                            <div class="fuel-bar">
                                <div class="fuel-fill" style="width: ${fuelPercentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        html += `
                    <div class="data-item">
                        <div class="data-label">Cloudinary ID</div>
                        <div class="data-value" style="font-family: monospace; font-size: 0.9rem;">${result.cloudinary_id}</div>
                    </div>
                </div>
            </div>
        `;
    });

    html += "</div>";

    html += `
        <div class="action-buttons">
            <button class="btn btn-secondary" onclick="analyzeAnother()">🔄 Analyze Another Vehicle</button>
        </div>
    `;

    resultsContent.innerHTML = html;

    document.getElementById("loadingState").style.display = "none";
    resultsContent.style.display = "block";
}

function downloadReport() {
}

function analyzeAnother() {
    window.location.href = "/upload";
}

function initializeResults(data) {
    window.analysisData = data;
    setTimeout(() => {
        renderResults(data);
    }, 1000);
}

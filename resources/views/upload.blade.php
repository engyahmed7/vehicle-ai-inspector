<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Image Analysis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .form-container {
            padding: 50px;
        }

        .upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .upload-item {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-item:hover {
            border-color: #3498db;
            background: #f0f8ff;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.1);
        }

        .upload-item.has-file {
            border-color: #27ae60;
            background: #f0fff4;
        }

        .upload-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .upload-item:hover .upload-icon {
            transform: scale(1.1);
        }

        .upload-item.has-file .upload-icon {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }

        .upload-label {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: block;
        }

        .upload-description {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            top: 0;
            left: 0;
        }

        .file-name {
            color: #27ae60;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 10px;
            word-break: break-all;
        }

        .submit-section {
            text-align: center;
            border-top: 1px solid #ecf0f1;
            padding-top: 40px;
        }

        .analyze-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 18px 50px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .analyze-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .analyze-btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: #ecf0f1;
            border-radius: 2px;
            margin-top: 20px;
            overflow: hidden;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            width: 0%;
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .upload-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }

        .image-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .type-exterior { border-left: 4px solid #3498db; }
        .type-interior { border-left: 4px solid #e74c3c; }
        .type-detail { border-left: 4px solid #f39c12; }

        .upload-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            opacity: 0;
        }

        .upload-item:hover::before {
            animation: shine 0.6s ease-in-out;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Vehicle Image Analysis</h1>
            <p>Upload high-quality images of your vehicle for comprehensive analysis</p>
        </div>
        
        <div class="form-container">
            <form action="{{ route('upload.analyze') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                
                <div class="upload-grid">
                    <!-- Exterior Images -->
                    <div class="upload-item type-exterior" data-type="front">
                        <div class="upload-icon">🚗</div>
                        <label class="upload-label">Front View</label>
                        <div class="upload-description">Clear front view of the vehicle</div>
                        <input type="file" name="images[front]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <div class="upload-item type-exterior" data-type="rear">
                        <div class="upload-icon">🔄</div>
                        <label class="upload-label">Rear View</label>
                        <div class="upload-description">Clear rear view of the vehicle</div>
                        <input type="file" name="images[rear]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <div class="upload-item type-exterior" data-type="left">
                        <div class="upload-icon">⬅️</div>
                        <label class="upload-label">Left Side</label>
                        <div class="upload-description">Left side profile view</div>
                        <input type="file" name="images[left]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <div class="upload-item type-exterior" data-type="right">
                        <div class="upload-icon">➡️</div>
                        <label class="upload-label">Right Side</label>
                        <div class="upload-description">Right side profile view</div>
                        <input type="file" name="images[right]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <!-- Interior Images -->
                    <div class="upload-item type-interior" data-type="interior_front">
                        <div class="upload-icon">🪑</div>
                        <label class="upload-label">Interior Front</label>
                        <div class="upload-description">Front seats and interior view</div>
                        <input type="file" name="images[interior_front]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <div class="upload-item type-interior" data-type="interior_rear">
                        <div class="upload-icon">🛋️</div>
                        <label class="upload-label">Interior Rear</label>
                        <div class="upload-description">Rear seats and interior space</div>
                        <input type="file" name="images[interior_rear]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <div class="upload-item type-detail" data-type="dashboard">
                        <div class="upload-icon">📊</div>
                        <label class="upload-label">Dashboard</label>
                        <div class="upload-description">Dashboard and instrument panel</div>
                        <input type="file" name="images[dashboard]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                    
                    <div class="upload-item type-detail" data-type="license_close">
                        <div class="upload-icon">🏷️</div>
                        <label class="upload-label">License Plate</label>
                        <div class="upload-description">Close-up of license plate</div>
                        <input type="file" name="images[license_close]" class="file-input" accept="image/*">
                        <div class="file-name"></div>
                    </div>
                </div>
                
                <div class="submit-section">
                    <button type="submit" class="analyze-btn" id="analyzeBtn">
                        🔍 Start Analysis
                    </button>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('.file-input');
            const analyzeBtn = document.getElementById('analyzeBtn');
            const uploadForm = document.getElementById('uploadForm');
            const progressBar = document.querySelector('.progress-bar');
            const progressFill = document.querySelector('.progress-fill');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const uploadItem = this.closest('.upload-item');
                    const fileName = uploadItem.querySelector('.file-name');
                    
                    if (this.files.length > 0) {
                        fileName.textContent = this.files[0].name;
                        uploadItem.classList.add('has-file');
                    } else {
                        fileName.textContent = '';
                        uploadItem.classList.remove('has-file');
                    }
                    
                    updateSubmitButton();
                });
            });
            
            function updateSubmitButton() {
                const hasFiles = Array.from(fileInputs).some(input => input.files.length > 0);
                analyzeBtn.disabled = !hasFiles;
            }
            
            uploadForm.addEventListener('submit', function(e) {
                analyzeBtn.disabled = true;
                analyzeBtn.innerHTML = '⏳ Analyzing...';
                progressBar.style.display = 'block';
                
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    progressFill.style.width = progress + '%';
                }, 200);
                
                setTimeout(() => {
                    clearInterval(interval);
                    progressFill.style.width = '100%';
                }, 1000);
            });
            
            updateSubmitButton();
            
            fileInputs.forEach(input => {
                const uploadItem = input.closest('.upload-item');
                
                uploadItem.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.style.borderColor = '#3498db';
                    this.style.backgroundColor = '#f0f8ff';
                });
                
                uploadItem.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.style.borderColor = '#dee2e6';
                    this.style.backgroundColor = '#f8f9fa';
                });
                
                uploadItem.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.style.borderColor = '#dee2e6';
                    this.style.backgroundColor = '#f8f9fa';
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        input.files = files;
                        input.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
    </script>
</body>
</html>
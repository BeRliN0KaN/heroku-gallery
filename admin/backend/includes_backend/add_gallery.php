
<head>
    <meta charset="UTF-8">
    <title>สร้างกิจกรรม + ลงทะเบียนใบหน้าอัตโนมัติ</title>
    <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <style>
        #gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .preview-box {
            width: 160px;
            height: 200px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 16px;
            line-height: 24px;
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>🧠 สร้างกิจกรรม พร้อมบันทึกใบหน้าจาก Activity Gallery</h2>

    <form action="" method="post" enctype="multipart/form-data" class="row g-3" id="activity-form">
        <label class="fw-bold">ชื่อกิจกรรม (ใช้ตั้งชื่อไฟล์ใบหน้า):</label>
        <input type="text" class="form-control" id="personName" name="title" placeholder="เช่น kann">

        <!-- <label>Activity Image</label>
    <input type="file" name="activity_image" accept="image/*"> -->

        <label>Activity Gallery (เลือกรูปหลายใบหน้าที่ต้องการเก็บ JSON)</label>
        <input type="file" id="activity_gallery" name="activity_gallery[]" multiple accept="image/*">

        <div id="gallery-preview" class="row mt-2"></div>

        <input type="submit" class="btn btn-primary" name="create_post" value="Publish">
    </form>

    <p id="status">รอสถานะการประมวลผล...</p>

    <script>
        let pendingDescriptors = [];
        const uploadedFiles = new Set();

        async function loadModels() {
            const modelPath = "/FaceRegJS-master/models";
            await faceapi.nets.ssdMobilenetv1.loadFromUri(modelPath);
            await faceapi.nets.faceLandmark68Net.loadFromUri(modelPath);
            await faceapi.nets.faceRecognitionNet.loadFromUri(modelPath);
        }


        document.getElementById('activity_gallery').addEventListener('change', async function(e) {
            const preview = document.getElementById('gallery-preview');
            const files = Array.from(e.target.files);
            const name = document.getElementById("personName").value.trim();
            if (!name) {
                alert("⚠️ กรุณากรอกชื่อกิจกรรมก่อนเลือกภาพ");
                return;
            }

            await loadModels();

            let faceCount = pendingDescriptors.length + 1;
            for (const file of files) {
                if (uploadedFiles.has(file.name)) {
                    console.warn(`⚠️ ข้าม ${file.name} เพราะเลือกซ้ำแล้ว`);
                    continue;
                }

                const imageDataUrl = await new Promise(resolve => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.readAsDataURL(file);
                });

                const img = new Image();
                img.src = imageDataUrl;
                await img.decode();

                const detections = await faceapi
                    .detectAllFaces(img)
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                if (!detections.length) {
                    console.warn(`❌ ไม่พบใบหน้าในภาพ ${file.name}`);
                    continue;
                }

                const box = document.createElement('div');
                box.className = 'preview-box';

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = () => {
                    preview.removeChild(box);
                    pendingDescriptors = pendingDescriptors.filter(d => d.sourceFile !== file.name);
                    uploadedFiles.delete(file.name);
                };

                const faceImg = document.createElement('img');
                faceImg.src = imageDataUrl;
                box.appendChild(removeBtn);
                box.appendChild(faceImg);
                preview.appendChild(box);

                detections.forEach((det, i) => {
                    const descriptor = Array.from(det.descriptor);
                    pendingDescriptors.push({
                        name: `${name}_${faceCount}`,
                        descriptors: [descriptor],
                        sourceFile: file.name,
                        fullImage: imageDataUrl // เพิ่มภาพต้นฉบับ
                    });
                    faceCount++;
                });

                uploadedFiles.add(file.name);
            }

            // รีเซ็ต input เพื่อให้สามารถเลือกไฟล์เดิมอีกครั้งหลังจากลบแล้วได้
            e.target.value = '';
        });

        document.getElementById('activity-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            for (const item of pendingDescriptors) {
                const response = await fetch("upload_descriptor.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(item)
                });
                const result = await response.json();
                console.log(`✔️ บันทึก ${item.name}.json →`, result.message);
            }

            this.submit();
        });
    </script>

</body>

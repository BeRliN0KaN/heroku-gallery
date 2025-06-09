const imageUpload = document.getElementById('imageUpload')

Promise.all([
  faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
  faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
  faceapi.nets.ssdMobilenetv1.loadFromUri('/models')
]).then(start)

async function start() {
  const container = document.createElement('div')
  container.style.position = 'relative'
  document.body.append(container)

  const labeledFaceDescriptors = await loadLabeledImages()
  const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.3)
  let image, canvas

  document.body.append('โหลดเสร็จแล้ว')

  imageUpload.addEventListener('change', async () => {
    if (image) image.remove()
    if (canvas) canvas.remove()

    image = await faceapi.bufferToImage(imageUpload.files[0])
    container.append(image)

    canvas = faceapi.createCanvasFromMedia(image)
    container.append(canvas)

    const displaySize = { width: image.width, height: image.height }
    faceapi.matchDimensions(canvas, displaySize)

    const detections = await faceapi
      .detectAllFaces(image)
      .withFaceLandmarks()
      .withFaceDescriptors()

    const resizedDetections = faceapi.resizeResults(detections, displaySize)

    const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor))

    results.forEach((result, i) => {
      const box = resizedDetections[i].detection.box
      let label

      // ✅ หากระยะมากกว่า 0.5 ให้ถือว่า unknown
      if (result.distance > 0.5) {
        label = '❌ unknown'
      } else {
        label = `✅ ${result.label} (${(1 - result.distance).toFixed(2)})`
      }

      const drawBox = new faceapi.draw.DrawBox(box, { label: label })
      drawBox.draw(canvas)
    })
  })
}

function loadLabeledImages() {
  const labels = ['Jisoo', 'Lisa', 'Rose', 'Jennie']
  return Promise.all(
    labels.map(async label => {
      const descriptions = []
      for (let i = 1; i <= 4; i++) {
        try {
          const img = await faceapi.fetchImage(`gallery/${label}/${i}.jpg`)
          const detection = await faceapi
            .detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor()
          if (detection && detection.descriptor) {
            descriptions.push(detection.descriptor)
          }
        } catch (err) {
          console.warn(`⚠️ โหลดไม่ได้: gallery/${label}/${i}.jpg`, err)
        }
      }
      return new faceapi.LabeledFaceDescriptors(label, descriptions)
    })
  )
}

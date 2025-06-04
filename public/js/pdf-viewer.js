// PDF Viewer with Watermark
function initPdfViewer(containerId, pdfUrl, options = {}) {
  const container = document.getElementById(containerId)
  if (!container) return

  const defaultOptions = {
    scale: 1.2,
    watermark: true,
    watermarkText: "CONFIDENTIAL",
  }

  const settings = { ...defaultOptions, ...options }

  let pdfDoc = null
  let pageNum = 1
  let pageRendering = false
  let pageNumPending = null
  const canvas = document.createElement("canvas")
  const ctx = canvas.getContext("2d")

  container.appendChild(canvas)

  // Declare pdfjsLib variable
  const pdfjsLib = window.pdfjsLib

  // Load PDF.js if not already loaded
  if (typeof pdfjsLib === "undefined") {
    console.error("PDF.js library not loaded")
    return
  }

  // Set worker path
  pdfjsLib.GlobalWorkerOptions.workerSrc = "/js/pdf.worker.min.js"

  // Load the PDF
  pdfjsLib
    .getDocument(pdfUrl)
    .promise.then((pdf) => {
      pdfDoc = pdf

      // Display total pages
      if (settings.totalPages) {
        document.getElementById(settings.totalPages).textContent = pdfDoc.numPages
      }

      // Initial render
      renderPage(pageNum)

      // Set up navigation controls
      setupNavigation()
    })
    .catch((error) => {
      console.error("Error loading PDF:", error)
      container.innerHTML = '<div class="alert alert-danger">Error loading PDF. Please try again later.</div>'
    })

  function renderPage(num) {
    pageRendering = true

    // Get the page
    pdfDoc.getPage(num).then((page) => {
      // Set scale
      const viewport = page.getViewport({ scale: settings.scale })
      canvas.height = viewport.height
      canvas.width = viewport.width

      // Render the PDF page
      const renderContext = {
        canvasContext: ctx,
        viewport: viewport,
      }

      page.render(renderContext).promise.then(() => {
        // Add watermark if enabled
        if (settings.watermark) {
          addWatermark()
        }

        pageRendering = false

        // If another page is pending, render it
        if (pageNumPending !== null) {
          renderPage(pageNumPending)
          pageNumPending = null
        }

        // Update page counter if provided
        if (settings.pageCounter) {
          document.getElementById(settings.pageCounter).textContent = num
        }

        // Update page input if provided
        if (settings.pageInput) {
          document.getElementById(settings.pageInput).value = num
        }

        // Track reading progress
        trackReadingProgress(num)
      })
    })
  }

  function addWatermark() {
    // Save context
    ctx.save()

    // Watermark text
    const text = settings.watermarkText || "CONFIDENTIAL"

    // Set watermark style
    ctx.globalAlpha = 0.15
    ctx.font = "60px Arial"
    ctx.fillStyle = "red"
    ctx.textAlign = "center"

    // Rotate and position watermark
    ctx.translate(canvas.width / 2, canvas.height / 2)
    ctx.rotate(-Math.PI / 4) // 45 degrees
    ctx.fillText(text, 0, 0)

    // Add user info if available
    if (settings.userName) {
      ctx.font = "24px Arial"
      ctx.fillText(settings.userName, 0, 50)
      ctx.fillText(new Date().toLocaleString(), 0, 80)
    }

    // Restore context
    ctx.restore()
  }

  function queueRenderPage(num) {
    if (pageRendering) {
      pageNumPending = num
    } else {
      renderPage(num)
    }
  }

  function prevPage() {
    if (pageNum <= 1) return
    pageNum--
    queueRenderPage(pageNum)
  }

  function nextPage() {
    if (pageNum >= pdfDoc.numPages) return
    pageNum++
    queueRenderPage(pageNum)
  }

  function goToPage(num) {
    if (num < 1 || num > pdfDoc.numPages) return
    pageNum = num
    queueRenderPage(pageNum)
  }

  function setScale(newScale) {
    settings.scale = newScale
    queueRenderPage(pageNum)
  }

  function setupNavigation() {
    // Set up navigation elements if provided in options
    if (settings.prevButton) {
      document.getElementById(settings.prevButton).addEventListener("click", prevPage)
    }

    if (settings.nextButton) {
      document.getElementById(settings.nextButton).addEventListener("click", nextPage)
    }

    if (settings.pageInput && settings.goToPageButton) {
      document.getElementById(settings.goToPageButton).addEventListener("click", () => {
        const pageNumber = Number.parseInt(document.getElementById(settings.pageInput).value)
        goToPage(pageNumber)
      })
    }

    if (settings.zoomIn) {
      document.getElementById(settings.zoomIn).addEventListener("click", () => {
        setScale(settings.scale + 0.2)
      })
    }

    if (settings.zoomOut) {
      document.getElementById(settings.zoomOut).addEventListener("click", () => {
        if (settings.scale > 0.4) {
          setScale(settings.scale - 0.2)
        }
      })
    }

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") prevPage()
      if (e.key === "ArrowRight") nextPage()
    })
  }

  function trackReadingProgress(page) {
    // If we have a book ID and CSRF token, track reading progress
    const bookId = container.dataset.bookId
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

    if (bookId && csrfToken) {
      // Calculate reading time (time since page load or last page change)
      const currentTime = Date.now()
      const startTime = window.pdfViewerStartTime || currentTime
      const readingTime = Math.floor((currentTime - startTime) / 60000) // in minutes

      // Update start time for next calculation
      window.pdfViewerStartTime = currentTime

      // Send reading progress to server
      fetch(`/digital-books/${bookId}/progress`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          page: page,
          reading_time: readingTime,
        }),
      }).catch((error) => console.error("Error updating reading progress:", error))
    }
  }

  // Return public methods
  return {
    prevPage: prevPage,
    nextPage: nextPage,
    goToPage: goToPage,
    setScale: setScale,
  }
}

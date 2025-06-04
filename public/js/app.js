// Main application JavaScript file
window.pdfViewerStartTime = Date.now()

document.addEventListener("DOMContentLoaded", () => {
  initializeBootstrapComponents()
  initializeFormHandlers()
  initializePdfViewer()
})

function initializeBootstrapComponents() {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map((tooltipTriggerEl) => new window.bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  popoverTriggerList.map((popoverTriggerEl) => new window.bootstrap.Popover(popoverTriggerEl))
}

function initializeFormHandlers() {
  // Search form handler
  const searchForm = document.getElementById("filterForm")
  if (searchForm) {
    searchForm.addEventListener("submit", (e) => {
      const searchInput = searchForm.querySelector('input[name="search"]')
      if (searchInput && !searchInput.value.trim()) {
        // Allow empty search to show all results
      }
    })
  }

  // File upload preview
  const pdfFileInput = document.getElementById("pdf_file")
  if (pdfFileInput) {
    pdfFileInput.addEventListener("change", handleFileUpload)
  }
}

function handleFileUpload() {

  const filePreview = document.getElementById("filePreview")
  const fileName = document.getElementById("fileName")
  const fileSize = document.getElementById("fileSize")

  if (this.files.length > 0) {
    const file = this.files[0]
    const sizeInMB = (file.size / 1024 / 1024).toFixed(2)

    if (fileName) fileName.textContent = file.name
    if (fileSize) fileSize.textContent = `(${sizeInMB} MB)`
    if (filePreview) filePreview.style.display = "block"

    // Validate file size (20MB limit)
    if (file.size > 20 * 1024 * 1024) {
      alert("Ukuran file terlalu besar! Maksimal 20MB.")
      this.value = ""
      if (filePreview) filePreview.style.display = "none"
    }
  } else {
    if (filePreview) filePreview.style.display = "none"
  }
}

function initializePdfViewer() {
  const pdfContainer = document.getElementById("pdfViewer")
  if (pdfContainer && pdfContainer.dataset.pdfUrl) {
    const pdfUrl = pdfContainer.dataset.pdfUrl

    // Initialize PDF viewer with options
    if (typeof window.initPdfViewer === "function") {
      window.initPdfViewer("pdfViewer", pdfUrl, {
        watermark: true,
        watermarkText: pdfContainer.dataset.watermarkText || "PERPUSTAKAAN DIGITAL",
        userName: pdfContainer.dataset.userName || "",
        prevButton: "prevPage",
        nextButton: "nextPage",
        pageCounter: "currentPage",
        totalPages: "totalPages",
        pageInput: "pageInput",
        goToPageButton: "goToPage",
        zoomIn: "zoomIn",
        zoomOut: "zoomOut",
      })
    }
  }
}

window.initPdfViewer = (containerId, pdfUrl, options = {}) => {
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

  // Hide loading spinner and add canvas
  const loadingSpinner = document.getElementById("loadingSpinner")
  if (loadingSpinner) loadingSpinner.style.display = "none"

  container.appendChild(canvas)

  // Declare pdfjsLib variable
  const pdfjsLib = window.pdfjsLib

  // Set PDF.js worker
  if (typeof pdfjsLib !== "undefined") {
    pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"

    // Load the PDF
    pdfjsLib
      .getDocument(pdfUrl)
      .promise.then((pdf) => {
        pdfDoc = pdf

        // Display total pages
        if (settings.totalPages) {
          const totalPagesElement = document.getElementById(settings.totalPages)
          if (totalPagesElement) {
            totalPagesElement.textContent = pdfDoc.numPages
          }
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
  }

  function renderPage(num) {
    pageRendering = true

    pdfDoc.getPage(num).then((page) => {
      const viewport = page.getViewport({ scale: settings.scale })
      canvas.height = viewport.height
      canvas.width = viewport.width

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

        // Update page counter
        if (settings.pageCounter) {
          const pageCounterElement = document.getElementById(settings.pageCounter)
          if (pageCounterElement) {
            pageCounterElement.textContent = num
          }
        }

        // Update page input
        if (settings.pageInput) {
          const pageInputElement = document.getElementById(settings.pageInput)
          if (pageInputElement) {
            pageInputElement.value = num
          }
        }

        // Update navigation buttons
        updateNavigationButtons()

        // Track reading progress
        trackReadingProgress(num)
      })
    })
  }

  function addWatermark() {
    ctx.save()

    const text = settings.watermarkText || "CONFIDENTIAL"

    ctx.globalAlpha = 0.15
    ctx.font = "60px Arial"
    ctx.fillStyle = "red"
    ctx.textAlign = "center"

    ctx.translate(canvas.width / 2, canvas.height / 2)
    ctx.rotate(-Math.PI / 4)
    ctx.fillText(text, 0, 0)

    if (settings.userName) {
      ctx.font = "24px Arial"
      ctx.fillText(settings.userName, 0, 50)
      ctx.fillText(new Date().toLocaleString(), 0, 80)
    }

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

  function updateNavigationButtons() {
    if (settings.prevButton) {
      const prevBtn = document.getElementById(settings.prevButton)
      if (prevBtn) {
        prevBtn.disabled = pageNum <= 1
      }
    }

    if (settings.nextButton) {
      const nextBtn = document.getElementById(settings.nextButton)
      if (nextBtn) {
        nextBtn.disabled = pageNum >= pdfDoc.numPages
      }
    }
  }

  function setupNavigation() {
    if (settings.prevButton) {
      const prevBtn = document.getElementById(settings.prevButton)
      if (prevBtn) {
        prevBtn.addEventListener("click", prevPage)
      }
    }

    if (settings.nextButton) {
      const nextBtn = document.getElementById(settings.nextButton)
      if (nextBtn) {
        nextBtn.addEventListener("click", nextPage)
      }
    }

    if (settings.pageInput && settings.goToPageButton) {
      const pageInput = document.getElementById(settings.pageInput)
      const goToPageBtn = document.getElementById(settings.goToPageButton)

      if (pageInput && goToPageBtn) {
        goToPageBtn.addEventListener("click", () => {
          const pageNumber = Number.parseInt(pageInput.value)
          goToPage(pageNumber)
        })

        pageInput.addEventListener("keypress", (e) => {
          if (e.key === "Enter") {
            const pageNumber = Number.parseInt(pageInput.value)
            goToPage(pageNumber)
          }
        })
      }
    }

    if (settings.zoomIn) {
      const zoomInBtn = document.getElementById(settings.zoomIn)
      if (zoomInBtn) {
        zoomInBtn.addEventListener("click", () => {
          setScale(settings.scale + 0.2)
        })
      }
    }

    if (settings.zoomOut) {
      const zoomOutBtn = document.getElementById(settings.zoomOut)
      if (zoomOutBtn) {
        zoomOutBtn.addEventListener("click", () => {
          if (settings.scale > 0.4) {
            setScale(settings.scale - 0.2)
          }
        })
      }
    }

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") prevPage()
      if (e.key === "ArrowRight") nextPage()
    })
  }

  function trackReadingProgress(page) {
    const bookId = container.dataset.bookId
    const csrfToken = document.querySelector('meta[name="csrf-token"]')

    if (bookId && csrfToken) {
      const currentTime = Date.now()
      const startTime = window.pdfViewerStartTime || currentTime
      const readingTime = Math.floor((currentTime - startTime) / 60000)

      window.pdfViewerStartTime = currentTime

      fetch(`/digital-books/${bookId}/progress`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken.content,
        },
        body: JSON.stringify({
          page: page,
          reading_time: readingTime,
        }),
      }).catch((error) => {
        console.error("Error updating reading progress:", error)
      })
    }
  }

  return {
    prevPage: prevPage,
    nextPage: nextPage,
    goToPage: goToPage,
    setScale: setScale,
  }
}

// Initialize charts for admin dashboard
window.initCharts = () => {
  const readingTrendsChart = document.getElementById("readingTrendsChart")
  if (readingTrendsChart && typeof window.Chart !== "undefined") {
    // Get data from the element's data attributes
    const labels = JSON.parse(readingTrendsChart.dataset.labels || "[]")
    const readers = JSON.parse(readingTrendsChart.dataset.readers || "[]")
    const readingTime = JSON.parse(readingTrendsChart.dataset.readingTime || "[]")

    new window.Chart(readingTrendsChart, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Jumlah Pembaca",
            data: readers,
            borderColor: "rgba(54, 162, 235, 1)",
            backgroundColor: "rgba(54, 162, 235, 0.2)",
            yAxisID: "y",
          },
          {
            label: "Waktu Baca (jam)",
            data: readingTime,
            borderColor: "rgba(255, 99, 132, 1)",
            backgroundColor: "rgba(255, 99, 132, 0.2)",
            yAxisID: "y1",
          },
        ],
      },
      options: {
        responsive: true,
        interaction: {
          mode: "index",
          intersect: false,
        },
        scales: {
          y: {
            type: "linear",
            display: true,
            position: "left",
            title: {
              display: true,
              text: "Jumlah Pembaca",
            },
          },
          y1: {
            type: "linear",
            display: true,
            position: "right",
            title: {
              display: true,
              text: "Waktu Baca (jam)",
            },
            grid: {
              drawOnChartArea: false,
            },
          },
        },
      },
    })
  }

  const categoryDistributionChart = document.getElementById("categoryDistributionChart")
  if (categoryDistributionChart && typeof window.Chart !== "undefined") {
    const categories = JSON.parse(categoryDistributionChart.dataset.categories || "[]")
    const bookCounts = JSON.parse(categoryDistributionChart.dataset.bookCounts || "[]")

    new window.Chart(categoryDistributionChart, {
      type: "pie",
      data: {
        labels: categories,
        datasets: [
          {
            data: bookCounts,
            backgroundColor: [
              "rgba(54, 162, 235, 0.7)",
              "rgba(255, 99, 132, 0.7)",
              "rgba(255, 206, 86, 0.7)",
              "rgba(75, 192, 192, 0.7)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: "right",
          },
          title: {
            display: true,
            text: "Distribusi Buku per Kategori",
          },
        },
      },
    })
  }
}

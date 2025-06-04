// PDF Viewer with Watermark
import * as pdfjsLib from "pdfjs-dist"

// Set worker path
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"

export class PdfViewer {
  constructor(containerId, pdfUrl, options = {}) {
    this.container = document.getElementById(containerId)
    this.pdfUrl = pdfUrl
    this.options = {
      scale: 1.2,
      watermark: true,
      watermarkText: "CONFIDENTIAL",
      ...options,
    }

    this.pdfDoc = null
    this.pageNum = 1
    this.pageRendering = false
    this.pageNumPending = null
    this.canvas = document.createElement("canvas")
    this.ctx = this.canvas.getContext("2d")

    this.container.appendChild(this.canvas)

    this.init()
  }

  async init() {
    try {
      // Load the PDF
      this.pdfDoc = await pdfjsLib.getDocument(this.pdfUrl).promise

      // Initial render
      this.renderPage(this.pageNum)

      // Set up navigation controls if provided
      this.setupNavigation()

      // Return the loaded document
      return this.pdfDoc
    } catch (error) {
      console.error("Error loading PDF:", error)
    }
  }

  async renderPage(num) {
    this.pageRendering = true

    try {
      // Get the page
      const page = await this.pdfDoc.getPage(num)

      // Set scale
      const viewport = page.getViewport({ scale: this.options.scale })
      this.canvas.height = viewport.height
      this.canvas.width = viewport.width

      // Render the PDF page
      const renderContext = {
        canvasContext: this.ctx,
        viewport: viewport,
      }

      await page.render(renderContext).promise

      // Add watermark if enabled
      if (this.options.watermark) {
        this.addWatermark()
      }

      this.pageRendering = false

      // If another page is pending, render it
      if (this.pageNumPending !== null) {
        this.renderPage(this.pageNumPending)
        this.pageNumPending = null
      }

      // Update page counter if provided
      if (this.pageCounter) {
        this.pageCounter.textContent = num
      }

      // Update page input if provided
      if (this.pageInput) {
        this.pageInput.value = num
      }

      // Trigger page change event
      this.triggerPageChangeEvent(num)
    } catch (error) {
      console.error("Error rendering page:", error)
      this.pageRendering = false
    }
  }

  addWatermark() {
    const ctx = this.ctx
    const canvas = this.canvas

    // Save context
    ctx.save()

    // Watermark text
    const text = this.options.watermarkText || "CONFIDENTIAL"

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
    if (this.options.userName) {
      ctx.font = "24px Arial"
      ctx.fillText(this.options.userName, 0, 50)
      ctx.fillText(new Date().toLocaleString(), 0, 80)
    }

    // Restore context
    ctx.restore()
  }

  queueRenderPage(num) {
    if (this.pageRendering) {
      this.pageNumPending = num
    } else {
      this.renderPage(num)
    }
  }

  prevPage() {
    if (this.pageNum <= 1) return
    this.pageNum--
    this.queueRenderPage(this.pageNum)
  }

  nextPage() {
    if (this.pageNum >= this.pdfDoc.numPages) return
    this.pageNum++
    this.queueRenderPage(this.pageNum)
  }

  goToPage(num) {
    if (num < 1 || num > this.pdfDoc.numPages) return
    this.pageNum = num
    this.queueRenderPage(this.pageNum)
  }

  setScale(newScale) {
    this.options.scale = newScale
    this.queueRenderPage(this.pageNum)
  }

  setupNavigation() {
    // Set up navigation elements if provided in options
    if (this.options.prevButton) {
      document.getElementById(this.options.prevButton).addEventListener("click", () => this.prevPage())
    }

    if (this.options.nextButton) {
      document.getElementById(this.options.nextButton).addEventListener("click", () => this.nextPage())
    }

    if (this.options.pageCounter) {
      this.pageCounter = document.getElementById(this.options.pageCounter)
    }

    if (this.options.totalPages) {
      const totalPagesElement = document.getElementById(this.options.totalPages)
      if (totalPagesElement) {
        totalPagesElement.textContent = this.pdfDoc.numPages
      }
    }

    if (this.options.pageInput && this.options.goToPageButton) {
      this.pageInput = document.getElementById(this.options.pageInput)
      const goToPageButton = document.getElementById(this.options.goToPageButton)

      goToPageButton.addEventListener("click", () => {
        const pageNumber = Number.parseInt(this.pageInput.value)
        this.goToPage(pageNumber)
      })
    }

    if (this.options.zoomIn) {
      document.getElementById(this.options.zoomIn).addEventListener("click", () => {
        this.setScale(this.options.scale + 0.2)
      })
    }

    if (this.options.zoomOut) {
      document.getElementById(this.options.zoomOut).addEventListener("click", () => {
        if (this.options.scale > 0.4) {
          this.setScale(this.options.scale - 0.2)
        }
      })
    }
  }

  triggerPageChangeEvent(pageNumber) {
    // Create and dispatch custom event
    const event = new CustomEvent("pagechange", {
      detail: {
        page: pageNumber,
        totalPages: this.pdfDoc.numPages,
      },
    })

    this.container.dispatchEvent(event)
  }
}

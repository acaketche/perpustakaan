import "./bootstrap"
import "../sass/app.scss"

// PDF Viewer functionality
import { PdfViewer } from "./pdf-viewer"

// Make PdfViewer globally available
window.PdfViewer = PdfViewer

// Bootstrap JavaScript
import bootstrap from "bootstrap"

// Chart.js for analytics
import Chart from "chart.js/auto"
window.Chart = Chart

// Initialize tooltips and other Bootstrap components
document.addEventListener("DOMContentLoaded", () => {
  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize Bootstrap popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))
})

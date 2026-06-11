import QRCode from 'qrcode'

// Render every check-in QR canvas on the page from its data-token.
document.querySelectorAll('canvas[data-token]').forEach((canvas) => {
    const token = canvas.dataset.token
    if (!token) return
    QRCode.toCanvas(canvas, token, { width: 180, margin: 1 }, (err) => {
        if (err) console.error('QR render failed', err)
    })
})

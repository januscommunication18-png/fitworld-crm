import { Notyf } from 'notyf'

const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    dismissible: true,
    ripple: true,
    types: [
        {
            type: 'success',
            background: 'var(--color-success)',
            icon: { className: 'icon-[tabler--circle-check] !text-success', tagName: 'i' },
            color: 'white',
        },
        {
            type: 'error',
            background: 'var(--color-error)',
            icon: { className: 'icon-[tabler--circle-x] !text-error', tagName: 'i' },
            color: 'white',
        },
        {
            type: 'warning',
            background: 'var(--color-warning)',
            icon: { className: 'icon-[tabler--alert-triangle] !text-warning', tagName: 'i' },
            color: 'white',
        },
        {
            type: 'info',
            background: 'var(--color-info)',
            icon: { className: 'icon-[tabler--info-circle] !text-info', tagName: 'i' },
            color: 'white',
        },
    ],
})

export const toast = {
    success: (msg) => notyf.success(msg),
    error: (msg) => notyf.error(msg),
    warning: (msg) => notyf.open({ type: 'warning', message: msg }),
    info: (msg) => notyf.open({ type: 'info', message: msg }),
}

export default toast

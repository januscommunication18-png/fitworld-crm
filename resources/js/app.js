import './bootstrap';
// FlyonUI is required for the styled <x-studio-select> dropdown (advance-select
// hydrator). The dashboard layout loaded it via layout.js, but subdomain pages
// pull only app.js, so without this import their advance-selects render as a
// hidden native <select> and the user sees nothing.
import 'flyonui/flyonui.js';

import React from 'react'
import { createRoot } from 'react-dom/client'
import App from './react/App'
import './react/i18n'

const el = document.getElementById('app')
if (el) {
  const root = createRoot(el)
  root.render(<App />)
}

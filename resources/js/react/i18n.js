import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

const resources = {
  en: { translation: { welcome: 'Welcome to AlBaik Store' } },
  ar: { translation: { welcome: 'مرحباً بكم في متجر البيك' } },
}

i18n.use(initReactI18next).init({
  resources,
  lng: 'en',
  fallbackLng: 'en',
  interpolation: { escapeValue: false },
})

export default i18n

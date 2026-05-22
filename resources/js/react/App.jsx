import React from 'react'
import { BrowserRouter, Routes, Route, Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useStore } from './store'
import Home from './pages/Home'
import Shop from './pages/Shop'
import Product from './pages/Product'
import Cart from './pages/Cart'
import Checkout from './pages/Checkout'
import Orders from './pages/Orders'
import Profile from './pages/Profile'

export default function App(){
  const { t, i18n } = useTranslation()
  const { currency, setCurrency } = useStore()

  const switchLocale = (locale) => {
    i18n.changeLanguage(locale)
    document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr'
  }

  return (
    <BrowserRouter>
      <div className="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-50">
        <header className="border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/70">
          <div className="mx-auto flex flex-wrap items-center justify-between gap-3 px-4 py-4 max-w-7xl">
            <Link to="/" className="text-xl font-semibold">AlBaik Store</Link>
            <div className="flex flex-wrap items-center gap-3 text-sm">
              <Link to="/shop" className="hover:text-sky-600">{t('Shop')}</Link>
              <Link to="/cart" className="hover:text-sky-600">{t('Cart')}</Link>
              <Link to="/orders" className="hover:text-sky-600">{t('Orders')}</Link>
              <Link to="/profile" className="hover:text-sky-600">{t('Profile')}</Link>
              <button onClick={() => switchLocale(i18n.language === 'en' ? 'ar' : 'en')} className="rounded-full border px-3 py-1 text-slate-700 dark:text-slate-100">
                {i18n.language === 'en' ? 'العربية' : 'English'}
              </button>
              <select value={currency} onChange={(e) => setCurrency(e.target.value)} className="rounded-full border px-3 py-1 bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="USD">USD</option>
                <option value="TRY">TRY</option>
                <option value="SYP">SYP</option>
              </select>
            </div>
          </div>
        </header>

        <main className="mx-auto max-w-7xl px-4 py-8">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/shop" element={<Shop />} />
            <Route path="/product/:id" element={<Product />} />
            <Route path="/cart" element={<Cart />} />
            <Route path="/checkout" element={<Checkout />} />
            <Route path="/orders" element={<Orders />} />
            <Route path="/profile" element={<Profile />} />
          </Routes>
        </main>
      </div>
    </BrowserRouter>
  )
}

import React from 'react'
import { useTranslation } from 'react-i18next'

export default function Home(){
  const { t } = useTranslation()

  return (
    <div className="space-y-6">
      <section className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 className="text-4xl font-bold tracking-tight">{t('welcome')}</h1>
        <p className="mt-4 max-w-2xl text-slate-600 dark:text-slate-300">{t('Discover the future of e-commerce with a scalable and multilingual store platform built for AlBaik.')}</p>
      </section>

      <section className="grid gap-4 md:grid-cols-3">
        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <h2 className="text-xl font-semibold">Retail & Wholesale</h2>
          <p className="mt-2 text-slate-600 dark:text-slate-300">Flexible pricing based on quantity with automatic wholesale rates.</p>
        </div>
        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <h2 className="text-xl font-semibold">Multilingual</h2>
          <p className="mt-2 text-slate-600 dark:text-slate-300">Arabic RTL and English LTR support with dynamic locale switching.</p>
        </div>
        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <h2 className="text-xl font-semibold">Admin Panel</h2>
          <p className="mt-2 text-slate-600 dark:text-slate-300">Filament-ready backend with product, orders, and settings management.</p>
        </div>
      </section>
    </div>
  )
}

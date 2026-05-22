import { create } from 'zustand'

export const useStore = create(set => ({
  currency: 'USD',
  setCurrency: (c) => set({ currency: c }),
}))

import React from 'react'
import { useParams } from 'react-router-dom'

export default function Product(){
  const { id } = useParams()
  return (
    <div>
      <h2 className="text-2xl font-semibold">Product {id}</h2>
      <p>Product details will be loaded from API.</p>
    </div>
  )
}

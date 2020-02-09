// __tests__/fetch.test.js
import React from 'react'
import { render, fireEvent, waitForElement } from '@testing-library/react'
import Authentication from "./Authentication.js"

test('loads and displays greeting', async () => {
  const { debug } = render(
      <Authentication />
  )


})
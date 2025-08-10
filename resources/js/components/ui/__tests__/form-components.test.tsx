import { render, screen } from '@testing-library/react'
import { Input } from '../input'
import { Label } from '../label'
import { Checkbox } from '../checkbox'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../select'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { test } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { test } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { test } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { test } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { expect } from 'vitest'
import { test } from 'vitest'
import { describe } from 'vitest'

describe('Form Components with Blue Theme', () => {
  test('Input component renders with blue theme classes', () => {
    render(<Input data-testid="test-input" placeholder="Test input" />)
    const input = screen.getByTestId('test-input')
    
    expect(input).toHaveClass('border-input')
    expect(input).toHaveClass('focus-visible:ring-ring')
    expect(input).toHaveClass('focus-visible:ring-ring')
    expect(input).toHaveClass('hover:border-ring/60')
  })

  test('Label component renders with blue theme text colors', () => {
    render(<Label data-testid="test-label">Test Label</Label>)
    const label = screen.getByTestId('test-label')
    
    expect(label).toHaveClass('text-foreground')
    expect(label).toHaveClass('text-sm')
    expect(label).toHaveClass('font-medium')
  })

  test('Checkbox component renders with blue theme states', () => {
    render(<Checkbox data-testid="test-checkbox" />)
    const checkbox = screen.getByTestId('test-checkbox')
    
    expect(checkbox).toHaveClass('border-input')
    expect(checkbox).toHaveClass('data-[state=checked]:bg-primary')
    expect(checkbox).toHaveClass('focus-visible:border-ring')
    expect(checkbox).toHaveClass('focus-visible:ring-ring/30')
    expect(checkbox).toHaveClass('hover:border-ring/60')
  })

  test('Select trigger renders with blue theme focus states', () => {
    render(
      <Select>
        <SelectTrigger data-testid="test-select">
          <SelectValue placeholder="Select an option" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="option1">Option 1</SelectItem>
        </SelectContent>
      </Select>
    )
    const selectTrigger = screen.getByTestId('test-select')
    
    expect(selectTrigger).toHaveClass('border-input')
    expect(selectTrigger).toHaveClass('focus-visible:border-ring')
    expect(selectTrigger).toHaveClass('focus-visible:ring-ring/30')
    expect(selectTrigger).toHaveClass('hover:border-ring/60')
    expect(selectTrigger).toHaveClass('data-[state=open]:border-ring')
  })

  test('Required label renders with red asterisk', () => {
    render(<Label data-testid="required-label" data-required>Required Field</Label>)
    const label = screen.getByTestId('required-label')
    
    expect(label).toHaveClass('[&[data-required]]:after:content-[\'*\']')
    expect(label).toHaveClass('[&[data-required]]:after:text-destructive')
  })
})
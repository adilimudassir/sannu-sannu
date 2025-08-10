import { render, screen } from '@testing-library/react'
import { describe, it, expect } from 'vitest'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../card'
import { Badge } from '../badge'
import { Separator } from '../separator'

describe('Card Component with Blue Theme', () => {
  it('renders card with proper blue theme classes', () => {
    render(
      <Card data-testid="card">
        <CardHeader>
          <CardTitle>Test Card</CardTitle>
          <CardDescription>Test description</CardDescription>
        </CardHeader>
        <CardContent>
          <p>Card content</p>
        </CardContent>
        <CardFooter>
          <p>Card footer</p>
        </CardFooter>
      </Card>
    )

    const card = screen.getByTestId('card')
    expect(card).toHaveClass('bg-card', 'text-card-foreground', 'border-border')
    expect(card).toHaveClass('hover:shadow-md', 'transition-shadow', 'duration-200')
  })

  it('renders card header with proper structure', () => {
    render(
      <Card>
        <CardHeader data-testid="card-header">
          <CardTitle data-testid="card-title">Test Title</CardTitle>
          <CardDescription data-testid="card-description">Test Description</CardDescription>
        </CardHeader>
      </Card>
    )

    expect(screen.getByTestId('card-header')).toBeInTheDocument()
    expect(screen.getByTestId('card-title')).toHaveTextContent('Test Title')
    expect(screen.getByTestId('card-description')).toHaveTextContent('Test Description')
    expect(screen.getByTestId('card-description')).toHaveClass('text-muted-foreground')
  })

  it('renders card content and footer', () => {
    render(
      <Card>
        <CardContent data-testid="card-content">Content</CardContent>
        <CardFooter data-testid="card-footer">Footer</CardFooter>
      </Card>
    )

    expect(screen.getByTestId('card-content')).toBeInTheDocument()
    expect(screen.getByTestId('card-footer')).toBeInTheDocument()
  })
})

describe('Badge Component with Blue Theme', () => {
  it('renders default badge with blue primary colors', () => {
    render(<Badge data-testid="badge">Default Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('bg-primary', 'text-primary-foreground')
    expect(badge).toHaveTextContent('Default Badge')
  })

  it('renders secondary badge with blue secondary colors', () => {
    render(<Badge variant="secondary" data-testid="badge">Secondary Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('bg-secondary', 'text-secondary-foreground')
  })

  it('renders blue variant badge', () => {
    render(<Badge variant="blue" data-testid="badge">Blue Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('bg-primary', 'text-primary-foreground')
  })

  it('renders blue outline variant badge', () => {
    render(<Badge variant="blue-outline" data-testid="badge">Blue Outline Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('border-primary', 'text-primary')
  })

  it('renders muted variant badge', () => {
    render(<Badge variant="muted" data-testid="badge">Muted Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('bg-muted', 'text-muted-foreground')
  })

  it('renders outline variant badge', () => {
    render(<Badge variant="outline" data-testid="badge">Outline Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('text-foreground')
  })

  it('renders destructive variant badge', () => {
    render(<Badge variant="destructive" data-testid="badge">Destructive Badge</Badge>)
    
    const badge = screen.getByTestId('badge')
    expect(badge).toHaveClass('bg-destructive', 'text-white')
  })
})

describe('Separator Component with Blue Theme', () => {
  it('renders horizontal separator with blue border color', () => {
    render(<Separator data-testid="separator" />)
    
    const separator = screen.getByTestId('separator')
    expect(separator).toHaveClass('bg-border')
    expect(separator).toHaveAttribute('data-orientation', 'horizontal')
  })

  it('renders vertical separator with blue border color', () => {
    render(<Separator orientation="vertical" data-testid="separator" />)
    
    const separator = screen.getByTestId('separator')
    expect(separator).toHaveClass('bg-border')
    expect(separator).toHaveAttribute('data-orientation', 'vertical')
  })

  it('applies custom className', () => {
    render(<Separator className="custom-class" data-testid="separator" />)
    
    const separator = screen.getByTestId('separator')
    expect(separator).toHaveClass('custom-class', 'bg-border')
  })
})

describe('Component Integration with Blue Theme', () => {
  it('renders card with badge and separator using consistent blue theme', () => {
    render(
      <Card data-testid="integration-card">
        <CardHeader>
          <CardTitle>Integration Test</CardTitle>
          <Badge variant="blue">Blue Badge</Badge>
        </CardHeader>
        <Separator data-testid="integration-separator" />
        <CardContent>
          <p>Content with blue theme</p>
        </CardContent>
      </Card>
    )

    const card = screen.getByTestId('integration-card')
    const separator = screen.getByTestId('integration-separator')
    const badge = screen.getByText('Blue Badge')

    // Verify all components use consistent blue theme classes
    expect(card).toHaveClass('bg-card', 'border-border')
    expect(separator).toHaveClass('bg-border')
    expect(badge).toHaveClass('bg-primary', 'text-primary-foreground')
  })
})
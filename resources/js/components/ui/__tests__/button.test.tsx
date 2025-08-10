import { render, screen } from '@testing-library/react'
import { describe, it, expect } from 'vitest'
import { Button, buttonVariants } from '../button'

describe('Button Component', () => {
  describe('Blue Theme Color Applications', () => {
    it('applies default (primary) variant with blue theme colors', () => {
      render(<Button>Primary Button</Button>)
      const button = screen.getByRole('button', { name: 'Primary Button' })
      
      // Check that the button has the primary background class
      expect(button).toHaveClass('bg-primary')
      expect(button).toHaveClass('text-primary-foreground')
      expect(button).toHaveClass('hover:bg-primary/90')
    })

    it('applies secondary variant with blue theme colors', () => {
      render(<Button variant="secondary">Secondary Button</Button>)
      const button = screen.getByRole('button', { name: 'Secondary Button' })
      
      expect(button).toHaveClass('bg-secondary')
      expect(button).toHaveClass('text-secondary-foreground')
      expect(button).toHaveClass('hover:bg-secondary/80')
    })

    it('applies outline variant with blue theme colors', () => {
      render(<Button variant="outline">Outline Button</Button>)
      const button = screen.getByRole('button', { name: 'Outline Button' })
      
      expect(button).toHaveClass('border-input')
      expect(button).toHaveClass('bg-background')
      expect(button).toHaveClass('hover:bg-accent')
      expect(button).toHaveClass('hover:text-accent-foreground')
      expect(button).toHaveClass('hover:border-accent')
    })

    it('applies ghost variant with blue theme colors', () => {
      render(<Button variant="ghost">Ghost Button</Button>)
      const button = screen.getByRole('button', { name: 'Ghost Button' })
      
      expect(button).toHaveClass('hover:bg-accent')
      expect(button).toHaveClass('hover:text-accent-foreground')
    })

    it('applies link variant with blue theme colors', () => {
      render(<Button variant="link">Link Button</Button>)
      const button = screen.getByRole('button', { name: 'Link Button' })
      
      expect(button).toHaveClass('text-primary')
      expect(button).toHaveClass('hover:text-primary/80')
    })

    it('applies destructive variant while maintaining blue focus states', () => {
      render(<Button variant="destructive">Delete</Button>)
      const button = screen.getByRole('button', { name: 'Delete' })
      
      expect(button).toHaveClass('bg-destructive')
      expect(button).toHaveClass('text-white')
      expect(button).toHaveClass('hover:bg-destructive/90')
    })
  })

  describe('Blue Theme Interactive States', () => {
    it('applies blue-themed hover states without transform effects', () => {
      render(<Button>Hover Test</Button>)
      const button = screen.getByRole('button', { name: 'Hover Test' })
      
      // Check for hover shadow effects (no transform animations)
      expect(button).toHaveClass('hover:shadow-md')
      expect(button).toHaveClass('hover:bg-primary/90')
      // Active shadow states removed for smoother interaction
    })

    it('applies blue-themed focus states', () => {
      render(<Button>Focus Test</Button>)
      const button = screen.getByRole('button', { name: 'Focus Test' })
      
      expect(button).toHaveClass('focus-visible:ring-primary/20')
      expect(button).toHaveClass('focus-visible:border-ring')
      expect(button).toHaveClass('focus-visible:ring-[3px]')
    })

    it('applies proper focus states for different variants', () => {
      const variants = ['secondary', 'outline', 'ghost', 'link'] as const
      
      variants.forEach(variant => {
        const { unmount } = render(<Button variant={variant}>{variant} Button</Button>)
        const button = screen.getByRole('button', { name: `${variant} Button` })
        
        if (variant === 'secondary') {
          expect(button).toHaveClass('focus-visible:ring-secondary/20')
        } else if (variant === 'outline' || variant === 'ghost') {
          expect(button).toHaveClass('focus-visible:ring-accent/20')
        } else if (variant === 'link') {
          expect(button).toHaveClass('focus-visible:ring-primary/20')
        }
        
        unmount()
      })
    })
  })

  describe('Button Sizes', () => {
    it('applies correct size classes', () => {
      const sizes = ['sm', 'default', 'lg', 'icon'] as const
      
      sizes.forEach(size => {
        const { unmount } = render(<Button size={size}>Size Test</Button>)
        const button = screen.getByRole('button', { name: 'Size Test' })
        
        switch (size) {
          case 'sm':
            expect(button).toHaveClass('h-8')
            break
          case 'default':
            expect(button).toHaveClass('h-9')
            break
          case 'lg':
            expect(button).toHaveClass('h-10')
            break
          case 'icon':
            expect(button).toHaveClass('size-9')
            break
        }
        
        unmount()
      })
    })
  })

  describe('Accessibility and States', () => {
    it('handles disabled state correctly', () => {
      render(<Button disabled>Disabled Button</Button>)
      const button = screen.getByRole('button', { name: 'Disabled Button' })
      
      expect(button).toBeDisabled()
      expect(button).toHaveClass('disabled:pointer-events-none')
      expect(button).toHaveClass('disabled:opacity-50')
    })

    it('supports asChild prop', () => {
      render(
        <Button asChild>
          <a href="/test">Link as Button</a>
        </Button>
      )
      
      const link = screen.getByRole('link', { name: 'Link as Button' })
      expect(link).toHaveAttribute('href', '/test')
      expect(link).toHaveClass('bg-primary') // Should still have button styles
    })

    it('applies aria-invalid styling', () => {
      render(<Button aria-invalid>Invalid Button</Button>)
      const button = screen.getByRole('button', { name: 'Invalid Button' })
      
      expect(button).toHaveClass('aria-invalid:ring-destructive/20')
      expect(button).toHaveClass('aria-invalid:border-destructive')
    })
  })

  describe('Button Variants Function', () => {
    it('generates correct classes for default variant', () => {
      const classes = buttonVariants()
      expect(classes).toContain('bg-primary')
      expect(classes).toContain('text-primary-foreground')
      expect(classes).toContain('h-9')
    })

    it('generates correct classes for custom variant and size', () => {
      const classes = buttonVariants({ variant: 'outline', size: 'lg' })
      expect(classes).toContain('border-input')
      expect(classes).toContain('bg-background')
      expect(classes).toContain('h-10')
    })

    it('allows custom className override', () => {
      const classes = buttonVariants({ className: 'custom-class' })
      expect(classes).toContain('custom-class')
    })
  })

  describe('Blue Theme Transition Effects', () => {
    it('includes proper transition classes', () => {
      render(<Button>Transition Test</Button>)
      const button = screen.getByRole('button', { name: 'Transition Test' })
      
      expect(button).toHaveClass('transition-[color,background-color,border-color,box-shadow]')
    })

    it('applies shadow effects for elevated interactions', () => {
      render(<Button variant="outline">Shadow Test</Button>)
      const button = screen.getByRole('button', { name: 'Shadow Test' })
      
      expect(button).toHaveClass('shadow-xs')
      expect(button).toHaveClass('hover:shadow-md')
      // Active shadow states removed for smoother interaction
    })
  })
})
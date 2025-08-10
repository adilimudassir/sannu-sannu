import { render } from '@testing-library/react'
import { Alert, AlertTitle, AlertDescription } from '../alert'
import { Avatar, AvatarImage, AvatarFallback } from '../avatar'
import { Skeleton } from '../skeleton'
import { Toggle } from '../toggle'
import { ToggleGroup, ToggleGroupItem } from '../toggle-group'

describe('Blue Theme Components', () => {
  describe('Alert', () => {
    it('renders default alert with blue theme classes', () => {
      const { container } = render(
        <Alert>
          <AlertTitle>Test Alert</AlertTitle>
          <AlertDescription>This is a test alert</AlertDescription>
        </Alert>
      )
      
      const alert = container.querySelector('[data-slot="alert"]')
      expect(alert).toHaveClass('bg-background', 'text-foreground', 'border-border')
    })

    it('renders info alert with blue theme classes', () => {
      const { container } = render(
        <Alert variant="info">
          <AlertTitle>Info Alert</AlertTitle>
          <AlertDescription>This is an info alert</AlertDescription>
        </Alert>
      )
      
      const alert = container.querySelector('[data-slot="alert"]')
      expect(alert).toHaveClass('bg-primary/10', 'text-primary', 'border-primary/20')
    })

    it('renders destructive alert with proper styling', () => {
      const { container } = render(
        <Alert variant="destructive">
          <AlertTitle>Error Alert</AlertTitle>
          <AlertDescription>This is an error alert</AlertDescription>
        </Alert>
      )
      
      const alert = container.querySelector('[data-slot="alert"]')
      expect(alert).toHaveClass('bg-destructive/10', 'text-destructive', 'border-destructive/20')
    })

    it('renders success alert with proper styling', () => {
      const { container } = render(
        <Alert variant="success">
          <AlertTitle>Success Alert</AlertTitle>
          <AlertDescription>This is a success alert</AlertDescription>
        </Alert>
      )
      
      const alert = container.querySelector('[data-slot="alert"]')
      expect(alert).toHaveClass('bg-emerald-50', 'text-emerald-900', 'border-emerald-200')
    })

    it('renders warning alert with proper styling', () => {
      const { container } = render(
        <Alert variant="warning">
          <AlertTitle>Warning Alert</AlertTitle>
          <AlertDescription>This is a warning alert</AlertDescription>
        </Alert>
      )
      
      const alert = container.querySelector('[data-slot="alert"]')
      expect(alert).toHaveClass('bg-amber-50', 'text-amber-900', 'border-amber-200')
    })
  })

  describe('Avatar', () => {
    it('renders avatar with blue border', () => {
      const { container } = render(
        <Avatar>
          <AvatarImage src="/test.jpg" alt="Test" />
          <AvatarFallback>TU</AvatarFallback>
        </Avatar>
      )
      
      const avatar = container.querySelector('[data-slot="avatar"]')
      expect(avatar).toHaveClass('border-2', 'border-border')
    })

    it('renders avatar fallback with blue theme', () => {
      const { container } = render(
        <Avatar>
          <AvatarFallback>TU</AvatarFallback>
        </Avatar>
      )
      
      const fallback = container.querySelector('[data-slot="avatar-fallback"]')
      expect(fallback).toHaveClass('bg-primary/10', 'text-primary', 'font-medium')
    })
  })

  describe('Skeleton', () => {
    it('renders skeleton with blue-tinted loading state', () => {
      const { container } = render(<Skeleton className="h-4 w-20" />)
      
      const skeleton = container.querySelector('[data-slot="skeleton"]')
      expect(skeleton).toHaveClass('bg-primary/10', 'animate-pulse', 'relative', 'overflow-hidden')
    })
  })

  describe('Toggle', () => {
    it('renders toggle with blue active states', () => {
      const { container } = render(<Toggle>Toggle</Toggle>)
      
      const toggle = container.querySelector('[data-slot="toggle"]')
      expect(toggle).toHaveClass('data-[state=on]:bg-primary', 'data-[state=on]:text-primary-foreground')
    })

    it('renders outline toggle with blue border when active', () => {
      const { container } = render(<Toggle variant="outline">Toggle</Toggle>)
      
      const toggle = container.querySelector('[data-slot="toggle"]')
      expect(toggle).toHaveClass('border', 'border-input', 'data-[state=on]:border-primary')
    })
  })

  describe('ToggleGroup', () => {
    it('renders toggle group with proper styling', () => {
      const { container } = render(
        <ToggleGroup type="single">
          <ToggleGroupItem value="a">A</ToggleGroupItem>
          <ToggleGroupItem value="b">B</ToggleGroupItem>
        </ToggleGroup>
      )
      
      const group = container.querySelector('[data-slot="toggle-group"]')
      expect(group).toHaveClass('flex', 'items-center', 'rounded-md')
    })

    it('renders outline toggle group with blue border', () => {
      const { container } = render(
        <ToggleGroup type="single" variant="outline">
          <ToggleGroupItem value="a">A</ToggleGroupItem>
          <ToggleGroupItem value="b">B</ToggleGroupItem>
        </ToggleGroup>
      )
      
      const group = container.querySelector('[data-slot="toggle-group"]')
      expect(group).toHaveClass('data-[variant=outline]:border', 'data-[variant=outline]:border-border')
    })
  })
})
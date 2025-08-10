import { Button } from '../button'

/**
 * Demo component to visually test button variants with blue theme
 * This can be used in Storybook or as a standalone demo page
 */
export function ButtonDemo() {
  return (
    <div className="p-8 space-y-8 bg-background">
      <div className="space-y-4">
        <h2 className="text-2xl font-semibold text-foreground">Button Variants - Blue Theme</h2>
        
        <div className="space-y-4">
          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Primary (Default)</h3>
            <div className="flex gap-4 items-center">
              <Button size="sm">Small Primary</Button>
              <Button>Default Primary</Button>
              <Button size="lg">Large Primary</Button>
              <Button disabled>Disabled Primary</Button>
            </div>
          </div>

          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Secondary</h3>
            <div className="flex gap-4 items-center">
              <Button variant="secondary" size="sm">Small Secondary</Button>
              <Button variant="secondary">Default Secondary</Button>
              <Button variant="secondary" size="lg">Large Secondary</Button>
              <Button variant="secondary" disabled>Disabled Secondary</Button>
            </div>
          </div>

          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Outline</h3>
            <div className="flex gap-4 items-center">
              <Button variant="outline" size="sm">Small Outline</Button>
              <Button variant="outline">Default Outline</Button>
              <Button variant="outline" size="lg">Large Outline</Button>
              <Button variant="outline" disabled>Disabled Outline</Button>
            </div>
          </div>

          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Ghost</h3>
            <div className="flex gap-4 items-center">
              <Button variant="ghost" size="sm">Small Ghost</Button>
              <Button variant="ghost">Default Ghost</Button>
              <Button variant="ghost" size="lg">Large Ghost</Button>
              <Button variant="ghost" disabled>Disabled Ghost</Button>
            </div>
          </div>

          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Link</h3>
            <div className="flex gap-4 items-center">
              <Button variant="link" size="sm">Small Link</Button>
              <Button variant="link">Default Link</Button>
              <Button variant="link" size="lg">Large Link</Button>
              <Button variant="link" disabled>Disabled Link</Button>
            </div>
          </div>

          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Destructive</h3>
            <div className="flex gap-4 items-center">
              <Button variant="destructive" size="sm">Small Destructive</Button>
              <Button variant="destructive">Default Destructive</Button>
              <Button variant="destructive" size="lg">Large Destructive</Button>
              <Button variant="destructive" disabled>Disabled Destructive</Button>
            </div>
          </div>

          <div className="space-y-2">
            <h3 className="text-lg font-medium text-foreground">Icon Buttons</h3>
            <div className="flex gap-4 items-center">
              <Button size="icon">+</Button>
              <Button variant="outline" size="icon">×</Button>
              <Button variant="ghost" size="icon">?</Button>
              <Button variant="destructive" size="icon">!</Button>
            </div>
          </div>
        </div>

        <div className="mt-8 p-4 bg-muted rounded-lg">
          <h3 className="text-lg font-medium text-foreground mb-2">Interactive Features</h3>
          <p className="text-muted-foreground text-sm mb-4">
            Hover over buttons to see the blue-themed hover effects with subtle lift animation.
            Focus on buttons (using Tab key) to see the blue focus rings.
          </p>
          <div className="flex gap-4">
            <Button>Hover & Focus Test</Button>
            <Button variant="outline">Outline Hover Test</Button>
            <Button variant="secondary">Secondary Hover Test</Button>
          </div>
        </div>
      </div>
    </div>
  )
}
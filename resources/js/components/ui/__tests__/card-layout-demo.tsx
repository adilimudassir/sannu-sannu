import React from 'react'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../card'
import { Badge } from '../badge'
import { Separator } from '../separator'

/**
 * Demo component showcasing the blue theme implementation for card and layout components
 * This component demonstrates:
 * - Card components with blue theme colors
 * - Badge variants with blue color scheme
 * - Separator with blue border colors
 * - Proper contrast ratios and accessibility
 */
export function CardLayoutDemo() {
  return (
    <div className="p-8 space-y-8 bg-background">
      <div className="space-y-4">
        <h2 className="text-2xl font-bold text-foreground">Card and Layout Components - Blue Theme Demo</h2>
        
        {/* Basic Card Demo */}
        <Card className="max-w-md">
          <CardHeader>
            <CardTitle>Blue Theme Card</CardTitle>
            <CardDescription>
              This card demonstrates the blue theme implementation with proper contrast ratios.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              The card uses the blue palette colors: Deep Navy, Royal Blue, Modern Sky, Cool Steel, and Soft Ice.
            </p>
          </CardContent>
          <CardFooter>
            <Badge variant="default">Primary Badge</Badge>
          </CardFooter>
        </Card>

        {/* Badge Variants Demo */}
        <Card className="max-w-2xl">
          <CardHeader>
            <CardTitle>Badge Variants</CardTitle>
            <CardDescription>
              Different badge variants using the blue theme color palette.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex flex-wrap gap-2">
              <Badge variant="default">Default (Primary)</Badge>
              <Badge variant="secondary">Secondary</Badge>
              <Badge variant="blue">Blue</Badge>
              <Badge variant="blue-outline">Blue Outline</Badge>
              <Badge variant="muted">Muted</Badge>
              <Badge variant="outline">Outline</Badge>
              <Badge variant="destructive">Destructive</Badge>
            </div>
          </CardContent>
        </Card>

        {/* Separator Demo */}
        <Card className="max-w-2xl">
          <CardHeader>
            <CardTitle>Separator Component</CardTitle>
            <CardDescription>
              Horizontal and vertical separators using blue border colors.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <p className="text-sm text-muted-foreground mb-2">Horizontal Separator:</p>
              <Separator />
            </div>
            <div className="flex items-center space-x-4">
              <p className="text-sm text-muted-foreground">Vertical Separator:</p>
              <Separator orientation="vertical" className="h-8" />
              <p className="text-sm text-muted-foreground">Between content</p>
            </div>
          </CardContent>
        </Card>

        {/* Complex Layout Demo */}
        <Card className="max-w-3xl">
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Complex Layout Example</CardTitle>
                <CardDescription>
                  A more complex card layout showcasing multiple components working together.
                </CardDescription>
              </div>
              <Badge variant="blue">Featured</Badge>
            </div>
          </CardHeader>
          <Separator />
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <h4 className="font-semibold text-foreground mb-2">Features</h4>
                <ul className="space-y-1 text-sm text-muted-foreground">
                  <li>• Blue theme integration</li>
                  <li>• Proper contrast ratios</li>
                  <li>• Accessibility compliant</li>
                  <li>• Responsive design</li>
                </ul>
              </div>
              <div>
                <h4 className="font-semibold text-foreground mb-2">Status</h4>
                <div className="flex flex-wrap gap-2">
                  <Badge variant="blue">Active</Badge>
                  <Badge variant="muted">Updated</Badge>
                  <Badge variant="blue-outline">Verified</Badge>
                </div>
              </div>
            </div>
          </CardContent>
          <Separator />
          <CardFooter className="justify-between">
            <p className="text-xs text-muted-foreground">Last updated: Today</p>
            <Badge variant="outline">View Details</Badge>
          </CardFooter>
        </Card>

        {/* Dark Mode Note */}
        <Card className="max-w-2xl border-primary/20">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <span>🌙</span>
              Dark Mode Support
            </CardTitle>
            <CardDescription>
              All components automatically adapt to dark mode using the blue theme color mappings.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground">
              The blue theme includes both light and dark mode variants with proper contrast ratios 
              for accessibility compliance (WCAG AA standards).
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

export default CardLayoutDemo
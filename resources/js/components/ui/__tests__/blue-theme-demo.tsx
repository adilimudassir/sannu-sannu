import { Alert, AlertTitle, AlertDescription } from '../alert'
import { Avatar, AvatarImage, AvatarFallback } from '../avatar'
import { Skeleton } from '../skeleton'
import { Toggle } from '../toggle'
import { ToggleGroup, ToggleGroupItem } from '../toggle-group'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../card'
import { Badge } from '../badge'

export function BlueThemeDemo() {
  return (
    <div className="p-8 space-y-8 max-w-4xl mx-auto">
      <div className="space-y-4">
        <h2 className="text-2xl font-bold text-foreground">Blue Theme Components Demo</h2>
        <p className="text-muted-foreground">
          Showcasing the updated UI components with the modern minimalist blue theme.
        </p>
      </div>

      {/* Alert Variants */}
      <Card>
        <CardHeader>
          <CardTitle>Alert Components</CardTitle>
          <CardDescription>Different alert variants with blue theme styling</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <Alert>
            <AlertTitle>Default Alert</AlertTitle>
            <AlertDescription>
              This is a default alert using the blue theme colors.
            </AlertDescription>
          </Alert>

          <Alert variant="info">
            <AlertTitle>Info Alert</AlertTitle>
            <AlertDescription>
              This is an info alert with blue primary colors.
            </AlertDescription>
          </Alert>

          <Alert variant="success">
            <AlertTitle>Success Alert</AlertTitle>
            <AlertDescription>
              This is a success alert with green colors.
            </AlertDescription>
          </Alert>

          <Alert variant="warning">
            <AlertTitle>Warning Alert</AlertTitle>
            <AlertDescription>
              This is a warning alert with amber colors.
            </AlertDescription>
          </Alert>

          <Alert variant="destructive">
            <AlertTitle>Error Alert</AlertTitle>
            <AlertDescription>
              This is an error alert with red colors.
            </AlertDescription>
          </Alert>
        </CardContent>
      </Card>

      {/* Avatar Components */}
      <Card>
        <CardHeader>
          <CardTitle>Avatar Components</CardTitle>
          <CardDescription>Avatars with blue border styling</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center space-x-4">
            <Avatar className="h-12 w-12">
              <AvatarImage src="https://github.com/shadcn.png" alt="@shadcn" />
              <AvatarFallback>CN</AvatarFallback>
            </Avatar>
            <Avatar className="h-16 w-16">
              <AvatarFallback>JD</AvatarFallback>
            </Avatar>
            <Avatar className="h-20 w-20">
              <AvatarFallback>AB</AvatarFallback>
            </Avatar>
          </div>
        </CardContent>
      </Card>

      {/* Skeleton Components */}
      <Card>
        <CardHeader>
          <CardTitle>Skeleton Components</CardTitle>
          <CardDescription>Loading states with blue-tinted shimmer effect</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Skeleton className="h-4 w-[250px]" />
            <Skeleton className="h-4 w-[200px]" />
            <Skeleton className="h-4 w-[150px]" />
          </div>
          <div className="flex items-center space-x-4">
            <Skeleton className="h-12 w-12 rounded-full" />
            <div className="space-y-2">
              <Skeleton className="h-4 w-[200px]" />
              <Skeleton className="h-4 w-[160px]" />
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Toggle Components */}
      <Card>
        <CardHeader>
          <CardTitle>Toggle Components</CardTitle>
          <CardDescription>Toggle buttons with blue active states</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="space-y-2">
            <h4 className="text-sm font-medium">Default Toggles</h4>
            <div className="flex items-center space-x-2">
              <Toggle>Toggle 1</Toggle>
              <Toggle defaultPressed>Toggle 2 (Active)</Toggle>
              <Toggle disabled>Disabled</Toggle>
            </div>
          </div>

          <div className="space-y-2">
            <h4 className="text-sm font-medium">Outline Toggles</h4>
            <div className="flex items-center space-x-2">
              <Toggle variant="outline">Outline 1</Toggle>
              <Toggle variant="outline" defaultPressed>Outline 2 (Active)</Toggle>
              <Toggle variant="outline" disabled>Disabled</Toggle>
            </div>
          </div>

          <div className="space-y-2">
            <h4 className="text-sm font-medium">Toggle Group</h4>
            <ToggleGroup type="single" defaultValue="center">
              <ToggleGroupItem value="left">Left</ToggleGroupItem>
              <ToggleGroupItem value="center">Center</ToggleGroupItem>
              <ToggleGroupItem value="right">Right</ToggleGroupItem>
            </ToggleGroup>
          </div>

          <div className="space-y-2">
            <h4 className="text-sm font-medium">Outline Toggle Group</h4>
            <ToggleGroup type="multiple" variant="outline">
              <ToggleGroupItem value="bold">Bold</ToggleGroupItem>
              <ToggleGroupItem value="italic">Italic</ToggleGroupItem>
              <ToggleGroupItem value="underline">Underline</ToggleGroupItem>
            </ToggleGroup>
          </div>
        </CardContent>
      </Card>

      {/* Color Palette Reference */}
      <Card>
        <CardHeader>
          <CardTitle>Blue Theme Color Palette</CardTitle>
          <CardDescription>The modern minimalist blue color system</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div className="space-y-2">
              <div className="h-16 w-full rounded-md bg-[#0D1B2A] border"></div>
              <div className="text-xs">
                <div className="font-medium">Deep Navy</div>
                <div className="text-muted-foreground">#0D1B2A</div>
              </div>
            </div>
            <div className="space-y-2">
              <div className="h-16 w-full rounded-md bg-[#1B263B] border"></div>
              <div className="text-xs">
                <div className="font-medium">Royal Blue</div>
                <div className="text-muted-foreground">#1B263B</div>
              </div>
            </div>
            <div className="space-y-2">
              <div className="h-16 w-full rounded-md bg-[#415A77] border"></div>
              <div className="text-xs">
                <div className="font-medium">Modern Sky</div>
                <div className="text-muted-foreground">#415A77</div>
              </div>
            </div>
            <div className="space-y-2">
              <div className="h-16 w-full rounded-md bg-[#778DA9] border"></div>
              <div className="text-xs">
                <div className="font-medium">Cool Steel</div>
                <div className="text-muted-foreground">#778DA9</div>
              </div>
            </div>
            <div className="space-y-2">
              <div className="h-16 w-full rounded-md bg-[#E0E1DD] border"></div>
              <div className="text-xs">
                <div className="font-medium">Soft Ice</div>
                <div className="text-muted-foreground">#E0E1DD</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
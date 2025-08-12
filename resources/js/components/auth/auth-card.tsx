import * as React from "react"
import { cn } from "@/lib/utils"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"

interface AuthCardProps extends React.ComponentProps<typeof Card> {
  title?: string
  description?: string
  children: React.ReactNode
}

function AuthCard({ title, description, children, className, ...props }: AuthCardProps) {
  return (
    <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
      <div className="w-full max-w-md">
        <Card
          className={cn(
            "w-full shadow-lg border-border/50",
            className
          )}
          {...props}
        >
          {(title || description) && (
            <CardHeader className="text-center space-y-2">
              {title && (
                <CardTitle className="text-xl font-medium">
                  {title}
                </CardTitle>
              )}
              {description && (
                <CardDescription className="text-sm text-muted-foreground">
                  {description}
                </CardDescription>
              )}
            </CardHeader>
          )}
          <CardContent className="space-y-6">
            {children}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

export { AuthCard }
import * as React from "react"
import { cn } from "@/lib/utils"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"

interface AuthCardProps extends React.ComponentProps<typeof Card> {
  title?: string
  description?: string
  children: React.ReactNode
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full'
}

function AuthCard({ title, description, children, className, size = 'md', ...props }: AuthCardProps) {
  const sizeClasses = {
    sm: 'max-w-md',      // 28rem / 448px
    md: 'max-w-xl',      // 36rem / 576px
    lg: 'max-w-3xl',     // 48rem / 768px
    xl: 'max-w-5xl',     // 64rem / 1024px
    '2xl': 'max-w-7xl',  // 80rem / 1280px
    full: 'max-w-[100rem]' // 100rem / 1600px
  }

  return (
    <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
      <div className={cn("w-full", !className?.includes("max-w-") && sizeClasses[size])}>
        <Card
          className={cn(
            "w-full shadow-2xl border-border/50",
            className
          )}
          {...props}
        >
          {(title || description) && (
            <CardHeader className="text-center space-y-4 py-8">
              {title && (
                <CardTitle className="text-2xl font-semibold tracking-tight">
                  {title}
                </CardTitle>
              )}
              {description && (
                <CardDescription className="text-md text-muted-foreground max-w-4xl mx-auto">
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
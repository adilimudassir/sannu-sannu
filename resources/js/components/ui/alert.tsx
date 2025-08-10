import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const alertVariants = cva(
  "relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current",
  {
    variants: {
      variant: {
        default: "bg-background text-foreground border-border",
        destructive:
          "bg-destructive/10 text-destructive border-destructive/20 [&>svg]:text-destructive *:data-[slot=alert-description]:text-destructive/80",
        info: "bg-primary/10 text-primary border-primary/20 [&>svg]:text-primary *:data-[slot=alert-description]:text-primary/80",
        success: "bg-emerald-50 text-emerald-900 border-emerald-200 [&>svg]:text-emerald-600 *:data-[slot=alert-description]:text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-100 dark:border-emerald-900/50 dark:[&>svg]:text-emerald-400 dark:*:data-[slot=alert-description]:text-emerald-200",
        warning: "bg-amber-50 text-amber-900 border-amber-200 [&>svg]:text-amber-600 *:data-[slot=alert-description]:text-amber-800 dark:bg-amber-950/50 dark:text-amber-100 dark:border-amber-900/50 dark:[&>svg]:text-amber-400 dark:*:data-[slot=alert-description]:text-amber-200",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Alert({
  className,
  variant,
  ...props
}: React.ComponentProps<"div"> & VariantProps<typeof alertVariants>) {
  return (
    <div
      data-slot="alert"
      role="alert"
      className={cn(alertVariants({ variant }), className)}
      {...props}
    />
  )
}

function AlertTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-title"
      className={cn(
        "col-start-2 line-clamp-1 min-h-4 font-medium tracking-tight",
        className
      )}
      {...props}
    />
  )
}

function AlertDescription({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-description"
      className={cn(
        "text-muted-foreground col-start-2 grid justify-items-start gap-1 text-sm [&_p]:leading-relaxed",
        className
      )}
      {...props}
    />
  )
}

export { Alert, AlertTitle, AlertDescription }

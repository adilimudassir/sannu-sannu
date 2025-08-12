import * as React from "react"
import { cn } from "@/lib/utils"
import { Button, buttonVariants } from "@/components/ui/button"
import { LoaderCircle } from "lucide-react"
import { type VariantProps } from "class-variance-authority"

interface AuthButtonProps
  extends React.ComponentProps<typeof Button>,
    VariantProps<typeof buttonVariants> {
  loading?: boolean
  loadingText?: string
}

const AuthButton = React.forwardRef<HTMLButtonElement, AuthButtonProps>(
  ({ 
    className, 
    children, 
    loading = false, 
    loadingText, 
    disabled, 
    variant = "default",
    size = "default",
    ...props 
  }, ref) => {
    const isDisabled = disabled || loading

    return (
      <Button
        ref={ref}
        className={cn(
          // Enhanced focus states for auth forms
          "focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-0",
          // Loading state styling
          loading && "cursor-not-allowed",
          className
        )}
        disabled={isDisabled}
        variant={variant}
        size={size}
        {...props}
      >
        {loading && (
          <LoaderCircle className="h-4 w-4 animate-spin" />
        )}
        {loading && loadingText ? loadingText : children}
      </Button>
    )
  }
)

AuthButton.displayName = "AuthButton"

export { AuthButton }
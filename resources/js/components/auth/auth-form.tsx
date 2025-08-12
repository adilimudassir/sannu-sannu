import * as React from "react"
import { useForm } from "@inertiajs/react"
import type { InertiaFormProps } from "@inertiajs/react"
import { cn } from "@/lib/utils"
import InputError from "@/components/input-error"

interface AuthFormProps<TForm extends Record<string, any>> extends React.ComponentProps<"form"> {
  initialData: TForm
  onSubmit: (data: TForm, form: InertiaFormProps<TForm>) => void
  children: (form: InertiaFormProps<TForm>) => React.ReactNode
  className?: string
}

function AuthForm<TForm extends Record<string, any>>({
  initialData,
  onSubmit,
  children,
  className,
  ...props
}: AuthFormProps<TForm>) {
  const form = useForm<TForm>(initialData)

  const handleSubmit: React.FormEventHandler = (e) => {
    e.preventDefault()
    onSubmit(form.data, form)
  }

  return (
    <form
      className={cn("space-y-6", className)}
      onSubmit={handleSubmit}
      {...props}
    >
      {children(form)}
    </form>
  )
}

interface AuthFormFieldProps {
  label: string
  name: string
  error?: string
  children: React.ReactNode
  required?: boolean
  className?: string
}

function AuthFormField({
  label,
  name,
  error,
  children,
  required = false,
  className
}: AuthFormFieldProps) {
  return (
    <div className={cn("space-y-2", className)}>
      <label
        htmlFor={name}
        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
      >
        {label}
        {required && <span className="text-destructive ml-1">*</span>}
      </label>
      {children}
      <InputError message={error} />
    </div>
  )
}

export { AuthForm, AuthFormField }
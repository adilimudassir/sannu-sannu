import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/input-error';
import { formatCurrency } from '@/lib/formatters';
import { Plus, X, Upload } from 'lucide-react';
import type { Product } from '@/types';

interface ProductManagerProps {
    products: Product[];
    onChange: (products: Product[]) => void;
    errors?: Record<string, string>;
}

export default function ProductManager({ products, onChange, errors = {} }: ProductManagerProps) {
    const addProduct = () => {
        onChange([
            ...products,
            { name: '', description: '', price: 0, sort_order: products.length }
        ]);
    };

    const removeProduct = (index: number) => {
        if (products.length > 1) {
            onChange(products.filter((_, i) => i !== index));
        }
    };

    const updateProduct = (index: number, field: keyof Product, value: any) => {
        const updatedProducts = [...products];
        updatedProducts[index] = { ...updatedProducts[index], [field]: value };
        onChange(updatedProducts);
    };

    const handleImageUpload = (index: number, file: File) => {
        updateProduct(index, 'image', file);
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            updateProduct(index, 'image_url', e.target?.result as string);
        };
        reader.readAsDataURL(file);
    };

    const totalAmount = products.reduce((sum, product) => sum + (product.price || 0), 0);

    return (
        <div className="space-y-6">
            {products.map((product, index) => (
                <div key={index} className="border border-border rounded-lg p-6 space-y-4 bg-card shadow-sm">
                    <div className="flex items-center justify-between pb-2 border-b border-border">
                        <h4 className="font-semibold text-lg text-foreground">Product {index + 1}</h4>
                        {products.length > 1 && (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => removeProduct(index)}
                                className="text-muted-foreground hover:text-destructive hover:border-destructive"
                            >
                                <X className="h-4 w-4" />
                            </Button>
                        )}
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div className="lg:col-span-2 space-y-2">
                            <Label htmlFor={`product_name_${index}`} data-required>Name</Label>
                            <Input
                                id={`product_name_${index}`}
                                value={product.name}
                                onChange={(e) => updateProduct(index, 'name', e.target.value)}
                                placeholder="Product name"
                                aria-invalid={!!errors[`products.${index}.name`]}
                            />
                            <InputError message={errors[`products.${index}.name`]} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor={`product_price_${index}`} data-required>Price</Label>
                            <Input
                                id={`product_price_${index}`}
                                type="number"
                                min="0.01"
                                step="0.01"
                                value={product.price || ''}
                                onChange={(e) => updateProduct(index, 'price', parseFloat(e.target.value) || 0)}
                                placeholder="Enter price in Naira"
                                aria-invalid={!!errors[`products.${index}.price`]}
                            />
                            <InputError message={errors[`products.${index}.price`]} />
                        </div>
                    </div>



                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor={`product_description_${index}`}>Description</Label>
                            <Textarea
                                id={`product_description_${index}`}
                                value={product.description || ''}
                                onChange={(e) => updateProduct(index, 'description', e.target.value)}
                                placeholder="Product description"
                                rows={3}
                            />
                            <InputError message={errors[`products.${index}.description`]} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor={`product_image_${index}`}>Image</Label>
                            <div className="space-y-3">
                                {product.image_url && (
                                    <div className="flex justify-center">
                                        <img
                                            src={product.image_url}
                                            alt={product.name}
                                            className="h-24 w-24 object-cover rounded-lg border border-border shadow-sm"
                                        />
                                    </div>
                                )}
                                <div className="space-y-2">
                                    <div className="flex items-center justify-center w-full">
                                        <label
                                            htmlFor={`product_image_${index}`}
                                            className="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-border rounded-lg cursor-pointer bg-muted/30 hover:bg-muted/50 hover:border-primary/50 transition-all duration-200 group"
                                        >
                                            <div className="flex flex-col items-center justify-center pt-5 pb-6">
                                                <Upload className="w-8 h-8 mb-2 text-muted-foreground group-hover:text-primary transition-colors" />
                                                <p className="mb-2 text-sm text-muted-foreground group-hover:text-foreground transition-colors">
                                                    <span className="font-semibold">Click to upload</span> or drag and drop
                                                </p>
                                                <p className="text-xs text-muted-foreground">JPEG, PNG, or WebP (MAX. 2MB)</p>
                                            </div>
                                            <Input
                                                id={`product_image_${index}`}
                                                type="file"
                                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                                className="hidden"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0];
                                                    if (file) {
                                                        handleImageUpload(index, file);
                                                    }
                                                }}
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <InputError message={errors[`products.${index}.image`]} />
                        </div>
                    </div>
                </div>
            ))}

            <Button
                type="button"
                variant="outline"
                onClick={addProduct}
                className="w-full"
            >
                <Plus className="h-4 w-4 mr-2" />
                Add Product
            </Button>

            <div className="bg-muted/50 border border-muted p-4 rounded-lg">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <span className="font-medium">Total Project Amount</span>
                        <p className="text-sm text-muted-foreground">Sum of all product prices</p>
                    </div>
                    <span className="text-2xl font-bold text-primary">{formatCurrency(totalAmount)}</span>
                </div>
            </div>
        </div>
    );
}
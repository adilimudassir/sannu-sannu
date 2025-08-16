import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import ImageUpload from '@/components/ui/image-upload';
import InputError from '@/components/input-error';
import { formatCurrency } from '@/lib/formatters';
import { Plus, X } from 'lucide-react';
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

    const handleImageUpload = (index: number, file: File | null) => {
        if (file) {
            updateProduct(index, 'image', file);
            
            // Create preview URL
            const reader = new FileReader();
            reader.onload = (e) => {
                updateProduct(index, 'image_url', e.target?.result as string);
            };
            reader.readAsDataURL(file);
        } else {
            updateProduct(index, 'image', null);
            updateProduct(index, 'image_url', null);
        }
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



                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                        <div className="space-y-2">
                            <Label htmlFor={`product_description_${index}`}>Description</Label>
                            <Textarea
                                id={`product_description_${index}`}
                                value={product.description || ''}
                                onChange={(e) => updateProduct(index, 'description', e.target.value)}
                                placeholder="Product description"
                                rows={5}
                                className="resize-none"
                            />
                            <InputError message={errors[`products.${index}.description`]} />
                        </div>

                        <ImageUpload
                            id={`product_image_${index}`}
                            label="Product Image"
                            value={product.image || product.image_url}
                            onChange={(file) => handleImageUpload(index, file)}
                            maxSize={5}
                            error={errors[`products.${index}.image`]}
                            previewClassName="flex justify-center"
                        />
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